<?php
/*
Plugin Name: FacetWP - Beaver Builder
Description: FacetWP and Beaver Builder Integration
Version: 1.4.1
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-beaver-builder
*/

defined( 'ABSPATH' ) or exit;

// setup constants.
define( 'FWPBB_PATH', plugin_dir_path( __FILE__ ) );
define( 'FWPBB_URL', plugin_dir_url( __FILE__ ) );
define( 'FWPBB_VER', '1.4.1' );


class FacetWP_BB_Integration {

    private $grids;
    private static $instance;
    public $use_next_query;


    function __construct() {
        add_action( 'init', [ $this, 'register_modules' ], 30 );
        add_action( 'wp_footer', [ $this, 'enqueue_scripts' ] );

        add_filter( 'facetwp_load_assets', [ $this, 'load_assets' ] );
        add_filter( 'facetwp_is_main_query', [ $this, 'is_main_query' ], 10, 2 );

        add_filter( 'fl_builder_register_settings_form', [ $this, 'add_facetwp_toggle' ], 10, 2 );
        add_filter( 'fl_builder_module_custom_class', [ $this, 'add_template_class' ], 10, 2 );
        add_filter( 'fl_builder_render_settings_field', [ $this, 'modify_fields' ], 10, 3 );
        add_action( 'fl_builder_before_render_module', [ $this, 'store_module_settings' ] );
        add_action( 'fl_builder_loop_before_query', [ $this, 'check_query' ] );
        add_filter( 'fl_builder_loop_query_args', [ $this, 'handle_custom_query' ], 11 );

        // WooCommerce [products] cache buster
        add_filter( 'shortcode_atts_products', [ $this, 'bust_shortcode_cache' ] );
    }


    public static function init() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Register FacetWP helper modules
     */
    function register_modules() {
        $this->grids = [];

        if ( class_exists( 'FLBuilderModule' ) && function_exists( 'FWP' ) ) {
            include_once FWPBB_PATH . 'modules/template/class-template.php';
            include_once FWPBB_PATH . 'modules/facet/class-facet.php';
            include_once FWPBB_PATH . 'modules/pager/class-pager.php';
            include_once FWPBB_PATH . 'modules/counts/class-counts.php';
            include_once FWPBB_PATH . 'modules/per-page/class-per-page.php';
            include_once FWPBB_PATH . 'modules/selections/class-selections.php';
            include_once FWPBB_PATH . 'modules/sort/class-sort.php';
        }
    }


    /**
     * Load assets
     */
    function enqueue_scripts() {
        if ( ! empty( $this->grids ) ) {
            wp_enqueue_script( 'facetwp-bb', FWPBB_URL . 'js/front.js', [ 'jquery' ], FWPBB_VER );
            wp_localize_script( 'facetwp-bb', 'FWPBB', [
                'post_id' => get_queried_object_id(),
                'modules' => $this->grids,
            ] );
        }
    }


    /**
     * Load assets for BB builder preview
     */
    function load_assets( $bool ) {
        if ( class_exists( 'FLBuilderModel' ) ) {
            return FLBuilderModel::is_builder_active() ? true : $bool;
        }
        return $bool;
    }


    /**
     * Detect modules with "facetwp" => "enabled" and also support
     * archive pages using Beaver Themer archive layouts
     */
    function is_main_query( $is_main_query, $query ) {

        if ( isset( $this->use_next_query ) ) {
            $is_main_query = $this->use_next_query;
            $this->use_next_query = null;
        }

        if ( 'fl-builder-template' == $query->get( 'post_type' ) ) {
            $is_main_query = false;
        }

        return $is_main_query;
    }


    /**
     * Prevent the WC [products] shortcode from getting cached
     * Otherwise we can't access the query in order to apply filtering
     *
     * @package \WC_Shortcode_Products->parse_attributes()
     */
    function bust_shortcode_cache( $atts ) {
        $atts['cache'] = false;
        return $atts;
    }


