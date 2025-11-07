<?php
/*
Plugin Name: FacetWP - Elementor
Description: FacetWP and Elementor Integration
Version: 1.9.3
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-elementor
*/

defined( 'ABSPATH' ) or exit;

define( 'FACETWP_ELEMENTOR_VERSION', '1.9.3' );


class FacetWP_Elementor_Addon {

    private static $instance;
    private $supported_widgets = [];
    private $found_widget;
    private $products_query_counter = 0;
    private $products_query_offset = 1;


    function __construct() {
        add_action( 'elementor/init', [ $this, 'setup_elementor' ] );
    }


    public static function init() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Setup hooks and properties for Elementor Pro
     */
    function setup_elementor() {

        if ( ! function_exists( 'FWP' ) ) {
            return;
        }

        add_action( 'elementor/widget/before_render_content', [ $this, 'add_template_class' ] );
        add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
        add_action( 'pre_get_posts', [ $this, 'set_pager_var' ], 1000 );
        add_filter( 'facetwp_assets', [ $this, 'assets' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'register_editor_styles' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_categories' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_new_widgets' ] );
        add_action( 'shutdown', [ $this, 'flush_buffers' ], -1 );

        // Hook only into desired element types (performance boost)
        $sections = [
            'section_layout' => [
                'posts',
                'archive-posts',
                'loop-grid',
                'loop-carousel'
            ],
            'section_content' => [
                'woocommerce-products',
                'woocommerce-archive-products', // deprecated
                'wc-archive-products'
            ],
            'section_filter_field' => [
                'uael-woo-products',
                'uael-posts'
            ],
            'section_general' => [
                'jet-listing-grid'
            ],
            'section_query' => [
                'pp-posts',
                'pp-woo-products'
            ],
            'general_settings_section'  => [
                'premium-addon-blog'
            ],
            'general_section'  => [
                'premium-woo-products'
            ],
        ];

        $sections = apply_filters( 'facetwp_elementor_sections', $sections );

        foreach ( $sections as $section_id => $widgets ) {
            foreach ( $widgets as $widget ) {
                add_action( "elementor/element/{$widget}/{$section_id}/after_section_end", [ $this, 'register_controls' ], 10, 2 );

                $this->supported_widgets[ $widget ] = true;
            }
        }
    }


