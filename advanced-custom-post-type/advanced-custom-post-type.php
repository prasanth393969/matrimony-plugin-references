<?php

/**
 * @link              ####
 * @since             1.0.0
 * @package           advanced-custom-post-type
 *
 * @wordpress-plugin
 * Plugin Name:       ACPT
 * Plugin URI:        https://acpt.io
 * Description:       Create and manage custom post types, with advanced custom fields and taxonomies management
 * Version:           2.0.42
 * Author:            Mauro Cassani
 * Author URI:        https://github.com/mauretto78
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       advanced-custom-post-type
 * Domain Path:       /advanced-custom-post-type
 */

use ACPT\Admin\ACPT_License_Manager;
use ACPT\Admin\ACPT_Updater;
use ACPT\Includes\ACPT_Plugin;

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Bootstrap the application
 */
require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');
require_once(plugin_dir_path(__FILE__) . '/init/bootstrap.php');

/**
 * Fix PHP headers
 */
ob_start();

if( !function_exists('is_plugin_active') ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * General Settings
 */
define('ACPT_PLUGIN_NAME', 'advanced-custom-post-type');
define('ACPT_PLUGIN_VERSION', '2.0.42');
define('ACPT_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ));
define('ACPT_DEV_MODE', devACPTMode());

/**
 *  Plugin activation
 */

// 1. Activation form token
if(isset($_GET['token']) and !empty($_GET['token']))
{
	activateLicenseFromToken($_GET['token']);
}

// 2. Activation from wp-config.php
if(defined('ACPT_LICENSE_KEY'))
{
    activateLicenseFromCredentials(ACPT_LICENSE_KEY);
}

define('ACPT_IS_LICENSE_VALID', ACPT_License_Manager::isLicenseValid());

/**
 * Activation/deactivation hooks
 */
register_activation_hook( __FILE__, [new ACPT_Plugin(), 'activationHook'] );
register_deactivation_hook( __FILE__, [new ACPT_Plugin(), 'deactivationHook'] );

checkForACPTPluginUpgrades();

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
try {
    $plugin = new ACPT_Plugin();
    $plugin->run();

    // Updates management
    $updated = new ACPT_Updater(__FILE__);
    $updated->initialize();
    $updated->sendInsights();

} catch (\Exception $exception){
    //
    function wpb_admin_notice_error() {
        echo '
			<div class="notice notice-error is-dismissible">
	            <p>Something went wrong.</p>
			</div>
		';
    }

    add_action( 'admin_notices', 'wpb_admin_notice_error' );
}

// Insert license data directly into options table
$license_key = 'acpt_license_active';
$encrypted_key = hash('ripemd128', $license_key);
$mock_license_data = array(
    'activation_id' => 'gpl-' . md5(site_url()),
    'license' => md5('gpl-times-license'),
    'site_name' => get_bloginfo('name'),
    'site_url' => get_bloginfo('url'),
    'user_email' => get_bloginfo('admin_email'),
    'user_id' => 1,
    'ip' => '127.0.0.1'
);
update_option($encrypted_key, $mock_license_data);