    /**
     * Add a FacetWP toggle for post grid modules
     */
    function add_facetwp_toggle( $form, $id ) {
        $supported = [
            'post-grid',                // BB
            'woocommerce',              // BB
            'product-grid',             // WooPack
            'pp-content-grid',          // PowerPack
            'blog-posts',               // UABB
            'uabb-woo-products',        // UABB
        ];

        if ( in_array( $id, $supported ) ) {
            $setting = [
                'type'    => 'select',
                'label'   => __( 'FacetWP', 'fl-builder' ),
                'default' => 'disable',
                'options' => [
                    'disable' => __( 'Disabled', 'fl-builder' ),
                    'enable'  => __( 'Enabled', 'fl-builder' ),
                ],
            ];

            if ( in_array( $id, [ 'woocommerce', 'blog-posts', 'uabb-woo-products' ] ) ) {
                $form['general']['sections']['general']['fields']['facetwp'] = $setting;
            }
            elseif ( in_array( $id, [ 'post-grid', 'pp-content-grid' ] ) ) {
                $form['layout']['sections']['general']['fields']['facetwp'] = $setting;
            }
            elseif ( in_array( $id, [ 'product-grid' ] ) ) {
                $form['general']['sections']['layout']['fields']['facetwp'] = $setting;
            }
        }

        return $form;
    }


    /**
     * Add the FacetWP template CSS class if needed
     */
    function add_template_class( $class, $module ) {
        if ( isset( $module->settings->facetwp ) && 'enable' === $module->settings->facetwp ) {
            if ( false === strpos( $class, 'facetwp-template' ) ) {
                $class .= ' facetwp-template';
            }
            $class .= ' facetwp-bb-module';
        }

        return $class;
    }


    /**
     * Customizations to BB's admin field UI
     */
    function modify_fields( $field, $name, $settings ) {

        // Add FacetWP templates to the "data source" dropdown
        if ( 'data_source' === $name ) {
            $templates = FWP()->helper->get_templates();

            foreach ( $templates as $template ) {
                $field['options'][ 'fwp/' . $template['name'] ] = 'FacetWP: ' . $template['label'];
            }
        }
        // Remove BB's "offset" setting
        elseif ( 'offset' === $name ) {
            if ( isset( $settings->facetwp ) && 'enable' == $settings->facetwp ) {

                // see classes/class-fl-builder-ui-settings-form.php:900
                unset( $field['type'] );
            }
        }

        return $field;
    }


