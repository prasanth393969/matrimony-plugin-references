<?php
/**
 * PA Setup Functions.
 */

namespace PremiumAddonsPro\Admin\Includes;

// PAPRO Classes.
use PremiumAddonsPro\Admin\Includes\Admin_Notices;
use PremiumAddonsPro\Includes\PAPRO_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PA_Installer.
 */
class PA_Installer {

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	public static $instance = null;

	/**
	 * Class Constructor
	 */
	public function __construct() {

		$plugin   = 'premium-addons-for-elementor';

		$plugin_info = $this->get_plugin_info( $plugin );

		// Install Premium Addons for Elementor
		if ( isset( $plugin_info->download_link ) ) {

			if ( $this->install_plugin( $plugin_info->download_link ) ) {

				return delete_transient( 'papro_install_free' );

			}
		}

		return false;
	}


	/**
	 * Install and activates pa.
	 *
	 * @since 2.5.3
	 * @access public
	 */
	public function install_plugin( $plugin_url ) {

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$upgrader->install( $plugin_url );

		// activate plugin
		activate_plugin( $upgrader->plugin_info(), '', false, true );

		return $skin->result;
	}

	public function get_plugin_info( $slug = '' ) {
		$args = array(
			'slug'   => $slug,
			'fields' => array(
				'version' => false,
			),
		);

		$response = wp_remote_post(
			'http://api.wordpress.org/plugins/info/1.0/',
			array(
				'body' => array(
					'action'  => 'plugin_information',
					'request' => serialize( (object) $args ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		} else {
			$response = unserialize( wp_remote_retrieve_body( $response ) );

			if ( $response ) {
				return $response;
			} else {
				return false;
			}
		}
	}

	/**
	 * Creates and returns an instance of the class
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return object
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();

		}

		return self::$instance;
	}
}
