<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PremiumAddonsPro\Admin\Includes\Admin_Helper;

// Premium Addons Classes
use PremiumAddons\Includes\Helper_Functions;

$account_link = Helper_Functions::get_campaign_link( 'https://my.leap13.com/', 'license-page', 'wp-dash', 'get-pro' );
$get_license  = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/pro/#get-pa-pro', 'license-page', 'wp-dash', 'get-pro' );
$upgrade_link  = Helper_Functions::get_campaign_link( 'https://premiumaddons.com/docs/upgrade-premium-addons-license/', 'license-page', 'wp-dash', 'get-pro' );


$status = Admin_Helper::get_license_status();
$info = get_transient( 'pa_license_info' );

?>

<div class="pa-section-content">
	<div class="row">
		<div class="col-full">
			<form class="pa-license-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php settings_fields( 'papro_license' ); ?>
				<div class="pa-section-info-wrap">
					<div class="pa-section-info">

                        <?php if( isset( $info['id'] ) && '4' !== $info['id'] ) : ?>

                            <b><?php echo __( 'Upgrade to a Lifetime License today and <span style="font-weight: 600; color: #FF6000;">Get 25% OFF</span>. No more renewals! You’ll only pay the difference from your current plan.', 'premium-addons-pro' ); ?></b>

                            <ol>
                                <li>
                                    <span><?php echo __( 'Login to your account at: ', 'premium-addons-pro' ); ?><a href="<?php echo esc_url( $account_link ); ?>" target="_blank"><?php echo __( 'https://my.leap13.com', 'premium-addons-pro' ); ?></a></span>
                                </li>

                                <li>
                                    <span><?php echo __( 'Navigate to License Keys tab.', 'premium-addons-pro' ); ?></span>
                                </li>

                                <li>
                                    <span><?php echo __( 'Click on View Upgrades link.', 'premium-addons-pro' ); ?></span>
                                </li>

                                <li>
                                    <span><?php echo __( 'Click Upgrade License link next to the desired upgrade.', 'premium-addons-pro' ); ?></span>
                                </li>

                                <li>
                                <span><?php echo sprintf( __( 'Enter the code %s at checkout for an extra 25%% off.', 'premium-addons-pro' ), '<strong>HalloweenUL2025</strong>' ); ?></span>
                                </li>
                            </ol>

                        <?php else : ?>
                            <b><?php echo __( 'Enter your license key here to activate Premium Addons Pro, and enable feature updates, Premium Templates, white labeling options and premium support.', 'premium-addons-pro' ); ?></b>

                            <ol>
                                <li>
                                    <span><?php echo __( 'Log in to ', 'premium-addons-pro' ); ?><a href="<?php echo esc_url( $account_link ); ?>" target="_blank"><?php echo __( 'your account', 'premium-addons-pro' ); ?></a><?php echo __( ' to get your license key', 'premium-addons-pro' ); ?></span>
                                </li>

                                <li>
                                    <span>
                                        <?php echo __( 'If you don\'t have a license key yet, get ', 'premium-addons-pro' ); ?>
                                        <a href="<?php echo esc_url( $get_license ); ?>" target="_blank"><?php echo __( 'Premium Addons Pro', 'premium-addons-pro' ); ?></a>
                                        <?php echo __( 'now. ', 'premium-addons-pro' ); ?></span><span style="text-decoration: underline; font-weight: 600; color: #FF6000;"><?php echo __( 'SAVE UP TO 25%', 'premium-addons-pro' ); ?></span><span><?php echo __( ' on new purchases and ', 'premium-addons-pro' ); ?><a href="<?php echo esc_url( $upgrade_link ); ?>" target="_blank"><?php echo __( 'upgrades', 'premium-addons-pro' ); ?></a>.</span>
                                </li>

                                <li>
                                    <span><?php echo __( 'Copy the license key from your account and paste it below.', 'premium-addons-pro' ); ?></span>
                                </li>

                                <li>
                                    <span><?php echo __( 'Click on Activate to activate the license.', 'premium-addons-pro' ); ?></span>
                                </li>
                            </ol>

                        <?php endif; ?>

						<label for="papro-license-key"><?php _e( 'License Key' ); ?></label>
						<input id="papro-license-key" <?php echo ( $status !== false && $status == 'valid' ) ? 'disabled' : ''; ?> name="papro_license_key" placeholder="<?php echo __( 'Please enter your license key here', 'premium-addons-pro' ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( Admin_Helper::get_encrypted_key() ); ?>" />

						<?php

                        wp_nonce_field( 'papro_nonce', 'papro_nonce' );
						if ( $status !== false && $status == 'valid' ) { ?>
                            <input type="hidden" name="action" value="papro_license_deactivate" />
                            <?php submit_button( __( 'Deactivate', 'premium-addons-pro' ), 'primary', 'submit', false ); ?>
                            <span style="color:green;"><?php echo __( 'Active', 'premium-addons-pro' ); ?></span>
                        <?php } else { ?>
                            <input type="hidden" name="action" value="papro_license_activate" />
                            <?php submit_button( __( 'Activate', 'premium-addons-pro' ), 'primary', 'submit', false ); ?>
                            <span style="color:red;"><?php echo __( 'License not valid', 'premium-addons-pro' ); ?></span>
                        <?php } ?>

					</div>
				</div>
			</form>
		</div>
	</div>
</div> <!-- End Section Content -->