    /**
     * If this is a FacetWP-enabled grid module, store some info
     */
    function store_module_settings( $module ) {
        $global_settings = FLBuilderModel::get_global_settings();
        $settings = $module->settings;
        $id = $module->node;

        if ( isset( $settings->facetwp ) && 'enable' == $settings->facetwp ) {
            if ( 'post-grid' == $module->slug ) {
                $options = [
                    'id'            => $id,
                    'layout'        => $settings->layout,
                    'pagination'    => $settings->pagination,
                    'postSpacing'   => $settings->post_spacing,
                    'postWidth'     => $settings->post_width,
                    'matchHeight'   => (int) $settings->match_height,
                ];
                if ( $settings->match_height ) {
                    $options['matchHeight'] = [
                        'default'       => $settings->match_height,
                        'medium'        => $settings->match_height_medium,
                        'responsive'    => $settings->match_height_responsive
                    ];
                }
            }
            elseif ( 'woocommerce' == $module->slug) {
                $options = [
                    'id' => $id,
                    'layout' => $settings->layout,
                ];
            }
            elseif ( 'pp-content-grid' == $module->slug ) {
                $options = [
                    'id'            => $id,
                    'layout'        => $settings->layout,
                    'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                    'perPage'       => $settings->posts_per_page,
                    'fields'        => json_encode( $settings ),
                    'pagination'    => $settings->pagination,
                    'postSpacing'   => $settings->post_spacing,
                    'postColumns'   => $settings->post_grid_count,
                    'matchHeight'   => $settings->match_height,
                    'filters'       => false,
                ];

                if ( 'grid' == $settings->layout && 'no' == $settings->match_height ) {
                    $options['masonry'] = 'yes';
                }
            }
            elseif ( 'blog-posts' == $module->slug ) {
                // modules/blog-posts/includes/frontend.js.php
                $options = [
                    'id'                        => esc_attr( $id ),
                    'pagination'                => isset( $settings->pagination ) ? esc_attr( $settings->pagination ) : 'numbers',
                    'is_carousel'               => isset( $settings->is_carousel ) ? esc_attr( $settings->is_carousel ) : 'grid',
                    'infinite'                  => ( 'yes' === $settings->infinite_loop ) ? 'true' : 'false',
                    'arrows'                    => ( 'yes' === $settings->enable_arrow ) ? 'true' : 'false',
                    'desktop'                   => ( '' !== $settings->post_per_grid_desktop ) ? esc_attr( $settings->post_per_grid_desktop ) : 1,
                    'moduleUrl'                 => esc_url( BB_ULTIMATE_ADDON_URL ) . 'modules/' . esc_attr( $settings->type ),
                    'medium'                    => ( '' !== $settings->post_per_grid_medium ) ? esc_attr( $settings->post_per_grid_medium ) : 1,
                    'small'                     => ( '' !== $settings->post_per_grid_small ) ? esc_attr( $settings->post_per_grid_small ) : 1,
                    'slidesToScroll'            => ( '' !== $settings->slides_to_scroll ) ? esc_attr( $settings->slides_to_scroll ) : 1,
                    'prevArrow'                 => ( isset( $settings->icon_left ) && '' !== $settings->icon_left ) ? esc_attr( $settings->icon_left ) : 'fas fa-angle-left',
                    'nextArrow'                 => ( isset( $settings->icon_right ) && '' !== $settings->icon_right ) ? esc_attr( $settings->icon_right ) : 'fas fa-angle-right',
                    'autoplay'                  => ( 'yes' === $settings->autoplay ) ? 'true' : 'false',
                    'autoplaySpeed'             => ( '' !== $settings->animation_speed ) ? esc_attr( $settings->animation_speed ) : '1000',
                    'dots'                      => ( 'yes' === $settings->enable_dots ) ? 'true' : 'false',
                    'small_breakpoint'          => esc_attr( $global_settings->responsive_breakpoint ),
                    'medium_breakpoint'         => esc_attr( $global_settings->medium_breakpoint ),
                    'equal_height_box'          => esc_attr( $settings->equal_height_box ),
                    'mesonry_equal_height'      => isset( $settings->mesonry_equal_height ) ? esc_attr( $settings->mesonry_equal_height ) : 'no',
                    'blog_image_position'       => esc_attr( $settings->blog_image_position ),
                    'element_space'             => esc_attr( $settings->element_space ),
                ];
            }
            elseif ( 'uabb-woo-products' == $module->slug ) {
                // modules/uabb-woo-products/includes/frontend.js.php
                $options = [
                    'id'                        => esc_attr( $id ),
                    'ajaxurl'                   => wp_kses_post( admin_url( 'admin-ajax.php' ) ),
                    'is_cart'                   => is_cart() ? 'true' : 'false',
                    'view_cart'                 => esc_attr__( 'View cart', 'uabb' ),
                    'cart_url'                  => esc_url( apply_filters( 'uabb_woocommerce_add_to_cart_redirect', wc_get_cart_url() ) ),
                    'layout'                    => esc_attr( $settings->layout ),
                    'skin'                      => esc_attr( $settings->skin ),
                    'next_arrow'                => apply_filters( 'uabb_woo_products_carousel_next_arrow_icon', 'fas fa-angle-right' ),
                    'prev_arrow'                => apply_filters( 'uabb_woo_products_carousel_previous_arrow_icon', 'fas fa-angle-left' ),
                    'infinite'                  => ( 'yes' === $settings->infinite_loop ) ? 'true' : 'false',
                    'dots'                      => ( 'yes' === $settings->enable_dots ) ? 'true' : 'false',
                    'arrows'                    => ( 'yes' === $settings->enable_arrow ) ? 'true' : 'false',
                    'desktop'                   => esc_attr( $settings->slider_columns_new ),
                    'medium'                    => esc_attr( $settings->slider_columns_new_medium ),
                    'small'                     => esc_attr( $settings->slider_columns_new_responsive ),
                    'slidesToScroll'            => ( '' !== $settings->slides_to_scroll ) ? esc_attr( $settings->slides_to_scroll ) : 1,
                    'autoplay'                  => ( 'yes' === $settings->autoplay ) ? 'true' : 'false',
                    'autoplaySpeed'             => ( '' !== $settings->animation_speed ) ? esc_attr( $settings->animation_speed ) : '1000',
                    'small_breakpoint'          => esc_attr( $global_settings->responsive_breakpoint ),
                    'medium_breakpoint'         => esc_attr( $global_settings->medium_breakpoint ),
                    'module_settings'           => wp_json_encode( $settings ),
                ];
            }

            $options['type'] = $settings->type;
            $this->grids[ $id ] = $options;
        }
    }


