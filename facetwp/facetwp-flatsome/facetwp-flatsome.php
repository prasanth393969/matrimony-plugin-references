<?php
/*
Plugin Name: FacetWP - Flatsome integration
Description: Flatsome theme support for FacetWP
Version: 0.4.5
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-flatsome
*/

defined( 'ABSPATH' ) or exit;

class FWP_Flatsome
{

    function __construct() {
        add_action( 'init' , [ $this, 'init' ] );
        add_action( 'wp_head', [ $this, 'wp_head' ], 100 );
    }


    function init() {

        // Inject the "facetwp-template" container
        add_action( 'woocommerce_before_main_content', function() {
            if ( ! is_singular() ) {
                echo '<div class="facetwp-template">';
            }
        });

        add_action( 'woocommerce_after_main_content', function() {
            if ( ! is_singular() ) {
                echo '</div>';
            }
        });

        // Override the result count text
        add_filter( 'facetwp_result_count', function( $output, $params ) {
            $first = $params['lower'];
            $last = $params['upper'];
            $total = $params['total'];

            ob_start();

            if ( $total <= $last ) {
                printf( _n( 'Showing the single result', 'Showing all %d results', $total, 'facetwp-flatsome' ), $total );
            } else {
                printf( _nx( 'Showing the single result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, '%1$d = first, %2$d = last, %3$d = total', 'facetwp-flatsome' ), $first, $last, $total );
            }

            return ob_get_clean();
        }, 10, 2 );

        // Override loop/result-count.php
        add_filter( 'woocommerce_locate_template', [ $this, 'locate_template' ], 10, 3 );
    }


    /**
     * Support lazy load and quick view
     */
    function wp_head() {
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var $ = window['jQuery'];

    $(document).on('facetwp-loaded', function() {
        if ('object' === typeof Flatsome && 'object' == typeof Flatsome.behaviors) {
            var opts = ['lazy-load-images', 'quick-view', 'lightbox-video', 'commons', 'wishlist'];

            $.each(opts, function(index, value) {
                if ('undefined' !== typeof Flatsome.behaviors[value]) {
                    Flatsome.behaviors[value].attach();
                }
            });
        }
     });
});
</script>
<?php
    }


    /**
     * Override WooCommerce's result-count.php template
     */
    function locate_template( $template, $template_name, $template_path ) {
        if ( 'loop/result-count.php' == $template_name ) {
            $template = dirname( __FILE__ ) . '/templates/result-count.php';
        }

        return $template;
    }
}


new FWP_Flatsome();
