<?php

namespace Essential_Addons_Elementor\Pro\Traits;

use Essential_Addons_Elementor\Pro\Classes\Helper as ClassesHelper;

// use \Essential_Addons_Elementor\Classes\Helper;

trait Helper
{
    use \Essential_Addons_Elementor\Template\Woocommerce\Checkout\Woo_Checkout_Helper;

    /**
     * Compare an installed plugins version
     *
     * @since 3.0.0
     */
    public function version_compare($plugin, $version, $condition)
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin = get_plugin_data(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin, false, false);

        return version_compare($plugin['Version'], $version, $condition);
    }

    // Subscribe to Mailchimp list
    public function mailchimp_subscribe_with_ajax()
    {
		if ( empty( $_POST['nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['nonce'], 'essential-addons-elementor' ) ) {
			return;
		}

        if (!isset($_POST['fields'])) {
            return;
        }

        $api_key = get_option('eael_save_mailchimp_api');
        $list_id = sanitize_text_field( $_POST['listId'] );

        $pattern = '/^[0-9a-z]{32}(-us)(0?[1-9]|[1-9][0-9])?$/';
        if ( ! preg_match($pattern, $api_key) ) {
            return;
        }

        $list_id_double_optin = ClassesHelper::mailchimp_lists('mailchimp', true);
        $is_double_optin = ! empty( $list_id_double_optin[$list_id] ) ? $list_id_double_optin[$list_id] : false;
        parse_str($_POST['fields'], $settings);

        $merge_fields = array(
            'FNAME' => !empty($settings['eael_mailchimp_firstname']) ? $settings['eael_mailchimp_firstname'] : '',
            'LNAME' => !empty($settings['eael_mailchimp_lastname']) ? $settings['eael_mailchimp_lastname'] : '',
        );

        $settings_tags_string = !empty($settings['eael_mailchimp_tags']) ? sanitize_text_field($settings['eael_mailchimp_tags']) : '';
        if(!empty($settings_tags_string)){
            $settings_tags_string = explode(',', $settings_tags_string);
        }

        $body_params = array(
            'email_address' => $settings['eael_mailchimp_email'],
            'status' => $is_double_optin ? 'pending' : 'subscribed',
            'merge_fields' => $merge_fields,
        );

        if(!empty($settings_tags_string)){
            $body_params['tags'] = $settings_tags_string;
        }


        $response = wp_safe_remote_post(
            'https://' . substr($api_key, strpos(
                $api_key,
                '-'
            ) + 1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($settings['eael_mailchimp_email'])),
            [
                'method' => 'PUT',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                ],
                'body' => wp_json_encode( $body_params ),
            ]
        );

        if (!is_wp_error($response)) {
            $response = json_decode(wp_remote_retrieve_body($response));

            if (!empty($response)) {
                if ($response->status == 'subscribed' || $response->status == 'pending') {
                    wp_send_json([
                        'status' => $response->status,
                    ]);
                } else {
                    wp_send_json([
                        'status' => $response->title,
                    ]);
                }
            }
        }
    }

    public function login_register_mailchimp_integration_subscribe( $user_id, $user_data, $settings )
    {
        if ( empty($user_data['user_email']) ) {
            return;
        }

        if( !( !empty( $settings['eael_register_mailchimp_integration_enable'] ) && 'yes' === $settings['eael_register_mailchimp_integration_enable'] ) ){
            return;
        }
        $api_key = sanitize_text_field( get_option('eael_lr_mailchimp_api_key') );
        $list_id = !empty($settings['eael_mailchimp_lists']) ? sanitize_text_field( $settings['eael_mailchimp_lists'] ) : '';

        if ( empty($api_key) || empty($list_id)) {
            return;
        }

        $pattern = '/^[0-9a-z]{32}(-us)(0?[1-9]|[1-9][0-9])?$/';
        if ( ! preg_match($pattern, $api_key) ) {
            return;
        }

        $list_id_double_optin = ClassesHelper::mailchimp_lists('mailchimp', true);
        $is_double_optin = ! empty( $list_id_double_optin[$list_id] ) ? $list_id_double_optin[$list_id] : false;

        $merge_fields = array(
            'FNAME' => !empty($user_data['first_name']) ? sanitize_text_field( $user_data['first_name'] ) : '',
            'LNAME' => !empty($user_data['last_name']) ? sanitize_text_field( $user_data['last_name'] ) : '',
        );

        $email = sanitize_email( $user_data['user_email'] );

        $response = wp_safe_remote_post(
            'https://' . substr($api_key, strpos(
                $api_key,
                '-'
            ) + 1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower( $email )),
            [
                'method' => 'PUT',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
                ],
                'body' => wp_json_encode([
                    'email_address' => $email,
                    'status' => $is_double_optin ? 'pending' : 'subscribed',
                    'merge_fields' => $merge_fields,
                ]),
            ]
        );

        if (!is_wp_error($response)) {
            return true;
        }else {
            return false;
        }
    }

    public function ajax_post_search() {
        if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'essential-addons-elementor' ) ) {
            return;
        }

        $html = '';
        $args = array(
            'post_type'   => esc_attr( $_POST['post_type'] ),
            'post_status' => 'publish',
            's'           => esc_attr( $_POST['key'] ),
        );

        $query = new \WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $html .= '<div class="ajax-search-result-post">
                    <h6><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h6>
                </div>';
            }
        }

        echo wp_kses( $html, ClassesHelper::eael_allowed_tags() );
        die();
    }

    public function connect_remote_db()
    {
        // check ajax referer
        check_ajax_referer('essential-addons-elementor', 'security');

        $result = [
            'connected' => false,
            'tables' => [],
        ];

        if (empty($_REQUEST['host']) || empty($_REQUEST['username']) || empty($_REQUEST['password']) || empty($_REQUEST['database'])) {
            wp_send_json($result);
        }

        $conn = new \mysqli($_REQUEST['host'], $_REQUEST['username'], $_REQUEST['password'], $_REQUEST['database']);

        if ($conn->connect_error) {
            wp_send_json($result);
        } else {
            $query = $conn->query("show tables");

            if ($query) {
                $tables = $query->fetch_all();

                $result['connected'] = true;
                $result['tables'] = wp_list_pluck($tables, 0);
            }

            $conn->close();
        }

        wp_send_json($result);
    }

    /**
     * Show the split layout.
     */
    public static function woo_checkout_render_split_template_($checkout, $settings)
    {

        $ea_woo_checkout_btn_next_data = $settings['ea_woo_checkout_tabs_btn_next_text'];
	    if ( get_option( 'woocommerce_enable_coupons' ) === 'yes' && $settings['ea_woo_checkout_coupon_hide'] !== 'yes' ) {
		    $enable_coupon = 1;
	    } else {
		    $enable_coupon = '';
        }
?>
        <div class="layout-split-container" data-coupon="<?php echo esc_attr( $enable_coupon ); ?>">
            <div class="info-area">
                <ul class="split-tabs">
                    <?php
                    $step1_class = 'first active';
                    $enable_login_reminder = false;

                    if ((\Elementor\Plugin::$instance->editor->is_edit_mode() && 'yes' === $settings['ea_section_woo_login_show']) || (!is_user_logged_in() && 'yes' === get_option('woocommerce_enable_checkout_login_reminder'))) {
                        $enable_login_reminder = true;
                        $step1_class = '';
                    ?>
                        <li id="step-0" data-step="0" class="split-tab first active"><?php echo esc_html( $settings['ea_woo_checkout_tab_login_text'] ); ?></li>
                    <?php
                    }
                    if ( get_option( 'woocommerce_enable_coupons' ) === 'yes' && $settings['ea_woo_checkout_coupon_hide'] !== 'yes' ) { ?>
                        <li id="step-1" class="split-tab <?php echo esc_attr( $step1_class ); ?>" data-step="1"><?php echo
                                                                                                        esc_html( $settings['ea_woo_checkout_tab_coupon_text'] ); ?></li>
                        <li id="step-2" class="split-tab" data-step="2"><?php echo esc_html( $settings['ea_woo_checkout_tab_billing_shipping_text'] ); ?></li>
                        <li id="step-3" class="split-tab last" data-step="3"><?php echo esc_html( $settings['ea_woo_checkout_tab_payment_text'] ); ?></li>
                    <?php } else { ?>
                        <li id="step-1" class="split-tab <?php echo esc_attr( $step1_class ); ?>" data-step="1"><?php echo esc_html( $settings['ea_woo_checkout_tab_billing_shipping_text'] ); ?></li>
                        <li id="step-2" class="split-tab last" data-step="2"><?php echo esc_html( $settings['ea_woo_checkout_tab_payment_text'] ); ?></li>
                    <?php } ?>
                </ul>

                <div class="split-tabs-content">
                    <?php
                    // If checkout registration is disabled and not logged in, the user cannot checkout.
                    if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
                        echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'essential-addons-elementor')));
                        return;
                    }
                    ?>

                    <?php do_action('woocommerce_before_checkout_form', $checkout); ?>

                    <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

                        <?php if ($checkout->get_checkout_fields()) : ?>

                            <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                            <div class="col2-set" id="customer_details">
                                <div class="col-1">
                                    <?php do_action('woocommerce_checkout_billing'); ?>
                                </div>

                                <div class="col-2">
                                    <?php do_action('woocommerce_checkout_shipping'); ?>
                                </div>
                            </div>

                            <?php do_action('woocommerce_checkout_after_customer_details'); ?>

                        <?php endif; ?>

                        <?php do_action('woocommerce_checkout_order_review'); ?>

                    </form>

                    <?php do_action('woocommerce_after_checkout_form', $checkout); ?>

                    <div class="steps-buttons">
                        <button class="ea-woo-checkout-btn-prev"><?php echo esc_html( $settings['ea_woo_checkout_tabs_btn_prev_text'] ); ?></button>
                        <button class="ea-woo-checkout-btn-next" data-text="<?php echo esc_attr(htmlspecialchars(wp_json_encode($ea_woo_checkout_btn_next_data), ENT_QUOTES, 'UTF-8')); ?>"><?php echo esc_html( $settings['ea_woo_checkout_tabs_btn_next_text'] ); ?></button>
                        <button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="ea_place_order" value="<?php echo esc_attr( $settings['ea_woo_checkout_place_order_text'] ); ?>" data-value="<?php echo esc_attr( $settings['ea_woo_checkout_place_order_text'] ); ?>" style="display:none;
                        "><?php echo esc_html( $settings['ea_woo_checkout_place_order_text'] ); ?></button>
                    </div>
                </div>
            </div>

            <div class="table-area">
                <div class="ea-woo-checkout-order-review">
                    <?php self::checkout_order_review_default($settings); ?>
                </div>
            </div>
        </div>
    <?php }

    /**
     * validate woocommerce post code
     *
     * @since  3.6.4
     */
    public function eael_woo_checkout_post_code_validate()
    {
	    $data     = $_POST['data'];
	    $validate = [
		    'message' => __( 'Billing Postcode is not a valid postcode / ZIP', 'essential-addons-elementor' ),
		    'valid'   => true
	    ];
	    if ( isset( $data['postcode'] ) ) {

		    $format = wc_format_postcode( $data['postcode'], $data['country'] );
		    if ( '' !== $format && ! \WC_Validation::is_postcode( $data['postcode'], $data['country'] ) ) {
			    $validate['valid'] = false;
		    }

		    if ( $err_message = apply_filters( 'eael_woocommerce_validate_postcode_error_message', false, $data['postcode'] ) ) {
			    $validate = [
				    'message' => $err_message,
				    'valid'   => false
			    ];
		    }
	    }
        wp_send_json($validate);
    }

    /**
     * Show the multi step layout.
     */
    public static function woo_checkout_render_multi_steps_template_($checkout, $settings)
    {

        $ea_woo_checkout_btn_next_data = $settings['ea_woo_checkout_tabs_btn_next_text'];
	    if ( get_option( 'woocommerce_enable_coupons' ) === 'yes' && $settings['ea_woo_checkout_coupon_hide'] !== 'yes' ) {
		    $enable_coupon = 1;
	    } else {
		    $enable_coupon = '';
	    }
    ?>
        <div class="layout-multi-steps-container" data-coupon="<?php echo esc_attr( $enable_coupon ); ?>">
            <ul class="ms-tabs">
                <?php
                $step1_class = 'first active';
                $enable_login_reminder = false;

                if ((\Elementor\Plugin::$instance->editor->is_edit_mode() && 'yes' === $settings['ea_section_woo_login_show']) || (!is_user_logged_in() && 'yes' === get_option('woocommerce_enable_checkout_login_reminder'))) {
                    $enable_login_reminder = true;
                    $step1_class = '';
                ?>
                    <li class="ms-tab first active" id="step-0" data-step="0"><?php echo
                                                                                    esc_html( $settings['ea_woo_checkout_tab_login_text'] ); ?></li>
                <?php }
                if ( get_option( 'woocommerce_enable_coupons' ) === 'yes' && $settings['ea_woo_checkout_coupon_hide'] !== 'yes' ) { ?>
                    <li class="ms-tab <?php echo esc_attr( $step1_class ); ?>" id="step-1" data-step="1"><?php echo
                                                                                                    esc_html( $settings['ea_woo_checkout_tab_coupon_text'] ); ?></li>
                    <li class="ms-tab" id="step-2" data-step="2"><?php echo esc_html( $settings['ea_woo_checkout_tab_billing_shipping_text'] ); ?></li>
                    <li class="ms-tab last" id="step-3" data-step="3"><?php echo esc_html( $settings['ea_woo_checkout_tab_payment_text'] ); ?></li>
                <?php } else { ?>
                    <li class="ms-tab <?php echo esc_attr( $step1_class ); ?>" id="step-1" data-step="1"><?php echo
                                                                                                    esc_html( $settings['ea_woo_checkout_tab_billing_shipping_text'] ); ?></li>
                    <li class="ms-tab last" id="step-2" data-step="2"><?php echo esc_html( $settings['ea_woo_checkout_tab_payment_text'] ); ?></li>
                <?php }
                ?>
            </ul>

            <div class="ms-tabs-content-wrap">
                <div class="ms-tabs-content">
                    <?php
                    // If checkout registration is disabled and not logged in, the user cannot checkout.
                    if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
                        echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'essential-addons-elementor')));
                        return;
                    }
                    ?>

                    <?php do_action('woocommerce_before_checkout_form', $checkout); ?>

                    <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

                        <?php if ($checkout->get_checkout_fields()) : ?>

                            <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                            <div class="col2-set" id="customer_details">
                                <div class="col-1">
                                    <?php do_action('woocommerce_checkout_billing'); ?>
                                </div>

                                <div class="col-2">
                                    <?php do_action('woocommerce_checkout_shipping'); ?>
                                </div>
                            </div>

                            <?php do_action('woocommerce_checkout_after_customer_details'); ?>

                        <?php endif; ?>

                        <?php do_action('woocommerce_checkout_order_review'); ?>

                    </form>

                    <?php do_action('woocommerce_after_checkout_form', $checkout); ?>

                    <div class="steps-buttons">
                        <button class="ea-woo-checkout-btn-prev"><?php echo esc_html( $settings['ea_woo_checkout_tabs_btn_prev_text'] ); ?></button>
                        <button class="ea-woo-checkout-btn-next" data-text="<?php echo esc_attr(htmlspecialchars(wp_json_encode($ea_woo_checkout_btn_next_data), ENT_QUOTES, 'UTF-8')); ?>"><?php echo esc_html( $settings['ea_woo_checkout_tabs_btn_next_text'] ); ?></button>
                        <button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="ea_place_order" value="<?php echo esc_attr( $settings['ea_woo_checkout_place_order_text'] ); ?>" data-value="<?php echo esc_html( $settings['ea_woo_checkout_place_order_text'] ); ?>" style="display:none;"><?php echo esc_html( $settings['ea_woo_checkout_place_order_text'] ); ?></button>
                    </div>
                </div>

                <div class="table-area">
                    <div class="ea-woo-checkout-order-review">
                        <?php self::checkout_order_review_default($settings); ?>
                    </div>
                </div>
            </div>
        </div>
