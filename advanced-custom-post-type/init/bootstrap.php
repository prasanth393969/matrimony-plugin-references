<?php

use ACPT\Admin\ACPT_License_Manager;
use ACPT\Includes\ACPT_DB;
use ACPT\Utils\PHP\Ini;
use ACPT\Utils\PHP\IP;

/**
 * Run the application in dev mode
 *
 * @return bool
 */
function devACPTMode()
{
	if(!is_plugin_active( 'query-monitor/query-monitor.php' )){
		return false;
	}

	$devSites = [
        'http://localhost:8000',
        'http://localhost:10003',
    ];

	return in_array(site_url(), $devSites);
}

/**
 * Activate the license from credentials
 *
 * @param $license
 */
function activateLicenseFromCredentials($license)
{
    if(ACPT_License_Manager::isLicenseValid()){
        return;
    }

    if( !function_exists('wp_redirect') ) {
        include_once( ABSPATH . 'wp-includes/pluggable.php' );
    }

    $url = 'https://acpt.io/wp-json/api/v1/license/activate';
    $siteUrl = site_url();
    $siteName = get_bloginfo('name');
    $ip = IP::getClientIP();

    $request = wp_remote_post($url, [
        'method'      => 'POST',
        'timeout'     => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'body'        => [
            'license'  => $license,
            'siteUrl'  => $siteUrl,
            'siteName' => $siteName,
            'ip'       => $ip,
        ],
    ]);

    if ( is_wp_error( $request ) or ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
        $error = 'There was a problem during the activation of the plugin via cURL. Please retry, in case the error persist, contact support to receive assistance.';
        wp_redirect(get_admin_url().'admin.php?page='.ACPT_PLUGIN_NAME.'&error=' . urlencode($error));
        exit();
    }

    $response = json_decode( wp_remote_retrieve_body( $request ) );

    if(!isset($response->id)){
        $error = 'The cURL response was empty. Please retry, in case the error persist, contact support to receive assistance.';
        wp_redirect(get_admin_url().'admin.php?page='.ACPT_PLUGIN_NAME.'&error=' . urlencode($error));
        exit();
    }

    $activate = ACPT_License_Manager::activateLicense(
        $response->id,
        $license,
        $siteName,
        $siteUrl,
        $response->user_email,
        $response->user_id,
    );

    if(!$activate){
        $error = 'The license was not activated. Please retry, in case the error persist, contact support to receive assistance.';
        wp_redirect(get_admin_url().'admin.php?page='.ACPT_PLUGIN_NAME.'&error=' . urlencode($error));
        exit();
    }

    wp_redirect(get_admin_url().'admin.php?page='.ACPT_PLUGIN_NAME);
    exit();
}

/**
 * Activate the license from token
 *
 * @param $token
 */
function activateLicenseFromToken($token)
{
    if(ACPT_License_Manager::isLicenseValid()){
        return;
    }

	if( !function_exists('wp_redirect') ) {
		include_once( ABSPATH . 'wp-includes/pluggable.php' );
	}

	$token = filter_var($token, FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);

	// Fix for Complianz
	// Try to activate only token with a minimum length
	if($token and strlen($token) >= 155){
		try {
			ACPT_License_Manager::activateLicenseFromToken($token);
			wp_redirect(get_admin_url().'admin.php?page='.ACPT_PLUGIN_NAME);
			exit();
		} catch (Exception $exception){
			$error = "An unexpected error occurs: ";
			$error .= $exception->getMessage();
			$error .= ' Please retry, in case the error persist, contact support to receive assistance.';
			wp_redirect(get_admin_url().'admin.php?page='.ACPT_PLUGIN_NAME.'&error=' . urlencode($error));
			exit();
		}
	}
}

/**
 * This function get the old version from the filemtime value of main plugin file
 *
 * @param int $old_version
 *
 * @return string
 */
function oldACPTPluginVersion($old_version)
{
	if(empty($old_version) or $old_version == 0){
		return null;
	}

	if(!is_numeric($old_version)){
		return null;
	}

	$date = date("Y-m-d", $old_version);

	if($date == "1970-01-01"){
		return null;
	}

	$config = Ini::read(__DIR__.'/../config.ini');
	$pluginVersions = $config['versions'];

	return (isset(array_flip($pluginVersions)[$date])) ? array_flip($pluginVersions)[$date] : null;
}

/**
 * Check for plugin upgrades
 */
function checkForACPTPluginUpgrades()
{
	try {
		// | =====================================================|
		// | LEGACY FORMAT | get_option('acpt_version')           |
		// | =====================================================|
		// | NEW FORMAT    | get_option('acpt_current_version')   |
		// | =====================================================|

		$old_version = get_option('acpt_current_version') ?? get_option('acpt_version', 0);
		$current_version = get_option('acpt_current_version') ? ACPT_PLUGIN_VERSION : filemtime(__FILE__);

		if ($old_version != $current_version) {

			ACPT_DB::flushCache();
			ACPT_DB::createSchema(ACPT_PLUGIN_VERSION, get_option('acpt_current_version') ?? oldACPTPluginVersion($old_version));
			ACPT_DB::sync();

			update_option('acpt_version', $current_version, false);
			update_option('acpt_current_version', ACPT_PLUGIN_VERSION, false);
		}
	} catch (\Exception $exception){
		// do nothing
	}
}
