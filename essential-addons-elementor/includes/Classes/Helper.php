<?php

namespace Essential_Addons_Elementor\Pro\Classes;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Essential_Addons_Elementor\Classes\Helper as ClassesHelper;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class Helper extends \Essential_Addons_Elementor\Classes\Helper
{
	use \Essential_Addons_Elementor\Pro\Traits\Dynamic_Filterable_Gallery;

	const EAEL_PRO_ALLOWED_HTML_TAGS = [
		'article',
		'aside',
		'div',
		'footer',
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'header',
		'main',
		'nav',
		'p',
		'section',
		'span',
	];
    /**
     * Get all product tags
     *
     * @return array
     */
    public static function get_woo_product_tags()
    {
        if (!apply_filters('eael/is_plugin_active', 'woocommerce/woocommerce.php')) {
            return [];
        }

        $options = [];
        $tags = get_terms('product_tag', array('hide_empty' => true));
        if (!empty($tags) && !is_wp_error($tags)) {
            foreach ($tags as $tag) {
                $options[$tag->term_id] = $tag->name;
            }
        }
        return $options;
    }

    /**
     * Get all product attributes
     *
     * @return array
     */
    public static function get_woo_product_atts()
    {
	    if ( ! apply_filters( 'eael/is_plugin_active', 'woocommerce/woocommerce.php' ) || ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		    return [];
	    }

        $options = [];
        $taxonomies = wc_get_attribute_taxonomies();

        foreach ($taxonomies as $tax) {
            $terms = get_terms('pa_' . $tax->attribute_name);

            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $options[$term->term_id] = $tax->attribute_label . ': ' . $term->name;
                }
            }
        }

        return $options;
    }

    /**
     * Get all registered menus.
     *
     * @return array of menus.
     */
    public static function get_menus()
    {
        $menus = wp_get_nav_menus();
        $options = [];

        if (empty($menus)) {
            return $options;
        }

        foreach ($menus as $menu) {
            $options[$menu->term_id] = $menu->name;
        }

        return $options;
    }

    public static function user_roles()
    {
        global $wp_roles;

        $all = $wp_roles->roles;
        $all_roles = array();

        if (!empty($all)) {
            foreach ($all as $key => $value) {
                $all_roles[$key] = $all[$key]['name'];
            }
        }

        return $all_roles;
    }

    public static function get_page_template_options($type = '')
    {
        $page_templates = self::get_elementor_templates($type);

        $options[-1] = __('Select', 'essential-addons-elementor');

        if (count($page_templates)) {
            foreach ($page_templates as $id => $name) {
                $options[$id] = $name;
            }
        } else {
            $options['no_template'] = __('No saved templates found!', 'essential-addons-elementor');
        }

        return $options;
    }

    // Get all WordPress registered widgets
    public static function get_registered_sidebars()
    {
        global $wp_registered_sidebars;
        $options = [];

        if (!$wp_registered_sidebars) {
            $options[''] = __('No sidebars were found', 'essential-addons-elementor');
        } else {
            $options['---'] = __('Choose Sidebar', 'essential-addons-elementor');

            foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
                $options[$sidebar_id] = $sidebar['name'];
            }
        }
        return $options;
    }

    // Get Mailchimp list
	public static function mailchimp_lists( $element = 'mailchimp', $type_double_optin = false ) {
		$lists   = $lists_double_optin = [];
		$api_key = get_option( 'eael_save_mailchimp_api' );

		if ( $element === 'login-register-form' ) {
			$api_key = get_option( 'eael_lr_mailchimp_api_key' );
		}

		if ( empty( $api_key ) ) {
			return $lists;
		}

        $pattern = '/^[0-9a-z]{32}(-us)(0?[1-9]|[1-9][0-9])?$/';
		if ( ! preg_match( $pattern, $api_key ) ) {
			return $lists;
		}

		$response = wp_safe_remote_get( 'https://' . substr( $api_key,
				strpos( $api_key, '-' ) + 1 ) . '.api.mailchimp.com/3.0/lists/?fields=lists.id,lists.name,lists.double_optin&count=1000', [
			'headers' => [
				'Content-Type'  => 'application/json',
				'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
			],
		] );

		if ( ! is_wp_error( $response ) ) {
			$response = json_decode( wp_remote_retrieve_body( $response ) );

			if ( ! empty( $response ) && ! empty( $response->lists ) ) {
				$lists[''] = __( 'Select One', 'essential-addons-elementor' );

				for ( $i = 0; $i < count( $response->lists ); $i ++ ) {
					$lists[ $response->lists[ $i ]->id ]              = $response->lists[ $i ]->name;
					$lists[ $response->lists[ $i ]->id ]              = $response->lists[ $i ]->name;
					$lists_double_optin[ $response->lists[ $i ]->id ] = $response->lists[ $i ]->double_optin;
				}
			}
		}

		return $type_double_optin ? $lists_double_optin : $lists;
	}

    public static function list_db_tables()
    {
        global $wpdb;

        $result = [];
        $tables = $wpdb->get_results('show tables', ARRAY_N);

        if ($tables) {
            $tables = wp_list_pluck($tables, 0);

            foreach ($tables as $table) {
                $result[$table] = $table;
            }
        }

        return $result;
    }

	public static function list_tablepress_tables() {
		if ( empty( \TablePress::$model_table ) ) {
			return [];
		}

		$result = [];
		$tables = \TablePress::$model_table->load_all( true );

		if ( $tables ) {
			foreach ( $tables as $table ) {
				$table                  = \TablePress::$model_table->load( $table, false, false );
				$result[ $table['id'] ] = $table['name'];
			}
		}

		return $result;
	}

	/**
	 * eael_pro_validate_html_tag
	 * @param $tag
	 * @return mixed|string
	 */
	public static function eael_pro_validate_html_tag( $tag ){
        if ( empty( $tag ) ) {
            return 'div';
        }

		return in_array( strtolower( $tag ), self::EAEL_PRO_ALLOWED_HTML_TAGS ) ? $tag : 'div';
	}

    /**
     * Get all dropdown options of elementor breakpoints.
     *
     * @return array of breakpoint options.
     */
    public static function get_breakpoint_dropdown_options(){
        $breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

        $dropdown_options = [];
        $excluded_breakpoints = [
            'laptop',
            'widescreen',
        ];

        foreach ( $breakpoints as $breakpoint_key => $breakpoint_instance ) {
            // Do not include laptop and widscreen in the options since this feature is for mobile devices.
            if ( in_array( $breakpoint_key, $excluded_breakpoints, true ) ) {
                continue;
            }

            $dropdown_options[ $breakpoint_key ] = sprintf(
                /* translators: 1: Breakpoint label, 2: `>` character, 3: Breakpoint value */
                esc_html__( '%1$s (%2$s %3$dpx)', 'essential-addons-elementor' ),
                $breakpoint_instance->get_label(),
                '>',
                $breakpoint_instance->get_value()
            );
        }

        $dropdown_options['desktop']    = esc_html__( 'Desktop (> 2400px)', 'essential-addons-elementor' );
        $dropdown_options['none']       = esc_html__( 'None', 'essential-addons-elementor' );

        return $dropdown_options;
    }

    /**
     * Get all active Elementor breakpoints with their values for responsive handling.
     * Includes all breakpoints including laptop and widescreen.
     *
     * @return array of breakpoint data with keys and values.
     */
    public static function get_all_active_breakpoints(){
        $breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();
        $breakpoint_data = [];

        foreach ( $breakpoints as $breakpoint_key => $breakpoint_instance ) {
            $breakpoint_data[ $breakpoint_key ] = [
                'label' => $breakpoint_instance->get_label(),
                'value' => $breakpoint_instance->get_value(),
                'direction' => $breakpoint_instance->get_direction(), // 'max' or 'min'
            ];
        }

        // Add desktop as it's not in active breakpoints but is the default
        $breakpoint_data['desktop'] = [
            'label' => esc_html__( 'Desktop', 'essential-addons-elementor' ),
            'value' => Plugin::$instance->breakpoints->get_desktop_min_point(),
            'direction' => 'min',
        ];

        return $breakpoint_data;
    }

    public static function validate_post_types( $post_types )
    {
        $allowed_post_types = self::get_post_types();
        $validated_post_types = '';

        if ( is_string( $post_types ) && array_key_exists( $post_types, $allowed_post_types ) ) {
            $validated_post_types = $post_types;
        } else {
            if ( is_array( $post_types ) ) {
                $validated_post_types = array_filter( $post_types, function ( $post_type ) use( $allowed_post_types ) {
                    return array_key_exists( $post_type, $allowed_post_types );
                });
            }
        }

        return $validated_post_types;
    }

    public static function get_allowed_taxonomies()
    {
        $taxonomies = get_taxonomies(['public' => true]);
        $taxonomy_array = [];

        foreach ($taxonomies as $taxonomy) {
            $taxonomy_object = get_taxonomy($taxonomy);
            $taxonomy_array[$taxonomy] = sanitize_text_field($taxonomy_object->labels->name);
        }
        return $taxonomy_array;
    }

    /**
     * Query Controls for Dynamic Tags
     *
     */
    public static function query_dynamic_tags( $wb, $args )
    {
        $post_types = ClassesHelper::get_post_types();
        $post_types = $args['post_types'] ?? $post_types;
        $hide_controls = ! empty( $args['hide_controls'] ) ? $args['hide_controls'] : [];
        
        if( ! isset( $args['post_types'] ) ) {
            $post_types['by_id'] = __('Manual Selection', 'essential-addons-elementor');
        }

        $taxonomies = get_taxonomies([], 'objects');

        $wb->add_control(
            'post_type',
            [
                'label' => __('Source', 'essential-addons-elementor'),
                'type' => in_array( 'post_type', $hide_controls ) ? Controls_Manager::HIDDEN : Controls_Manager::SELECT,
                'options' => $post_types,
                'default' => key($post_types),
            ]
        );

        $wb->add_control(
            'posts_ids',
            [
                'label' => __('Search & Select', 'essential-addons-elementor'),
                'type' => 'eael-select2',
                'options' => ClassesHelper::get_post_list(),
                'label_block' => true,
                'multiple'    => true,
                'source_name' => 'post_type',
                'source_type' => 'any',
                'condition' => [
                    'post_type' => 'by_id',
                ],
            ]
        );

        $wb->add_control(
            'posts_by_current_user',
            [
                'label' => __('Posts by Current User', 'essential-addons-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'essential-addons-elementor'),
                'label_off' => __('No', 'essential-addons-elementor'),
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'post_type!' => ['by_id', 'source_dynamic'],
                ],
            ]
        );

        $wb->add_control(
            'authors', [
                'label' => __('Author', 'essential-addons-elementor'),
                'label_block' => true,
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'default' => [],
                'options' => ClassesHelper::get_authors_list(),
                'condition' => [
                    'post_type!' => ['by_id', 'source_dynamic'],
                    'posts_by_current_user!' => 'yes',
                ],
            ]
        );

        foreach ($taxonomies as $taxonomy => $object) {
            if (!isset($object->object_type[0]) || !in_array($object->object_type[0], array_keys($post_types))) {
                continue;
            }
            
            if ( 'post_format' === $taxonomy || 'product_shipping_class' === $taxonomy || 'product_visibility' === $taxonomy ) {
                continue;
            }

            $wb->add_control(
                $taxonomy . '_ids',
                [
                    'label' => $object->label,
                    'type' => in_array( $taxonomy . '_ids', $hide_controls ) ? Controls_Manager::HIDDEN : 'eael-select2',
                    'label_block' => true,
                    'multiple' => true,
                    'source_name' => 'taxonomy',
                    'source_type' => $taxonomy,
                    'condition' => [
                        'post_type' => $object->object_type,
                    ],
                ]
            );
        }

	    $wb->add_control(
		    'post__not_in',
		    [
			    'label'       => __( 'Exclude', 'essential-addons-elementor' ),
			    'type'        => 'eael-select2',
			    'label_block' => true,
			    'multiple'    => true,
			    'source_name' => 'post_type',
			    'source_type' => 'any',
			    'condition'   => [
				    'post_type!' => [ 'by_id', 'source_dynamic' ],
			    ],
		    ]
	    );

        $wb->add_control(
            'posts_per_page',
            [
                'label' => __('Count', 'essential-addons-elementor'),
                'type' => Controls_Manager::NUMBER,
                'default' => '10',
                'min' => '1',
            ]
        );

        $wb->add_control(
            'offset',
            [
                'label' => __('Offset', 'essential-addons-elementor'),
                'type' => Controls_Manager::NUMBER,
                'default' => '0',
	            'condition' => [
	            	'orderby!' => 'rand'
	            ]
            ]
        );

        $wb->add_control(
            'orderby',
            [
                'label' => __('Order By', 'essential-addons-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => ClassesHelper::get_post_orderby_options(),
                'default' => 'date',

            ]
        );

        $wb->add_control(
            'order',
            [
                'label' => __('Order', 'essential-addons-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'asc' => 'Ascending',
                    'desc' => 'Descending',
                ],
                'default' => 'desc',

            ]
        );

    }

	/**
	 * Get formatted last modified date
	 *
	 * @param array $settings Widget settings
	 *
	 * @return string Formatted last modified date
	 */
	public static function get_last_modified_date( $settings ) {
		if ( empty( $settings['eael_post_list_show_last_modified'] ) || $settings['eael_post_list_show_last_modified'] !== 'yes' ) {
			return '';
		}

		$modified_time  = get_the_modified_time( 'U' );
		$published_time = get_the_time( 'U' );

		// Only show modified date if it's different from published date
		if ( $modified_time <= $published_time ) {
			return '';
		}

		$prefix = ! empty( $settings['eael_post_list_last_modified_prefix'] ) ? $settings['eael_post_list_last_modified_prefix'] : '';
		$format = $settings['eael_post_list_last_modified_format'];

		switch ( $format ) {
			case 'relative':
				$date_string = sprintf( __( '%s ago', 'essential-addons-elementor' ), human_time_diff( $modified_time, current_time( 'timestamp' ) ) );
				break;
			case 'custom':
				$custom_format = ! empty( $settings['eael_post_list_last_modified_custom_format'] ) ? $settings['eael_post_list_last_modified_custom_format'] : 'F j, Y';
				$date_string   = get_the_modified_date( $custom_format );
				break;
			default:
				$date_string = get_the_modified_date( get_option( 'date_format' ) );
				break;
		}

		return '<span class="eael-post-last-modified"><i class="far fa-edit"></i> ' . esc_html( $prefix . $date_string ) . '</span>';
	}

	/**
	 * Render meta dates (published and last modified) based on settings
	 *
	 * @param array $settings Widget settings
	 *
	 * @return void
	 */
	public static function render_post_meta_dates( $settings ) {
		if ( $settings['eael_post_list_post_meta'] !== 'yes' ) {
			return;
		}

		$published_date     = '<span class="eael-post-published-date"><i class="far fa-calendar-alt"></i> ' . get_the_date( get_option( 'date_format' ) ) . '</span>';
		$last_modified_date = self::get_last_modified_date( $settings );
		$position           = $settings['eael_post_list_last_modified_position'];

		echo '<div class="meta">';

		switch ( $position ) {
			case 'before_date':
				if ( $last_modified_date ) {
					echo $last_modified_date . ' ';
				}
				echo $published_date;
				break;
			case 'after_date':
				echo $published_date;
				if ( $last_modified_date ) {
					echo ' ' . $last_modified_date;
				}
				break;
			case 'separate_line':
				echo $published_date;
				if ( $last_modified_date ) {
					echo '<br>' . $last_modified_date;
				}
				break;
			case 'replace_date':
				if ( $last_modified_date ) {
					echo $last_modified_date;
				} else {
					echo $published_date;
				}
				break;
			default:
				echo $published_date;
				if ( $last_modified_date ) {
					echo ' ' . $last_modified_date;
				}
				break;
		}

		echo '</div>';
	}
}