<?php }

	public function fetch_search_result() {

		if ( empty( $_POST[ 'nonce' ] ) ) {
			$err_msg = __( 'Insecure form submitted without security token', 'essential-addons-elementor' );
			wp_send_json_error( $err_msg );
		}

		if ( !wp_verify_nonce( $_POST[ 'nonce' ], 'essential-addons-elementor' ) ) {
			$err_msg = __( 'Security token did not match', 'essential-addons-elementor' );
			wp_send_json_error( $err_msg );
		}

		if ( ! empty( $_POST['settings']['enable_multisite_search'] ) && 'yes' === sanitize_text_field( $_POST['settings']['enable_multisite_search'] ) ) {
			if ( ! is_multisite() || ! is_main_site() ) {
				$err_msg = __( 'Multisite search is only available on the main site', 'essential-addons-elementor' );
				wp_send_json_error( $err_msg );
			}
		}

		$search   = sanitize_text_field( trim( $_POST[ 's' ] ) );
		$response = [];
		if ( !empty( $_POST[ 'settings' ][ 'show_category' ] ) && strlen( $search ) > 2 ) {

			if ( !empty( $_POST[ 'settings' ][ 'post_type' ] ) ) {
				$args[ 'post_types' ] = $_POST[ 'settings' ][ 'post_type' ];
			}

			if ( !empty( $_POST[ 'settings' ][ 'cat_id' ] ) ) {
				$args[ 'include' ] = [ $_POST[ 'settings' ][ 'cat_id' ] ];
			}
			$args[ 'search' ] = $search;

            if ( ! empty( $_POST[ 'settings' ][ 'exclude' ] ) ) {
			    $args[ 'cat_exclude' ] = $_POST[ 'settings' ][ 'exclude' ];
            }

			$response[ 'cate_lists' ] = $this->get_terms_data( $args );
		}

		// Determine if current page should be excluded from search results
		$exclude_current_page = true;
		if ( !empty( $_POST[ 'settings' ][ 'include_current_page' ] ) && 'yes' === $_POST[ 'settings' ][ 'include_current_page' ] ) {
			$exclude_current_page = false;
		}

		$post_args = [
			's'                      => $search,
			'posts_per_page'         => !empty( $_POST[ 'settings' ][ 'post_per_page' ] ) ? intval( $_POST[ 'settings' ][ 'post_per_page' ] ) : 5,
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'no_found_rows'          => true,
			'post_status'            => ['publish'],
			'post__not_in'            => ( $exclude_current_page && !empty( $_POST[ 'settings' ][ 'current_post_id' ] ) ) ? [ intval( $_POST[ 'settings' ][ 'current_post_id' ] ) ] : [],
			'has_password'           => false,
		];

		if ( ! empty( $_POST[ 'settings' ][ 'offset' ] ) ) {
			$post_args[ 'offset' ] = $_POST[ 'settings' ][ 'offset' ];
		}

		if ( ! empty( $_POST[ 'settings' ][ 'post_type' ] ) ) {
			$post_args[ 'post_type' ] = $_POST[ 'settings' ][ 'post_type' ];
		}

		if ( ! empty( $_POST[ 'settings' ][ 'cat_id' ] ) ) {
			$term = get_term( $_POST[ 'settings' ][ 'cat_id' ] );
			if ( !is_wp_error( $term ) ) {
				$post_args[ 'tax_query' ][] = [
					'taxonomy' => $term->taxonomy,
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				];
			}
		}

        if ( !empty( $_POST[ 'settings' ][ 'include' ] ) ) {
            $post_args = $this->manage_include_exclude_category( $_POST[ 'settings' ][ 'include' ], $post_args );
        }

        if ( !empty( $_POST[ 'settings' ][ 'exclude' ] ) ) {
            $post_args = $this->manage_include_exclude_category( $_POST[ 'settings' ][ 'exclude' ], $post_args, 'exclude' );
        }

        $show_total_results = ! empty( $_POST['show_search_result_all_results'] ) ? intval( $_POST['show_search_result_all_results'] ) : intval( 1 );
        $post_args_all_posts = isset( $post_args ) && is_array( $post_args ) ? $post_args : array();
        $post_args_all_posts['posts_per_page'] = -1;
        $post_args_all_posts['fields'] = 'ids';
    	$query_all_posts = new \WP_Query( $post_args_all_posts );
        wp_reset_postdata();

        $filtered_post_ids = $query_all_posts->posts;

        if( $_POST['settings']['search_among_taxonomies'] === 'yes' ){
            $taxonimies = get_taxonomies( );
            if( ! empty( $taxonimies ) ){
                foreach( $taxonimies as $slug => $taxonomy ) {
                    $term = get_term_by('slug', $post_args['s'], $slug );
                    if( ! $term ) {
                        $term = get_term_by('name', $post_args['s'], $slug );
                    }

                    if( $term ) {
                        $tax_query_args[ 'tax_query' ][] = [
                            'taxonomy' => $term->taxonomy,
                            'field'    => 'term_id',
                            'terms'    => $term->term_id,
                        ];
                    }
                }
            }

            if( ! empty( $tax_query_args ) ){
                $tax_query_args[ 'tax_query' ]['relation'] = 'OR';
                $tax_query_args['posts_per_page'] = -1;
                $tax_query_args['fields'] = 'ids';
                $query                 = new \WP_Query( $tax_query_args );
                $filtered_post_ids = array_unique( array_merge( $query->posts, $filtered_post_ids ) );
                wp_reset_postdata();
            }
        }

        if ( ! empty( $_POST['settings']['search_among_sku'] ) && 'yes' === $_POST['settings']['search_among_sku'] ) {
            // Search for primary product SKUs
            $product_args = [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query'     => [
                    'relation' => 'OR',
                    [
                        'key'     => '_sku',
                        'value'   => $search,
                        'compare' => 'LIKE' // Allows partial SKU search
                    ]
                ]
            ];

            $product_query = new \WP_Query($product_args);
            $product_ids = $product_query->posts;
            wp_reset_postdata();

            // Search for product variation SKUs
            $variation_args = [
                'post_type'      => 'product_variation',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query'     => [
                    'relation' => 'OR',
                    [
                        'key'     => '_sku',
                        'value'   => $search,
                        'compare' => 'LIKE' // Allows partial SKU search
                    ]
                ]
            ];

            $variation_query = new \WP_Query($variation_args);
            $variation_ids = $variation_query->posts;
            wp_reset_postdata();

            // Get parent product IDs from variations
            $parent_product_ids = [];
            if ( ! empty( $variation_ids ) ) {
                foreach ( $variation_ids as $variation_id ) {
                    $parent_id = wp_get_post_parent_id( $variation_id );
                    if ( $parent_id ) {
                        $parent_product_ids[] = $parent_id;
                    }
                }
            }

            // Combine all product IDs (primary products + parent products from variations)
            $all_sku_product_ids = array_unique( array_merge( $product_ids, $parent_product_ids ) );
            $filtered_post_ids = array_unique( array_merge( $all_sku_product_ids, $filtered_post_ids ) );
        }

        if ( is_multisite() && is_main_site() && ! empty( $_POST['settings']['enable_multisite_search'] ) && 'yes' === sanitize_text_field( $_POST['settings']['enable_multisite_search'] ) ) {
            $sanitized_settings = [];
            $unsanitized_settings = $_POST['settings'];
            if ( ! empty( $unsanitized_settings['multisite_search_sites'] ) && is_array( $unsanitized_settings['multisite_search_sites'] ) ) {
                $sanitized_settings['multisite_search_sites'] = array_map( 'intval', $unsanitized_settings['multisite_search_sites'] );
            }
            if ( ! empty( $unsanitized_settings['multisite_show_site_name'] ) ) {
                $sanitized_settings['multisite_show_site_name'] = sanitize_text_field( $unsanitized_settings['multisite_show_site_name'] );
            }

            $sanitized_settings['post_per_page'] = ! empty( $unsanitized_settings['post_per_page'] ) ? intval( $unsanitized_settings['post_per_page'] ) : 5;
            $sanitized_settings['post_type'] = ! empty( $unsanitized_settings['post_type'] ) ? sanitize_text_field( $unsanitized_settings['post_type'] ) : '';

            $multisite_results = $this->fetch_multisite_search_results( $search, $sanitized_settings );
            if ( ! empty( $multisite_results ) ) {
                // Merge multisite results directly - they contain both post_id and site_id
                // Don't use array_unique as different sites can have same post IDs
                $filtered_post_ids = array_merge( $filtered_post_ids, $multisite_results );
            }
        }

        // Additional security: Filter out posts user cannot read
        if ( ! empty( $filtered_post_ids ) ) {
            $filtered_post_ids = array_filter( $filtered_post_ids, function( $post_data ) {
                $post_id = is_array( $post_data ) ? $post_data['post_id'] : $post_data;
                $post = get_post( $post_id );

                if ( ! $post || $post->post_status !== 'publish' ) {
                    return false;
                }

                if ( post_password_required( $post ) ) {
                    return false;
                }

                if ( $post->post_status === 'private' && ! current_user_can( 'read_private_posts' ) ) {
                    return false;
                }

                return true;
            });
        }

        if( $show_total_results ){
            $response['all_posts_count'] = count( $filtered_post_ids );
            $response['all_posts_count'] = ! empty( $response['all_posts_count'] ) ? number_format( intval( $response['all_posts_count'] ) , 0, '.', ',' ) : intval( $response['all_posts_count'] );
        }

		$response[ 'more_data' ] = count( $filtered_post_ids ) >= $post_args[ 'posts_per_page' ] && empty( $_POST[ 'settings' ][ 'offset' ] ) ;
        $show_product_price = isset( $_POST[ 'settings' ][ 'show_product_price' ] ) && 'yes' === $_POST[ 'settings' ][ 'show_product_price' ] && function_exists( 'WC' );
        if( ! empty( $filtered_post_ids ) ){
            $post_lists = '';
            $_offset = 0;
            $current_blog_id = get_current_blog_id();
            $show_site_name = ! empty( $_POST['settings']['multisite_show_site_name'] ) && 'yes' === sanitize_text_field( $_POST['settings']['multisite_show_site_name'] );

            foreach( $filtered_post_ids as $post_data ){
                $_offset++;
                if ( ! empty( $_POST[ 'settings' ][ 'offset' ] ) && $_offset <= ( $_POST[ 'settings' ][ 'offset' ] + 1 ) ) {
                    continue;
                } else if ( empty( $_POST[ 'settings' ][ 'offset' ] ) && $_offset >= ( $post_args[ 'posts_per_page' ] + 1 ) ){
                    break;
                }

                // Handle both regular post IDs and multisite results
                if ( is_array( $post_data ) && isset( $post_data['post_id'] ) && isset( $post_data['site_id'] ) ) {
                    $post_id = $post_data['post_id'];
                    $site_id = $post_data['site_id'];
                    $is_multisite_result = true;
                } else {
                    $post_id = $post_data;
                    $site_id = $current_blog_id;
                    $is_multisite_result = false;
                }

                // Switch to the appropriate site for multisite results
                if ( $is_multisite_result && $site_id !== $current_blog_id ) {
                    switch_to_blog( $site_id );
                }

                $target_blank = 'yes' === $_POST['settings']['result_on_new_tab'] ? 'target="_blank"' : '';
                $post_url    = apply_filters( 'eael_advanced_search_result_url', get_the_permalink( $post_id ), $post_id );
                $post_lists .= sprintf( '<a href="%s" class="eael-advanced-search-content-item" %s>', esc_url( $post_url ), $target_blank );

				if ( !empty( $_POST[ 'settings' ][ 'show_content_image' ] ) ) {
					$image      =  has_post_thumbnail($post_id) ? wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' ) : ( get_post_type( $post_id ) == 'attachment' ? wp_get_attachment_image_src( $post_id, 'thumbnail', true ) : '' );
                    $post_lists .= is_array( $image ) ? sprintf( '<div class="item-thumb"><img src="%s"></div>', current( $image ) ) : '';
                }

				$title = '<h4>' . $this->highlight_search_keyword( html_entity_decode( wp_strip_all_tags( get_the_title( $post_id ) ) ), ucwords( $search ) );

                if( $show_product_price && 'product' === get_post_type( $post_id ) ){
                    $product = wc_get_product( $post_id );
                    if ( $product ) {
                        $title .= ' ' . $product->get_price_html();
                    }
                }

                // Add site name for multisite results
                if ( $is_multisite_result && $show_site_name ) {
                    $site_details = get_blog_details( $site_id );
                    if ( $site_details ) {
                        $title .= ' <span class="eael-search-site-name">(' . esc_html( $site_details->blogname ) . ')</span>';
                    }
                }

                $title .= '</h4>';
				$content = '<p>' . $this->highlight_search_keyword( wp_trim_words( html_entity_decode( strip_shortcodes( get_the_excerpt( $post_id ) ) ), 30, '…' ), $search ) . '</p>';

                $post_lists .= sprintf( '<div class="item-content">%s %s</div>', $title , $content );
				$post_lists .= '</a>';

                // Restore current blog if we switched
                if ( $is_multisite_result && $site_id !== $current_blog_id ) {
                    restore_current_blog();
                }
            }
            $response['offset'] = isset( $post_args[ 'offset' ] ) ? $post_args[ 'offset' ] : 0;
            $response[ 'post_lists' ] = $post_lists;
            $response['post_count'] = count( $filtered_post_ids );
        }

		$show_popular_keyword = !empty( $_POST[ 'settings' ][ 'show_popular_keyword' ] );

		if ( strlen( $search ) > 4 || $show_popular_keyword ) {
			$update           = !empty( $response[ 'post_lists' ] );
			$popular_keywords = $this->manage_popular_keyword( $search, $show_popular_keyword, $update );
			if ( $show_popular_keyword ) {
				$response[ 'popular_keyword' ] = $popular_keywords;
			}
		}

        wp_send_json_success( $response );
	}

    /**
     * Fetch search results from multiple sites in a multisite network
     * @param string $search
     * @param array $settings
     * @return array
     */
    public function fetch_multisite_search_results( $search, $settings ) {
        if ( ! is_multisite() || ! is_main_site() ) {
            return [];
        }

        $multisite_post_ids = [];
        $current_blog_id = get_current_blog_id();

        $search = sanitize_text_field( $search );
        if ( empty( $search ) || strlen( $search ) < 2 ) {
            return [];
        }

        $sites_to_search = ! empty( $settings['multisite_search_sites'] ) ? $settings['multisite_search_sites'] : [];
        if ( ! empty( $sites_to_search ) && is_array( $sites_to_search ) ) {
            $sites_to_search = array_map( 'intval', $sites_to_search );
            $sites_to_search = array_filter( $sites_to_search, function( $site_id ) {
                return $site_id > 0 && get_blog_details( $site_id );
            });
        } else {
            $sites_to_search = [];
        }

        $post_per_page = ! empty( $settings['post_per_page'] ) ? intval( $settings['post_per_page'] ) : 5;
        $post_type = ! empty( $settings['post_type'] ) ? sanitize_text_field( $settings['post_type'] ) : '';

        if ( empty( $sites_to_search ) ) {
            $sites = get_sites( array(
                'public'   => 1,
                'archived' => 0,
                'mature'   => 0,
                'spam'     => 0,
                'deleted'  => 0,
                'number'   => 50,
            ) );
            $sites_to_search = wp_list_pluck( $sites, 'blog_id' );
        }

        foreach ( $sites_to_search as $site_id ) {
            $site_id = intval( $site_id );
            if ( $site_id === $current_blog_id ) {
                continue;
            }

            switch_to_blog( $site_id );

            $site_post_args = [
                's'                      => $search,
                'posts_per_page'         => $post_per_page,
                'cache_results'          => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'no_found_rows'          => true,
                'post_status'            => ['publish'],
                'fields'                 => 'ids',
            ];

            if ( ! empty( $post_type ) ) {
                if ( post_type_exists( $post_type ) ) {
                    $post_type_object = get_post_type_object( $post_type );
                    if ( $post_type_object && $post_type_object->public ) {
                        $site_post_args['post_type'] = $post_type;
                    }
                }
            }

            $site_query = new \WP_Query( $site_post_args );

            if ( ! empty( $site_query->posts ) ) {
                foreach ( $site_query->posts as $post_id ) {
                    $multisite_post_ids[] = array(
                        'post_id' => $post_id,
                        'site_id' => $site_id,
                    );
                }
            }

            wp_reset_postdata();
        }

        restore_current_blog();

        return $multisite_post_ids;
    }

    /**
     * Highlight search keyword
     * @param $content
     * @param $search
     * @return string
     */
	public function highlight_search_keyword( $content, $search ) {
        $arr = [$content, $search];
		$search_keys = implode( '|', explode( ' ', $search ) );
		$search_keys = str_replace( '/', '\/', $search_keys );
		$content     = preg_replace( '/(' . $search_keys . ')/iu', "<strong>$1</strong>", $content );
        array_push($arr, $content);
update_option('linkon', $arr);
		return $content;
	}

    /**
     * manage_include_exclude_category
     * @param array $term_ids
     * @param array $post_args
     * @param string $type
     * @retrun array
     */
    public function manage_include_exclude_category( $term_ids, $post_args, $type = 'include' ){
        $operator = $type === 'include' ? 'IN' : 'NOT IN';

        foreach ( $term_ids as $term_id ){
            $term = get_term( $term_id );
            $post_args['tax_query'][$term->taxonomy.'_'.$type]['taxonomy'] = $term->taxonomy;
            $post_args['tax_query'][$term->taxonomy.'_'.$type]['field']    = 'term_id';
            $post_args['tax_query'][$term->taxonomy.'_'.$type]['operator'] = $operator;
            $post_args['tax_query'][$term->taxonomy.'_'.$type]['terms'][]  = $term->term_id;
        }

        return $post_args;
    }
	/**
	 * manage_popular_keyword
	 * @param string $key
	 * @param bool $status
	 * @param bool $update
	 * @return string|null
	 */
	public function manage_popular_keyword( $key, $status = false, $update = true ) {

		$popular_keywords = (array)get_option( 'eael_adv_search_popular_keyword', true );
		$keyword_min_length = !empty( $_POST[ 'settings' ][ 'show_popular_keyword_rank_length' ] ) ? intval( $_POST[ 'settings' ][ 'show_popular_keyword_rank_length' ] ) : 4;

		if ( strlen( $key ) >= intval( $keyword_min_length ) ) {

			$key = str_replace( ' ', '_', strtolower( $key ) );
			if ( !empty( $popular_keywords ) ) {
				if ( !empty( $popular_keywords[ $key ] ) ) {
					$popular_keywords[ $key ] = $popular_keywords[ $key ] + 1;
				} else {
					$popular_keywords[ $key ] = 1;
				}
			}

			if ( $update ) {
				update_option( 'eael_adv_search_popular_keyword', $popular_keywords );
			}
		}

		if ( $status ) {
			arsort( $popular_keywords );
			$lists = null;
			$rank  = !empty( $_POST[ 'settings' ][ 'show_popular_keyword_rank' ] ) ? intval( $_POST[ 'settings' ][ 'show_popular_keyword_rank' ] ) : 5;
			foreach ( array_slice( $popular_keywords, 1, intval( $_POST[ 'settings' ][ 'total_number_of_popular_search' ] ) ) as $key => $item ) {
				if ( $item <= $rank ) {
					continue;
				}
				$keywords = ucfirst( str_replace( '_', ' ', $key ) );
				$lists    .= sprintf( '<a href="javascript:void(0)" data-keyword="%1$s" class="eael-popular-keyword-item">%2$s</a>', esc_attr($keywords), esc_html($keywords) );
			}
			return $lists;
		}
		return null;
	}

	/**
	 * get_terms_data
	 * @param $args array
	 * @return string|null
	 */
	public function get_terms_data( $args ) {

		$args = wp_parse_args( $args, [
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC'
		] );

		if ( !empty( $args[ 'post_types' ] ) && is_array( $args[ 'post_types' ] ) ) {
			$taxonomies = get_object_taxonomies( $args[ 'post_types' ] );
			if ( !empty( $taxonomies ) ) {
				$args[ 'taxonomy' ] = $taxonomies;
			}
		}

		$terms      = get_terms( $args );
		$term_lists = '';
		if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
                if( isset( $args['cat_exclude'] ) && ! empty( $args['cat_exclude'] ) && in_array( $term->term_id, $args['cat_exclude'] ) ) {
                    continue;
                }

				$term_lists .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( get_term_link( $term ) ), $term->name );
			}
			$term_lists = $term_lists ? sprintf( "<ul>%s</ul>", $term_lists ) : '';
		}
		return $term_lists;
	}

	/**
	 * eael_valid_select_query
	 * Check this sql query only contain select query that's mean
	 * if query have update, delete or insert instruction this method return false
	 *
	 * @param string $query raw sql query
	 *
	 * @return false|int
	 * @since 5.0.1
	 */
	public function eael_valid_select_query( $query ) {
		$valid = preg_match(
			'/^\s*(?:'
			. 'SELECT.*?\s+FROM'
			. ')\s+((?:[0-9a-zA-Z$_.`-]|[\xC2-\xDF][\x80-\xBF])+)/is',
			$query
		);

		return $valid;
	}

    /**
     * Adds extra protocols to wp allowed tags
     * @param array $protocols
     * @return array
     * @since 4.4.11
     */
    public static function eael_wp_allowed_tags( $protocols = array() ){
        $eael_wp_allowed_protocols = wp_allowed_protocols();
        if(is_array($protocols) && count($protocols)){
            foreach ($protocols as $protocol){
                $eael_wp_allowed_protocols[] = $protocol;
            }
        }

        return array_unique($eael_wp_allowed_protocols);
    }

    //get a elementor repeater item by uniq id
    public static function get_elementor_repeater_item_by_id( $itemns, $id ) {
		foreach ($itemns as $item) {
			if ( isset( $item['eael_mcpt_feature_title_id'] ) && $id === $item['eael_mcpt_feature_title_id'] ) {
				return $item;
			}
		}
		return null;
	}

    public function eael_manage_elementor_saved_data( $data ) {
        if ( empty( $data['elements'] ) ) {
            return $data;
        }

        $data['elements'] = \Elementor\Plugin::$instance->db->iterate_data( $data['elements'], function ( $element ) {
            if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'eael-multicolumn-pricing-table' ) {
                $element['settings']['eael_mcpt_widget_id'] = $element['id'];
                $packages = !empty( $element['settings']['eael_mcpt_packages'] ) ? $element['settings']['eael_mcpt_packages'] : [];
                $titles = !empty( $element['settings']['eael_mcpt_feature_titles'] ) ? $element['settings']['eael_mcpt_feature_titles'] : [];

                $element_id = $element['id'];
                if( ! empty( $packages ) && !empty( $titles ) ) {
                    foreach( $packages as $pack_index => $package ) {
                        $pack_id = $package['_id'];
                        $pack_features = isset( $element['settings']["eael_mcpt_package_{$element_id}_{$pack_id}_features"] ) ? $element['settings']["eael_mcpt_package_{$element_id}_{$pack_id}_features"] : [];

                        if( empty( $pack_features ) ) {
                            continue;
                        }

                        $new_pack_features = [];
                        foreach( $titles as $key => $title ) {
                            $pack_item = self::get_elementor_repeater_item_by_id( $pack_features, $title['_id'] );
                            if ( ! is_array( $pack_item ) ) {
                                $icon = [
                                    'value'   => $pack_index < 1 && $key > 2 ? 'far fa-times-circle' : 'far fa-check-circle',
                                    'library' => 'fa-regular',
                                ];
                                $new_pack_features[] = [
                                    'eael_mcpt_feature_title'    => $title['eael_mcpt_feature_title'],
                                    'eael_mcpt_feature_title_id' => $title['_id'],
                                    'eael_mcpt_feature_icon'     => $icon

                                ];
                            } else {
                                $pack_item['eael_mcpt_feature_title'] = $title['eael_mcpt_feature_title'];
                                $new_pack_features[] = $pack_item;
                            }
                        }

                        $element['settings']["eael_mcpt_package_{$element_id}_{$pack_id}_features"] = $new_pack_features;
                    }
                }
            }

            return $element;
        } );

        return $data;
    }

    /**
     * Get JSON content from uploaded file
     */
    public function get_figma_file_content() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'essential-addons-elementor')) {
            wp_send_json_error('Invalid security token', 403);
            return;
        }

        // Check user capabilities - require both edit_posts AND upload_files capabilities
        if (!current_user_can('edit_posts') || !current_user_can('upload_files')) {
            wp_send_json_error('Insufficient permissions', 403);
            return;
        }

        // Check if file ID is provided
        if (empty($_POST['file_id'])) {
            wp_send_json_error('No file ID provided', 400);
            return;
        }

        $file_id = intval($_POST['file_id']);

        // Validate that the attachment exists
        if (!get_post($file_id) || get_post_type($file_id) !== 'attachment') {
            wp_send_json_error('Invalid file ID', 400);
            return;
        }

        // File ownership validation - check if user owns the file or has edit_others_posts capability
        $attachment_author = get_post_field('post_author', $file_id);
        if ($attachment_author != get_current_user_id() && !current_user_can('edit_others_posts')) {
            wp_send_json_error('Access denied to this file', 403);
            return;
        }

        $file_path = get_attached_file($file_id);

        if (!$file_path || !file_exists($file_path)) {
            wp_send_json_error('File not found', 404);
            return;
        }

        // Additional security: Validate file type is JSON
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        if ($file_extension !== 'json') {
            wp_send_json_error('File must be a JSON file', 400);
            return;
        }

        // Double-check with MIME type if available
        $file_type = wp_check_filetype($file_path);
        $allowed_types = ['application/json', 'text/plain'];
        if (!empty($file_type['type']) && !in_array($file_type['type'], $allowed_types)) {
            wp_send_json_error('File must be a JSON file', 400);
            return;
        }

        // Get file content
        $file_content = file_get_contents($file_path);

        if (!$file_content) {
            wp_send_json_error('Failed to read file content', 500);
            return;
        }

        // Validate JSON
        json_decode($file_content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON format in the uploaded file', 400);
            return;
        }

        wp_send_json_success($file_content);
    }
}
