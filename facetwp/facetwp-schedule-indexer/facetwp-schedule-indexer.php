<?php
/*
Plugin Name: FacetWP Schedule Indexer
Plugin URI: https://facetwp.com/
Description: Runs indexer periodically by cron
Version: 1.1
Author: FacetWP, LLC
*/

add_action( 'fwp_scheduled_index', 'fwp_scheduled_index' );
function fwp_scheduled_index() {
  FWP()->indexer->index(); // Trigger a full re-index
}

register_activation_hook( __FILE__, 'fwp_schedule_indexer_activation' );
function fwp_schedule_indexer_activation() {
  if ( ! wp_next_scheduled( 'fwp_scheduled_index' ) ) {

    // The event will run 'hourly'. Valid other values: 'twicedaily', 'daily' and 'weekly'.
    // Or pass a custom schedule name (created with the 'cron_schedules' hook.
    $index_schedule =  apply_filters( 'facetwp_index_schedule', 'hourly' );
    wp_schedule_event( time(), $index_schedule, 'fwp_scheduled_index' );
  }
}

register_deactivation_hook( __FILE__, 'fwp_schedule_indexer_deactivation' );
function fwp_schedule_indexer_deactivation() {
  wp_clear_scheduled_hook( 'fwp_scheduled_index' );
}