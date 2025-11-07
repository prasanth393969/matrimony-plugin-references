<?php
/*
Plugin Name: FacetWP - Conditional Logic
Description: Toggle facets based on certain conditions
Version: 1.5
Author: FacetWP, LLC
Author URI: https://facetwp.com/
GitHub URI: facetwp/facetwp-conditional-logic
*/

defined( 'ABSPATH' ) or exit;

class FacetWP_Conditional_Logic_Addon
{

    public $rules;
    public $facets = [];
    public $templates = [];


    function __construct() {

        define( 'FWPCL_VERSION', '1.5' );
        define( 'FWPCL_DIR', dirname( __FILE__ ) );
        define( 'FWPCL_URL', plugins_url( '', __FILE__ ) );
        define( 'FWPCL_BASENAME', plugin_basename( __FILE__ ) );

        add_action( 'facetwp_init', [ $this, 'init' ] );
    }


    function init() {
        if ( ! function_exists( 'FWP' ) ) {
            return;
        }

        load_plugin_textdomain( 'facetwp-conditional-logic', false, basename( FWPCL_DIR ) . '/languages' );

        $this->facets = FWP()->helper->get_facets();
        $this->templates = FWP()->helper->get_templates();

        // load settings
        $rulesets = get_option( 'fwpcl_rulesets' );
        $this->rulesets = empty( $rulesets ) ? [] : json_decode( $rulesets, true );

        $this->admin_i18n = [
          'Saving' => __( 'Saving', 'facetwp-conditional-logic' ),
          'Importing' => __( 'Importing', 'facetwp-conditional-logic' ),
          'Changes saved' => __( 'Changes saved', 'facetwp-conditional-logic' ),
          'OR' => __( 'OR', 'facetwp-conditional-logic' ),
          'IF' => __( 'IF', 'facetwp-conditional-logic' ),
          'AND' => __( 'AND', 'facetwp-conditional-logic' ),
          'THEN' => __( 'THEN', 'facetwp-conditional-logic' ),
          'Delete this ruleset?' => __( 'Delete this ruleset?', 'facetwp-conditional-logic' ),
        ];

        // register admin assets
        wp_register_script( 'fwpcl-admin', FWPCL_URL . '/assets/js/admin.js', [ 'jquery' ], FWPCL_VERSION, false );
        wp_register_style( 'fwpcl-admin', FWPCL_URL . '/assets/css/admin.css', [], FWPCL_VERSION );
        wp_register_style( 'fwp-admin', FACETWP_URL . '/assets/css/admin.css', [], FACETWP_VERSION );

        // ajax
        add_action( 'wp_ajax_fwpcl_import', [ $this, 'import' ] );
        add_action( 'wp_ajax_fwpcl_save', [ $this, 'save_rules' ] );

        // wp hooks
        add_filter( 'facetwp_assets', [ $this, 'load_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }


    function import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $rulesets = stripslashes( $_POST['import_code'] );
        update_option( 'fwpcl_rulesets', $rulesets );
        _e( 'All done!', 'facetwp-conditional-logic' );
        exit;
    }


    function save_rules() {
        if ( current_user_can( 'manage_options' ) ) {
            $rulesets = stripslashes( $_POST['data'] );
            $json_test = json_decode( $rulesets, true );

            // check for valid JSON
            if ( is_array( $json_test ) ) {
                update_option( 'fwpcl_rulesets', $rulesets );
                _e( 'Rules saved', 'facetwp-conditional-logic' );
            }
            else {
                _e( 'Error: invalid JSON', 'facetwp-conditional-logic' );
            }
        }
        exit;
    }


    function admin_menu() {
        add_options_page( 'FacetWP Logic', 'FacetWP Logic', 'manage_options', 'fwpcl-admin', [ $this, 'settings_page' ] );
    }


    function enqueue_scripts( $hook ) {
        if ( 'settings_page_fwpcl-admin' == $hook ) {
            wp_enqueue_script( 'fwpcl-admin' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_style( 'media-views' );
            wp_enqueue_style( 'fwp-admin' );
            wp_enqueue_style( 'fwpcl-admin' );
            wp_localize_script( 'fwpcl-admin', 'FWPCL', [ 'rulesets' => $this->rulesets, 'i18n' => $this->admin_i18n ] );
        }
    }


    function settings_page() {
        include( dirname( __FILE__ ) . '/page-settings.php' );
    }


    function load_assets( $assets ) {
        FWP()->display->json['rulesets'] = $this->rulesets;

        $assets['fwpcl-front.css'] = [ FWPCL_URL . '/assets/css/front.css', FWPCL_VERSION ];
        $assets['fwpcl-front.js'] = [ FWPCL_URL . '/assets/js/front.js', FWPCL_VERSION ];
        return $assets;
    }
}


new FacetWP_Conditional_Logic_Addon();
