<?php
/**
 * PAPRO Core.
 */

namespace PremiumAddonsPro\Includes;

use PremiumAddonsPro\Admin\Includes\Admin_Helper;
use PremiumAddonsPro\Admin\Includes\Admin_Notices;
use PremiumAddonsPro\Admin\Includes\PA_Installer;
use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No access of directly access.
}

if ( ! class_exists( 'PAPRO_Core' ) ) {

	/**
	 * Intialize and Sets up the plugin.
	 */
	class PAPRO_Core {

		/**
		 * Class instance
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Sets up needed actions/filters for the plug-in to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return void
		 */
		public function __construct() {

			spl_autoload_register( array( $this, 'autoload' ) );

			// Register Activation Hook.
			register_activation_hook( PREMIUM_PRO_ADDONS_FILE, array( $this, 'register_activation_hook' ) );

            // Load plugin textdomain.
			add_action( 'init', array( $this, 'i18n' ) );

			// Load plugin core.
			add_action( 'plugins_loaded', array( $this, 'papro_setup' ), 9 );

			// Check if free version of Premium Addons installed.
			if ( self::check_premium_free() ) {

				// Load Addons required Files.
				add_action( 'elementor/init', array( $this, 'elementor_init' ) );
			} else {
                add_action( 'admin_init', array( $this, 'load_pa_installer' ) );
            }

			// if ( defined( 'ELEMENTOR_PRO_VERSION' ) && defined( 'PREMIUM_ADDONS_VERSION' ) ) {
			// require_once PREMIUM_PRO_ADDONS_PATH . 'includes/grid-builder/pa-grid-builder-handler.php';
			// }
		}

		/**
		 * AutoLoad
		 *
		 * @since 2.0.7
		 * @param string $class class.
		 */
		public function autoload( $class ) {

			if ( 0 !== strpos( $class, 'PremiumAddonsPro' ) ) {
				return;
			}

			$class_to_load = $class;

			if ( ! class_exists( $class_to_load ) ) {
				$filename = strtolower(
					preg_replace(
						array( '/^PremiumAddonsPro\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ),
						array( '', '$1-$2', '-', DIRECTORY_SEPARATOR ),
						$class_to_load
					)
				);

				$filename = PREMIUM_PRO_ADDONS_PATH . $filename . '.php';

				if ( is_readable( $filename ) ) {

					include $filename;
				}
			}
		}

		/**
		 * Register Activation Hook
		 *
		 * Reset hide white labeling tab option on plugin activate
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function register_activation_hook() {

			$white_label_settings = is_network_admin() ? get_site_option( 'pa_wht_lbl_save_settings' ) : get_option( 'pa_wht_lbl_save_settings' );

			if ( isset( $white_label_settings['premium-wht-lbl-option'] ) ) {

				$white_label_settings['premium-wht-lbl-option'] = 0;

				is_network_admin() ? update_site_option( 'pa_wht_lbl_save_settings', $white_label_settings ) : update_option( 'pa_wht_lbl_save_settings', $white_label_settings );

			}

			set_transient( 'papro_install_free', true, 1800 );
		}

		/**
		 * Elementor Init
		 *
		 * Load required files after init Elementor
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function elementor_init() {

			Addons_Integration::get_instance();
		}

		/**
		 * Installs translation text domain and checks if Elementor is installed
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function papro_setup() {

			$this->init_files();

			if ( defined( 'ELEMENTOR_PRO_VERSION' ) && defined( 'PREMIUM_ADDONS_VERSION' ) ) {
				require_once PREMIUM_PRO_ADDONS_PATH . 'includes/grid-builder/pa-grid-builder-handler.php';
			}
		}

		/**
		 * Init Plugin Updater
		 *
		 * @since 2.0.7
		 * @access public
		 */
		public function init_plugin_updater() {

			// Disable SSL verification.
			add_filter( 'edd_sl_api_request_verify_ssl', '__return_false' );

			// Get License Key.
			$license_key = Admin_Helper::get_license_key();

			new \PAPRO_Plugin_Updater(
				PAPRO_STORE_URL,
				PREMIUM_PRO_ADDONS_FILE,
				array(
					'version' => PREMIUM_PRO_ADDONS_VERSION,
					'license' => $license_key,
					'item_id' => PAPRO_ITEM_ID,
					'author'  => 'Leap13',
					'url'     => home_url(),
					'beta'    => false,
				)
			);
		}

		/**
		 * Check Premium Free
		 *
		 * Check if free version is activated
		 *
		 * @since 1.1.1
		 * @access public
		 *
		 * @return boolean PA active
		 */
		public static function check_premium_free() {

			return defined( 'PREMIUM_ADDONS_VERSION' );
		}

        /**
		 * Load plugin translated strings using text domain
		 *
		 * @since 2.9.44
		 * @access public
		 *
		 * @return void
		 */
		public function i18n() {

			load_plugin_textdomain( 'premium-addons-pro' );
		}


		/**
		 * Init Files
		 *
		 * Require initial necessary files
		 *
		 * @since 1.1.1
		 * @access public
		 *
		 * @return void
		 */
		public function init_files() {

			$current_page = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

			if ( self::check_premium_free() ) {

				if ( is_admin() ) {
					\PremiumAddonsPro\Admin\Includes\Admin_Helper::get_instance();

					if ( false === strpos( $current_page, 'action=elementor' ) ) {
						if ( ! class_exists( 'PAPRO_Plugin_Updater' ) ) {
							// Require Plugin Updater.
							require_once PREMIUM_PRO_ADDONS_PATH . 'license/updater.php';
						}
						$this->init_plugin_updater();

					}
				}

				Plugin::instance();

			}

			if ( is_admin() && false === strpos( $current_page, 'action=elementor' ) ) {
				\PremiumAddonsPro\Admin\Includes\Admin_Notices::get_instance();
			}
		}

		/**
		 * Load PA Installer
		 *
		 * Load the handler file to install the free version.
		 *
		 * @since 2.5.3
		 * @access public
		 *
		 * @return void
		 */
		public function load_pa_installer() {

            $check_install = get_transient( 'papro_install_free' );

            if( $check_install ) {
                PA_Installer::get_instance();
            }

		}

		/**
		 * Get instance
		 *
		 * Creates and returns an instance of the class
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @return object
		 */
		public static function get_instance() {

			if ( ! isset( self::$instance ) ) {

				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}

if ( ! function_exists( 'premium_addons_pro' ) ) {
	/**
	 * Returns an instance of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function premium_addons_pro() {
		return PAPRO_Core::get_instance();
	}
}
premium_addons_pro();
