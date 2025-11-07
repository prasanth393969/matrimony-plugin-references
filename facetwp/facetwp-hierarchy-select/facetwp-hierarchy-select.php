<?php
/*
Plugin Name: FacetWP - Hierarchy Select
Description: Hierarchy select facet type
Version: 0.5.3
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-hierarchy-select
*/

defined( 'ABSPATH' ) or exit;

define( 'FACETWP_HIERARCHY_SELECT_VERSION', '0.5.3' );


/**
 * FacetWP registration hook
 */
add_filter( 'facetwp_facet_types', function( $types ) {
    include( dirname( __FILE__ ) . '/class-hierarchy-select.php' );
    $types['hierarchy_select'] = new FacetWP_Facet_Hierarchy_Select_Addon();
    return $types;
});
