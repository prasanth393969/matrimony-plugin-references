<?php
/*
Plugin Name: FacetWP - Color
Description: Filter results by color
Version: 1.7.1
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-color
*/

defined( 'ABSPATH' ) or exit;

define( 'FACETWP_COLOR_VERSION', '1.7.1' );


/**
 * FacetWP registration hook
 */
add_filter( 'facetwp_facet_types', function( $types ) {
    include( dirname( __FILE__ ) . '/class-color.php' );
    $types['color'] = new FacetWP_Facet_Color_Addon();
    return $types;
});

/**
 * Add integrations
 */
add_action( 'init', function() {
    if ( class_exists( 'Woo_Variation_Swatches' ) ) {
        include( dirname( __FILE__ ) . '/integrations/class-woo-variation-swatches.php' );
    }
    if ( class_exists( 'Iconic_Woo_Attribute_Swatches' ) ) {
        include( dirname( __FILE__ ) . '/integrations/class-iconic-woo-attribute-swatches.php' );
    }
});
