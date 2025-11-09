<?php

/**
 * PAPRO Admin Notices.
 */
namespace PremiumAddonsPro\Admin\Includes;

use PremiumAddonsPro\Includes\PAPRO_Core;
use PremiumAddonsPro\Admin\Includes\Admin_Helper;
use PremiumAddonsPro\License\API;

use PremiumAddons\Includes\Helper_Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Admin_Notices {

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	private static $instance = null;

	/**
	 * Premium Addons Slug
	 *
	 * @var slug
	 */
	private static $slug = 'premium-addons-for-elementor';

	/**
	 * Class Constructor
	 */
	public function __construct() {

		// Check if Required Plugins are installed
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

	}

	/**
	 * Admin Notices
	 *
	 * Check if admin notice should be shown or not
	 *
	 * @since 1.4.5
	 * @access public
	 */
	public function admin_notices() {

        if ( PAPRO_Core::check_premium_free() ) {

			$license_key = Admin_Helper::get_license_key();

			if ( empty( $license_key ) ) {

				$this->license_notices();

			} else {

                // if ( ! Helper_Functions::check_hide_notifications() ) {
                //     $this->bf_upgrade_notice( $license_key );
                // }

            }
		}

		$this->required_plugins_check();

		$current_screen = Admin_Helper::get_current_screen();

		if ( strpos( $current_screen, 'premium-addons' ) !== false ) {

			$this->check_papro_license_messages();

		}
	}

	/**
	 * Check PAPRO License Messages
	 *
	 * Check and print PAPRO license notices
	 *
	 * @since 2.2.0
	 * @access public
	 */
	public function check_papro_license_messages() {

		if ( ( isset( $_GET['sl_activation'] ) || isset( $_GET['sl_deactivation'] ) ) && ! empty( $_GET['message'] ) ) {

			$target = isset( $_GET['sl_activation'] ) ? $_GET['sl_activation'] : null;

			switch ( $target ) {
				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo wp_kses_post( $message ); ?></p>
					</div>
					<?php
					break;
				case 'true':
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;
			}
		}
	}

	/*
	 * Prints admin notice message when license is not active
	 *
	 * @since 1.4.9
	 * @access private
	 *
	 * @param $url license activation admin page
	 */
	private function print_license_activate_message( $url, $status ) {

		?>

			<div class="error">
				<?php

				if ( 'invalid' === $status ) {
					printf(
						'<p><b>Invalid Premium Addons Pro License Key!</b><br><span>Get a license to get access to 90+ Elementor widgets, premium support and full access to 550+ Premium Templates and white labeling. <a href="%s">Purchase Now</a></span></p>',
						'https://premiumaddons.com/pro'
					);
				} elseif ( 'expired' === $status ) {
					printf(
						'<p><b>Your Premium Addons Pro License Key Has Expired!</b><br><span>Renew now and get a 15%% discount. Get access to 550+ Premium Templates and Premium Support. <a href="%s">Renew Now</a></span></p>',
						PAPRO_STORE_URL
					);
				} else {
					printf(
						'<p>Thank you for purchasing <b>Premium Addons Pro!</b><br><span>Please <a href="%s">activate your license key</a> to get access to 90+ Elementor widgets, premium support and full access to 550+ Premium Templates and white labeling.</span></p>',
						$url
					);
				}

				?>
			</div>

		<?php
	}

	/**
	 * License Notices
	 *
	 * Shows an admin notice when to activate/validate license
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function license_notices() {

		$status = Admin_Helper::get_license_status();

		$license_url = add_query_arg(
            array(
                'page' => 'premium-addons#tab=license',
            ),
            esc_url( admin_url( 'admin.php' ) )
        );

		$this->print_license_activate_message( $license_url, $status );
	}

	/**
	 * Shows an admin notice when the free version is missing
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function required_plugins_check() {

		$pa_path = sprintf( '%1$s/%1$s.php', self::$slug );

		if ( ! PAPRO_Core::check_premium_free() ) {

			$message = '';

			if ( self::is_plugin_installed( $pa_path ) ) {
				if ( current_user_can( 'activate_plugins' ) ) {

					$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $pa_path . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $pa_path );

					$message = '<p>' . __( 'Premium Addons PRO is not working because you need to activate Premium Addons for Elementor plugin.', 'premium-addons-pro' ) . '</p>';

					$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Now', 'premium-addons-pro' ) ) . '</p>';

				}
			} elseif ( current_user_can( 'install_plugins' ) ) {

					$install_url = wp_nonce_url( self_admin_url( sprintf( 'update.php?action=install-plugin&plugin=%s', self::$slug ) ), sprintf( 'install-plugin_%s', self::$slug ) );

					$message = '<p>' . __( 'Premium Addons PRO is not working because you need to Install Premium Addons for Elementor plugin.', 'premium-addons-pro' ) . '</p>';

					$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Now', 'premium-addons-pro' ) ) . '</p>';
			}

			$this->render_admin_notices( $message );
		}
	}

	/**
	 * Checks if a plugin is installed
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return boolean
	 */
	public static function is_plugin_installed( $plugin_path ) {

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugins = get_plugins();

		return isset( $plugins[ $plugin_path ] );
	}

	/**
	 * Renders an admin notice error message
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function render_admin_notices( $message ) {
		?>
			<div class="error">
				<?php echo $message; ?>
			</div>
		<?php
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

		if ( self::$instance == null ) {

			self::$instance = new self();

		}

		return self::$instance;
	}
}