    /**
     * Check whether the upcoming query is the correct one
     */
    function check_query( $settings ) {
        $this->use_next_query = isset( $settings->facetwp ) && 'enable' == $settings->facetwp;
    }


    /**
     * Override query arguments
     * Source: "custom_query" or "fwp/<template_name>"
     */
    function handle_custom_query( $args ) {

        // Exit if not the builder
        if ( empty( $args['fl_builder_loop' ] ) ) {
            return $args;
        }

        $settings = $args['settings'];

        $is_enabled = isset( $settings->facetwp ) && 'enable' === $settings->facetwp;
        $source = isset( $settings->data_source ) ? $settings->data_source : '';
        $is_fwp_query = ( 0 === strpos( $source, 'fwp/' ) );

        if ( $is_enabled || $is_fwp_query ) {
            if ( $is_fwp_query ) {

                // Grab the template by name
                $template = FWP()->helper->get_template_by_name( substr( $source, 4 ) );

                if ( false !== $template ) {

                    // Use the query builder
                    if ( isset( $template['modes'] ) && 'visual' == $template['modes']['query'] ) {
                        $query_args = FWP()->builder->parse_query_obj( $template['query_obj'] );
                    }
                    else {

                        // remove UTF-8 non-breaking spaces
                        $query_args = preg_replace( "/\xC2\xA0/", ' ', $template['query'] );
                        $query_args = (array) eval( '?>' . $query_args );
                    }

                    // Merge the two arrays
                    $args = array_merge( $args, $query_args );
                }
            }

            // Set paged and offset
            $prefix = FWP()->helper->get_setting( 'prefix', 'fwp_' );
            $paged_var = isset( $_GET[ $prefix . 'paged' ] ) ? (int) $_GET[ $prefix . 'paged' ] : 1;
            $load_more_var = isset( $_GET[ $prefix . 'load_more' ] ) ? (int) $_GET[ $prefix . 'load_more' ] : false;

            $paged = $load_more_var ? $load_more_var : $paged_var;

            // For AJAX refreshes, grab the page number from the response
            if ( ! FWP()->request->is_preload ) {
                $post_data = FWP()->request->process_post_data();
                $paged = (int) $post_data['paged'];
            }

            $per_page = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : 10;
            $offset = ( 1 < $paged ) ? ( ( $paged - 1 ) * $per_page ) : 0;

            $GLOBALS['wp_the_query']->set( 'page', $paged );
            $GLOBALS['wp_the_query']->set( 'paged', $paged );
            $args['paged'] = $paged;
            $args['offset'] = $offset;

            // Support old "Load more"
            if ( FWP()->request->is_preload && $load_more_var ) {
                $args['posts_per_page'] = $paged * $per_page;
                $args['offset'] = 0;
            }

            $args['facetwp'] = $is_enabled;
        }

        return $args;
    }
}


FacetWP_BB_Integration::init();