    /**
     * Add an "Enable FacetWP" toggle for supported widget types
     */
    function register_controls( $element, $args ) {
        $element->start_controls_section(
            'facetwp_section', [
                'label' => __( 'FacetWP', 'facetwp-elementor' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT
            ]
        );

        $element->add_control(
            'enable_facetwp', [
                'label' => __( 'Enable FacetWP', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'facetwp-elementor' ),
                'label_off' => __( 'No', 'facetwp-elementor' ),
                'return_value' => 'yes',
                'default' => 'no'
            ]
        );

        $element->end_controls_section();
    }


    /**
     * Check whether the current page has a valid Elementor location template
     * @param string $location The location name (usually 'archive' or 'single')
     */
    function has_location( $location = 'archive' ) {
        if ( !class_exists( '\ElementorPro\Plugin') ) {
            return false;
        }

        if ( 'single' == $location && 0 == get_queried_object_id() ) {
            return false;
        }

        $manager = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' )->get_conditions_manager();
        $documents = $manager->get_documents_for_location( $location );
        return ! empty( $documents );
    }


    /**
     * Add the "facetwp-template" CSS class
     */
    function add_template_class( $widget ) {
        $name = $widget->get_name();

        if ( isset( $this->supported_widgets[ $name ] ) && 'yes' === $widget->get_settings( 'enable_facetwp' ) ) {
            $widget->add_render_attribute( '_wrapper', 'class', [ 'facetwp-template', 'facetwp-elementor-widget' ] );
            $this->found_widget = $name;

        }
        elseif ( 'facetwp-button' == $name ) {
            if ( 'submit' == $widget->get_settings( 'facetwp_button_type' ) ) {
                $link = $widget->get_settings( 'facetwp_link' );
                $widget->add_render_attribute( '_wrapper', 'class', [ 'fwp-submit' ] );
                $widget->add_render_attribute( '_wrapper', 'href', [ 'javascript:;' ] );
                $widget->add_render_attribute( '_wrapper', 'data-href', $link );
            } elseif ( 'flyout' == $widget->get_settings( 'facetwp_button_type' ) ) {
                $widget->add_render_attribute( '_wrapper', 'class', [ 'facetwp-flyout-open' ] );
            } elseif ( 'apply' == $widget->get_settings( 'facetwp_button_type' ) ) {
                $widget->add_render_attribute( '_wrapper', 'href', [ 'javascript:;' ] );
                $widget->add_render_attribute( '_wrapper', 'onclick', [ 'FWP.refresh();' ] );
                add_action( 'facetwp_scripts', function() { ?>
<script> (function($) { $(function() { FWP.auto_refresh = false; }); })(fUtil);</script>
                <?php }, 100 );

            }
        }

    }


    /**
     * Force FacetWP to use the query if the widget matches
     */
    function pre_get_posts( $query ) {

        // Ignore `Ele Custom Skin` single queries
        if ( $query->is_singular || true === $query->get( 'suppress_filters', false ) ) {
            $query->set( 'facetwp', false );
        }

        // A supported widget is in use
        elseif ( ! empty( $this->found_widget ) && 'done' != $this->found_widget ) {

            // Elementor runs the product query twice, so intercept only the 2nd query
            if (  in_array( $this->found_widget, [ 'woocommerce-products', 'premium-addon-blog' ] ) ) {
                $is_correct_query = ( $this->products_query_offset === $this->products_query_counter );
                $query->set( 'facetwp', $is_correct_query );
                $this->products_query_counter++;
            }
            elseif ( empty( FWP()->facet->query ) ) {
                $query->set( 'facetwp', true );
            }
        }

        // Ignore this single page if the Elementor widget is disabled
        elseif ( $this->has_location( 'single' ) && empty( $this->found_widget ) ) {
            $query->set( 'facetwp', false );
        }
    }


    /**
     * Set the page number, needed by the Elementor posts widget's pagination
     * This hook uses priority = 1000 so that FWP()->facet->render() fires first
     * or FWP()->facet->pager_args won't be available
     */
    function set_pager_var( $query ) {
        $is_main_query = ( true === $query->get( 'facetwp', false ) );
        $has_query_run = ( ! empty( FWP()->facet->query ) );

        if ( $is_main_query && $has_query_run && ! empty( $this->found_widget ) && 'done' != $this->found_widget ) {
            if ( isset( FWP()->facet->pager_args ) ) {
                set_query_var( 'paged', (int) FWP()->facet->pager_args['page'] );
            }

            // Bail after intercepting one valid query
            $this->found_widget = 'done';
        }
    }


    /**
     * Load the JS assets
     */
    function assets( $assets ) {
        $assets['facetwp-elementor-front.js'] = [ plugins_url( '', __FILE__ ) . '/assets/js/front.js', FACETWP_ELEMENTOR_VERSION ];
        return $assets;
    }

    /**
     * Adds styles for editor
     */
    function register_editor_styles() {

        wp_register_style( 'facetwp-editor', plugins_url( '', __FILE__ ) . '/assets/css/editor.css' );
        wp_enqueue_style( 'facetwp-editor' );

    }


    /**
     * Register widget categories
     */
    function register_widget_categories( $elements_manager ) {

        $elements_manager->add_category(
            'facetwp',
            [
                'title' => esc_html__( 'FacetWP', 'facetwp-elementor' ),
                'icon' => 'eicon-plus-circle-o'
            ]
        );

    }


    /**
     * Register new Elementor widgets.
     */
    function register_new_widgets( $widgets_manager ) {

        wp_register_style( 'facetwp-admin', plugins_url( '', __FILE__ ) . '/assets/css/admin.css' );

        // facet
        require_once( __DIR__ . '/widgets/facet.php' );
        $widgets_manager->register( new \FacetWP_Facet_Widget() );
        // listing
        require_once( __DIR__ . '/widgets/listing.php' );
        $widgets_manager->register( new \FacetWP_Listing_Widget() );
        // button
        require_once( __DIR__ . '/widgets/button.php' );
        $widgets_manager->register( new \FacetWP_Button_Widget() );

    }

    /**
     * flush elementor buffers for image optimization feature
     **/
    function flush_buffers() {
        if ( isset( FWP()->request ) && FWP()->request->is_refresh ) {
            $buffer = ob_get_status();
            if ( 'Elementor\Modules\ImageLoadingOptimization\Module::handle_buffer_content' ==  $buffer['name'] ) {
                ob_end_flush();
            }
        }
    }

}


FacetWP_Elementor_Addon::init();
