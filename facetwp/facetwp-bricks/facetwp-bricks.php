<?php
/*
Plugin Name: FacetWP - Bricks Builder integration
Description: Integrates FacetWP with Bricks
Version: 0.7
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-bricks
*/

defined( 'ABSPATH' ) or exit;

class FacetWP_Bricks_Addon
{

    public $found_element = '';
    public $class_added = false;
    public $content_id;


    function __construct() {
        define( 'FACETWP_BRICKS_VERSION', '0.7' );
        define( 'FACETWP_BRICKS_URL', plugins_url( '', __FILE__ ) );

        add_filter( 'after_setup_theme', [ $this, 'init' ] );

    }

    function init() {

        if ( ! defined( 'BRICKS_VERSION' ) ) {
            return;
        }

        add_filter( 'facetwp_assets', [ $this, 'assets' ] );
        add_filter( 'wp_footer', [ $this, 'add_content_id' ], 100 );
        add_filter( 'facetwp_is_main_query', [ $this, 'skip_archive' ], 10, 2 );
        add_filter( 'bricks/posts/query_vars', [ $this, 'maybe_detect_query' ], 10, 3 );
        add_filter( 'bricks/posts/merge_query', [ $this, 'maybe_skip_merge' ], 10, 2 );
        add_filter( 'bricks/element/render_attributes', [ $this, 'maybe_append_css_class' ], 10, 3 );
        add_filter( 'bricks/elements/posts/controls', [ $this, 'add_controls' ] );
        add_filter( 'bricks/elements/woocommerce-products/controls', [ $this, 'add_product_controls' ] );
        add_filter( 'bricks/posts/query_vars', [ $this, 'process_noresults_products' ], 10, 3 );
        add_filter( 'bricks/query/no_results_content', [ $this, 'process_noresults_posts' ], 10, 3 );

        foreach ( [ 'container', 'block', 'div' ] as $name ) {
            add_filter( "bricks/elements/$name/controls", [ $this, 'add_controls_extended' ] );
        }
    }


    /**
     * Add "Use FacetWP" setting above the "Query" setting
     */
    function add_controls( $controls ) {
        $output = [];

        foreach ( $controls as $key => $data ) {
            if ( 'query' == $key ) {
                $output['usingFacetWP'] = [
                    'tab'   => 'content',
                    'label' => esc_html__( 'Use FacetWP', 'fwp' ),
                    'type'  => 'checkbox'
                ];
            }

            $output[ $key ] = $data;
        }

        return $output;
    }

    /**
     * Extend add_controls() to only show the "Use FacetWP"
     * setting when "Use query loop" is enabled
     */
    function add_controls_extended( $controls ) {
        $controls = $this->add_controls( $controls );
        $controls['usingFacetWP']['required'] = [ 'hasLoop', '=', true ];

        return $controls;
    }

    /**
     * Add "Use FacetWP" at top of content tab
     */
    function add_product_controls( $controls ) {
        $output['usingFacetWP'] = [
            'tab'   => 'content',
            'label' => esc_html__( 'Use FacetWP', 'fwp' ),
            'type'  => 'checkbox'
        ];

        $output = array_merge( $output, $controls );

        return $output;
    }


    /**
     * Ignore the WP "Blog Home" query
     */
    function skip_archive( $is_main_query, $query ) {
        if ( 'bricks_template' == $query->get( 'post_type' ) ) {
            return false;
        }

        if ( $query->is_main_query() && ( $query->is_archive() || $query->is_home() || $query->is_search() ) ) {

            $content_template_id = $this->get_content_id( $query );
            $elements = (array) get_post_meta( $content_template_id, BRICKS_DB_PAGE_CONTENT, true );

            $is_archive_main_query = false;
            $is_template_query = false;

            foreach ( $elements as $row ) {
                // fixes $row['settings']['usingFacetWP'] may be set on more than one element but ['settings']['query']['is_archive_main_query'] may not be set on first one
                if ( isset( $row['settings']['usingFacetWP'] ) && isset( $row['settings']['query']['is_archive_main_query'] ) ) {
                    $is_archive_main_query = true;
                } else if ( ! $is_archive_main_query && isset( $row['settings']['usingFacetWP'] ) ) {
                    $is_template_query = true;
                }
            }

            if ( !$is_archive_main_query && $is_template_query ) {
                return false;
            }
        }

        return $is_main_query;
    }

    /**
     * get content id
     */
    /**
     * get content id
     */
    function get_content_id( $query = null ) {
        $http = FWP()->facet->http_params;

        // Fixes term archives with custom Bricks 404 template
        if ( $query->is_main_query() && $query->is_archive() && false == get_queried_object_id() && !( get_queried_object() instanceof WP_Post_Type )  ) {
            return false;
        }

        if ( isset( $http['content_id'] ) && ! empty( $http['content_id'] ) ) {
            $this->content_id = $http['content_id'];
        }
        else {
            \Bricks\Database::set_active_templates();
            $content_template_id = \Bricks\Database::$active_templates['content'];
            $this->content_id = $content_template_id;
        }

        return $this->content_id;
    }


    /**
     * Prevent Bricks from merging with the WP main query
     *
     * @link https://academy.bricksbuilder.io/article/filter-bricks-posts-merge_query/
     */
    function maybe_skip_merge( $merge, $element_id ) {
        $element = $this->get_element( get_the_ID(), $element_id );

        if ( isset( $element['settings']['usingFacetWP'] ) ) {
            $merge = false;
        }

        return $merge;
    }


    /**
     * Detect the query via the "facetwp" query var
     */
    function maybe_detect_query( $query_vars, $settings, $element_id ) {
        if ( isset( $settings['usingFacetWP'] ) ) {

            $element = $this->get_element( get_the_ID(), $element_id );
            $is_empty = empty( $this->found_element );
            $main_query = $GLOBALS['wp_the_query'];

            $this->found_element = $element_id;

            $query_vars['facetwp'] = true;

            if ( ! isset( $settings['query']['disable_query_merge'] ) ) {
                if ( $main_query->is_archive || $main_query->is_search ) {
                    if ( $main_query->is_category ) {
                        $query_vars['cat'] = $main_query->get( 'cat' );
                    }
                    elseif ( $main_query->is_tag ) {
                        $query_vars['tag_id'] = $main_query->get( 'tag_id' );
                    }
                    elseif ( $main_query->is_tax ) {
                        $query_vars['taxonomy'] = $main_query->get( 'taxonomy' );
                        $query_vars['term'] = $main_query->get( 'term' );
                    }
                    elseif ( $main_query->is_search ) {
                        $query_vars['s'] = $main_query->get( 's' );
                    }
                }
            }
        }

        return $query_vars;
    }


    /**
     * Add the "facetwp-template" CSS class
     */
    function maybe_append_css_class( $attributes, $key, $element ) {
        if ( ! $this->class_added && ! empty( $this->found_element ) && '_root' == $key ) {
            $children = $element->element['children'] ?? [];
            $children = array_flip( $children );

            // https://academy.bricksbuilder.io/article/query-loop/
            $is_posts_element = ( $this->found_element == $element->id );
            $has_nested_query_loop = isset( $children[ $this->found_element ] );

            if ( $is_posts_element || $has_nested_query_loop ) {
                $attributes[ $key ]['class'][] = 'facetwp-template';
                $this->class_added = true;

                // add arguments to adjust pager
                add_filter( 'bricks/paginate_links_args', function( $args ) {
                    if ( isset( FWP()->request->output['settings']['pager'] ) && ! empty( FWP()->request->output['settings']['pager'] ) ) {
                        $args['current'] = FWP()->request->output['settings']['pager']['page'];
                        $args['total'] = FWP()->request->output['settings']['pager']['total_pages'];
                    }
                    return $args;
                });
            }
        }

        return $attributes;
    }


    /**
     * Get a specific Bricks element
     */
    function get_element( $post_id, $element_id ) {
        $element = \Bricks\Helpers::get_element_data( $post_id, $element_id );
        return $element['element'] ?? [];
    }


    /**
     * Pager support
     */
    function assets( $assets ) {
        $assets['facetwp-bricks.js'] = [ FACETWP_BRICKS_URL . '/assets/js/front.js', FACETWP_BRICKS_VERSION ];
        return $assets;
    }

    /**
     * Put the content_id into FWP_HTTP
     */
    function add_content_id() {
        $content_template_id = $this->content_id ?? get_queried_object_id();

        if ( 0 < $content_template_id ) {
            echo "<script>var FWP_HTTP = FWP_HTTP || {}; FWP_HTTP.content_id = '$content_template_id';</script>";
        }
    }

    /**
     * Process no results template for Bricks Posts elements
     */
    function process_noresults_posts( $content, $settings, $element_id ) {

        if ( true == ( $settings['usingFacetWP'] ?? false ) ) {

            $element = $this->get_element( get_the_ID(), $element_id );

            // Only run if element is a Posts element
            if ( isset( $element['name'] ) && 'posts' === $element['name'] ) {

                $id = isset( $settings['_cssId'] ) ? $settings['_cssId'] : 'brxe-' . $element_id;
                $classes = isset( $settings['_cssClasses'] ) ? ' ' . $settings['_cssClasses'] : '';

                $content = '<div id="' . $id . '" class="brxe-posts facetwp-template' . $classes . '" data-script-id="' . $element_id . '">' . $content . '</div>';

            }
        }

        return $content;
    }

    /**
     * Process no results template for Bricks Products elements
     */
    function process_noresults_products( $query_vars, $settings, $element_id ) {

        if ( true == ( $settings['usingFacetWP'] ?? false ) && apply_filters( 'facetwp_bricks_noresults_products', true ) ) {

            $element = $this->get_element( get_the_ID(), $element_id );

            // Only run if element is a Products element
            if ( isset( $element['name'] ) && 'woocommerce-products' === $element['name'] ) {

                $id = isset( $settings['_cssId'] ) ? $settings['_cssId'] : 'brxe-' . $element_id;
                $classes = isset( $settings['_cssClasses'] ) ? ' ' . $settings['_cssClasses'] : '';

                add_action( 'woocommerce_no_products_found', function() use ( $id, $classes ) {

                    remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );
                    $noresults_content = '<p class="no-results">' . __( 'No products were found matching your selection.', 'fwp-front' ) . '</p>';
                    echo '<div id="' . $id . '" class="brxe-woocommerce-products facetwp-template' . $classes . '">' . $noresults_content . '</div>';

                }, 9 );
            }

        }

        return $query_vars;
    }
}


new FacetWP_Bricks_Addon();
