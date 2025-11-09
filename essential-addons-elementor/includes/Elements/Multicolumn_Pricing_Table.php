<?php
namespace Essential_Addons_Elementor\Pro\Elements;

use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Essential_Addons_Elementor\Classes\Helper;
use Elementor\Plugin;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Widget_Base;
use \Elementor\Repeater;
use Elementor\Icons_Manager;

class Multicolumn_Pricing_Table extends Widget_Base {

	private $max_packes = 15;

    public function get_name()
    {
        return 'eael-multicolumn-pricing-table';
    }

    public function get_title()
    {
        return esc_html__('Multicolumn Pricing Table', 'essential-addons-elementor');
    }

    public function get_icon()
    {
        return 'eaicon-multicolumn-pricing';
    }

    public function get_categories()
    {
        return [ 'essential-addons-elementor' ];
    }

    public function get_keywords()
    {
        return [
            'multi column',
            'price menu',
            'pricing',
            'price',
            'price table',
            'table',
            'ea table',
            'ea pricing table',
            'comparison table',
            'pricing plan',
            'dynamic price',
            'woocommerce pricing',
            'ea',
            'essential addons',
        ];
    }

	protected function is_dynamic_content():bool {
        return false;
    }

	public function has_widget_inner_wrapper(): bool {
        return ! Helper::eael_e_optimized_markup();
    }

    public function get_custom_help_url()
    {
        return 'https://essential-addons.com/elementor/docs/ea-multicolumn-pricing-table/';
    }

    protected function register_controls()
    {

		/**
         * Pricing Table Packages
         */
        $this->start_controls_section(
            'eael_multicolumn_pricing_table_layout',
            [
                'label' => esc_html__( 'Layouts', 'essential-addons-elementor' )
            ]
        );

		$image_dir_url = EAEL_PRO_PLUGIN_URL . 'assets/admin/images/layout-previews/multicolumn-pricing-';
		$this->add_control(
			'eael_mcpt_layout',
			[
				'label'       => '',
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'retro-layout' => [
						'title' => esc_html__( 'Retro', 'essential-addons-elementor' ),
						'image'  => $image_dir_url . 'retro-layout.png',
					],
					'modern-layout' => [
						'title' => esc_html__( 'Modern', 'essential-addons-elementor' ),
						'image'  => $image_dir_url . 'modern-layout.png',
					],
				],
				'default'     => 'retro-layout',
				'label_block' => true,
                'toggle'      => false,
                'image_choose'=> true,
			]
		);

		$this->add_control(
			'eael_mcpt_package_title_tag',
			[
				'label'       => esc_html__( 'Package Title Tag', 'essential-addons-elementor' ),
				'label_block' => true,
				'type'        => Controls_Manager::CHOOSE,
				'options'     => [
					'h1' => [
						'title' => esc_html__( 'H1', 'essential-addons-elementor' ),
						'icon'  => 'eicon-editor-h1',
					],
					'h2' => [
						'title' => esc_html__( 'H2', 'essential-addons-elementor' ),
						'icon'  => 'eicon-editor-h2',
					],
					'h3' => [
						'title' => esc_html__( 'H3', 'essential-addons-elementor' ),
						'icon'  => 'eicon-editor-h3',
					],
					'h4' => [
						'title' => esc_html__( 'H4', 'essential-addons-elementor' ),
						'icon'  => 'eicon-editor-h4',
					],
					'h5' => [
						'title' => esc_html__( 'H5', 'essential-addons-elementor' ),
						'icon'  => 'eicon-editor-h5',
					],
					'h6' => [
						'title' => esc_html__( 'H6', 'essential-addons-elementor' ),
						'icon'  => 'eicon-editor-h6',
					],
					'div' => [
						'title' => esc_html__( 'Div', 'essential-addons-elementor' ),
						'text'  => 'div',
					],
					'span' => [
						'title' => esc_html__( 'Span', 'essential-addons-elementor' ),
						'text'  => 'span',
					],
					'p' => [
						'title' => esc_html__( 'P', 'essential-addons-elementor' ),
						'text'  => 'P',
					],
				],
                'default'   => 'h2',
				'toggle'    => false,
			]
		);

		$this->add_control(
			'eael_mcpt_package_title_effect',
			[
				'label'   => esc_html__( 'Text Effect', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'no'      => esc_html__( 'No Effect', 'essential-addons-elementor' ),
					'marquee'  => esc_html__( 'Marquee', 'essential-addons-elementor' ),
					'reflect' => esc_html__( 'Reflect', 'essential-addons-elementor' ),
				],
			]
		);

		$this->add_control(
			'eael_mcpt_package_title_marquee_direction',
			[
				'label'   => esc_html__( 'Direction', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'scroll-left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-left',
					],
					'scroll-right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => 'scroll-left',
				'toggle'  => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.title-marquee .eael-mcpt-package .eael-mcpt-package-title' => 'animation-name: {{VALUE}};',
				],
				'condition' => [
					'eael_mcpt_package_title_effect' => 'marquee'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_marquee_speed',
			[
				'label'      => esc_html__( 'Animation Speed', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range'      => [
					's' => [
						'min' => 1,
						'max' => 10000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.title-marquee .eael-mcpt-package .eael-mcpt-package-title' => 'animation-duration: {{SIZE}}s;',
				],
				'condition' => [
					'eael_mcpt_package_title_effect' => 'marquee'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_feature',
			[
				'label'        => esc_html__( 'Collapse Feature Rows', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'separator'    => 'before',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_feature_rows',
			[
				'label'     => esc_html__( 'Number of Rows', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 3,
				'min'       => 1,
				'condition' => [
					'eael_mcpt_collaps_feature' => 'yes'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_feature_icon_position',
			[
				'label'   => esc_html__( 'Icon Position', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'toggle'  => false,
				'condition' => [
					'eael_mcpt_collaps_feature' => 'yes'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_closed_label',
			[
				'label'   => esc_html__( 'Collapsed Label', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'ai'      => [ 'active' => false ],
				'default' => esc_html__( 'See More', 'essential-addons-elementor' ),
				'condition' => [
					'eael_mcpt_collaps_feature' => 'yes'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_closed_icon',
			[
				'label'   => esc_html__( 'Collapsed Icon', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-angle-down',
					'library' => 'fa-solid',
				],
				'condition' => [
					'eael_mcpt_collaps_feature' => 'yes'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_expanded_label',
			[
				'label'   => esc_html__( 'Expanded Label', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'ai'      => [ 'active' => false ],
				'default' => esc_html__( 'See Less', 'essential-addons-elementor' ),
				'condition' => [
					'eael_mcpt_collaps_feature' => 'yes'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_expanded_icon',
			[
				'label' => esc_html__( 'Expanded Icon', 'essential-addons-elementor' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-angle-up',
					'library' => 'fa-solid',
				],
				'condition' => [
					'eael_mcpt_collaps_feature' => 'yes'
				]
			]
		);

		$this->end_controls_section();

		/**
		 * Pricing Table Features title
		 */
		
		 $this->start_controls_section(
			'eael_multicolumn_pricing_table_features',
			[
				'label' => esc_html__( 'Title Column', 'essential-addons-elementor' ),
				]
		);

		$this->add_control(
			'eael_mcpt_corner_cell_heading',
			[
				'label' => esc_html__( 'Header Content', 'essential-addons-elementor' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'eael_mcpt_icon_type',
			[
				'label'   => esc_html__( 'Icon Type', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'text',
				'options' => [
					'text' => [
						'title' => esc_html__( 'Text', 'essential-addons-elementor' ),
						'icon' => 'eicon-t-letter',
					],
					'icon' => [
						'title' => esc_html__( 'Icon', 'essential-addons-elementor' ),
						'icon' => 'eicon-favorite',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'essential-addons-elementor' ),
						'icon' => 'eicon-image',
					],
				],
			]
		);

		$this->add_control(
			'eael_mcpt_text',
			[
				'label'     => '',
				'type' 	    => Controls_Manager::TEXT,
				'ai'   	    => [ 'active' => false, ],
				'label_block' => true,
				'condition' => [
					'eael_mcpt_icon_type' => 'text',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_icon',
			[
				'label'     => '',
				'type'      => Controls_Manager::ICONS,
				'separator' => 'after',
				'default' => [
					'value' => 'fas fa-home',
					'library' => 'fa-solid',
				],
				'condition' => [
					'eael_mcpt_icon_type' => 'icon',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_image',
			[
				'label'     => '',
				'type'      => Controls_Manager::MEDIA,
				'separator' => 'after',
				'ai'        => [ 'active' => false, ],
				'condition' => [
					'eael_mcpt_icon_type' => 'image',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_features_heading',
			[
				'label' => esc_html__( 'Features', 'essential-addons-elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'eael_mcpt_feature_title_icon_position',
			[
				'label'   => esc_html__( 'Icon Position', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-left',
					],
					'top' => [
						'title' => esc_html__( 'Top', 'essential-addons-elementor' ),
						'icon'  => 'eicon-v-align-top',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-right',
					],
					'bottom' => [
						'title' => esc_html__( 'Bottom', 'essential-addons-elementor' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default' => 'left',
				'toggle'  => false,
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout',
				]
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'eael_mcpt_feature_title',
			[
				'label'   => esc_html__( 'Title', 'essential-addons-elementor'),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Feature', 'essential-addons-elementor'),
				'ai' 	  => [ 'active' => false ],
			]
		);

		$repeater->add_control(
			'eael_mcpt_feature_title_icon',
			[
				'label'   => esc_html__( 'Icon', 'essential-addons-elementor'),
				'type'    => Controls_Manager::ICONS,
				'description'  => esc_html__( 'This Icon will only available for Modern Layout.', 'essential-addons-elementor' ),
				'default' => [
					'value' => 'fas fa-home',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			"eael_mcpt_feature_titles",
			[
				'type'      => Controls_Manager::REPEATER,
				'seperator' => 'before',
				'default'   => [
					[ 'eael_mcpt_feature_title' => 'Total Features' ],
					[ 'eael_mcpt_feature_title' => 'Advanced Features' ],
					[ 'eael_mcpt_feature_title' => 'Cloud Storage' ],
					[ 'eael_mcpt_feature_title' => 'Priority Support' ],
					[ 'eael_mcpt_feature_title' => 'Analytics Suite' ],
				],
				'button_text' => esc_html__( 'Add Feature', 'essential-addons-elementor' ),
				'fields'      => $repeater->get_controls() ,
				'title_field' => '{{eael_mcpt_feature_title}}',
			]
		);

		$this->add_control('eael_mcpt_sunc_packages_features',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => '<div style="display:grid;justify-content:center;align-items:center;">
							<button class="eael-mcpt-action-btn elementor-button" style="background: #d30c5c;">' . __( 'Sync Features', 'essential-addons-elementor' ) . '</button>
							<p class="elementor-control-field-description">' . __( 'After adding or removing features, you need to click on the button to sync all the features.', 'essential-addons-elementor' ) . '</p>
						</div>'
			]
		);
			
		$this->end_controls_section();
		
		/**
		 * Pricing Table Packages
		 */
		
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_packages',
			[
				'label' => esc_html__( 'Pricing Packages', 'essential-addons-elementor' ),
			]
		);

		$this->add_control(
			'eael_mcpt_widget_id',
			[
				'label'       => '',
				'type'        => Controls_Manager::HIDDEN,
			]
		);

		$packages 	= new Repeater();
		
		$packages->add_control(
			'eael_mcpt_package_title',
			[
				'label'   => 'Name',
				'type'    => Controls_Manager::TEXT,
				'default' => 'Package',
				'ai'      => [ 'active' => false, ],
			]
		);

		$packages->add_control(
			"eael_mcpt_package_is_featured",
			[
				'label'        => esc_html__( 'Make it Featured', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'ai'           => [ 'active' => false, ],
			]
		);

		$packages->add_control(
			"eael_mcpt_featured_badge_icon",
			[
				'label'   => esc_html__( 'Icon', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-check-circle',
					'library' => 'fa-solid',
				],
				'condition' => [
					"eael_mcpt_package_is_featured" => 'yes',
				]
			]
		);

		$packages->add_control(
			"eael_mcpt_featured_badge_icon_position",
			[
				'label'   => esc_html__( 'Icon Position', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'condition' => [
					"eael_mcpt_package_is_featured" => 'yes',
				],
				'default' => 'left',
				'toggle'  => false,
			]
		);

		$packages->add_control(
			"eael_mcpt_featured_badge_text",
			[
				'label'   => esc_html__( 'Featured Text Label', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'ai' 	  => [ 'active' => false ],
				'separator' => 'after',
				'default' => esc_html__( 'Best Value', 'essential-addons-elementor' ),
				'condition' => [
					"eael_mcpt_package_is_featured" => 'yes',
				]
			]
		);

		$packages->add_control(
			"eael_mcpt_package_currency",
			[
				'label'   => esc_html__( 'Currency', 'essential-addons-elementor' ),
				'default' => '$',
				'type'    => Controls_Manager::TEXT,
				'ai' 	  => [ 'active' => false, ],
			]
		);

		$packages->add_control(
			"eael_mcpt_package_currency_position",
			[
				'label'   => esc_html__( 'Currency Position', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'toggle'  => false,
			]
		);

		$packages->add_control(
			"eael_mcpt_package_price",
			[
				'label'   => esc_html__( 'Price', 'essential-addons-elementor' ),
				'default' => 10,
				'type'    => Controls_Manager::NUMBER,
				'ai' 	  => [ 'active' => false, ],
			]
		);

		$packages->add_control(
			"eael_mcpt_package_sale_price",
			[
				'label'   => esc_html__( 'Sale Price', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'ai' 	  => [ 'active' => false, ],
			]
		);

		$packages->add_control(
			"eael_mcpt_package_sale_price_position",
			[
				'label'   => esc_html__( 'Sale Price Position', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'before' => [
						'title' => esc_html__( 'Before', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-left',
					],
					'after' => [
						'title' => esc_html__( 'After', 'essential-addons-elementor' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default' => 'after',
				'toggle'  => false,
				'condition' => [
					"eael_mcpt_package_sale_price!" => ''
				]
			]
		);

		$packages->add_control(
			"eael_mcpt_package_period",
			[
				'label'   => esc_html__( 'Period (Per)', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'month', 'essential-addons-elementor' ),
				'ai' 	  => [ 'active' => false, ],
			]
		);

		$packages->add_control(
			"eael_mcpt_package_period_separator",
			[
				'label'   => esc_html__( 'Period Separator', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '/',
				'ai' 	  => [ 'active' => false, ],
			]
		);

		$packages->add_control(
			"eael_mcpt_package_enable_button",
			[
				'label'        => esc_html__( 'Enable Button', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'ai'           => [ 'active' => false, ],
				'separator'    => 'before',
				'default'      => 'yes',
			]
		);

		$packages->add_control(
			"eael_mcpt_package_button_text",
			[
				'label'   => esc_html__( 'Text', 'essential-addons-elementor' ),
				'default' => esc_html__( 'Buy Now', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'ai' 	  => [ 'active' => false, ],
				'condition' => [
					"eael_mcpt_package_enable_button" => 'yes',
				]
			]
		);

		$packages->add_control(
			"eael_mcpt_package_link",
			[
				'label'       => esc_html__( 'Link', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::URL,
				'label_block' => true,
				'default'     => [ 'url' => '#' ],
				'condition' => [
					"eael_mcpt_package_enable_button" => 'yes',
				]
			]
		);

		$this->add_control(
			"eael_mcpt_packages",
			[
				'label' 	  => '',
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $packages->get_controls() ,
				'title_field' => '{{eael_mcpt_package_title}}',
				'button_text' => esc_html__( 'Add Package', 'essential-addons-elementor' ),
				'default'     => [
					[ 'eael_mcpt_package_title' => 'Silver' ],
					[ 'eael_mcpt_package_title' => 'Bronze' ],
					[ 'eael_mcpt_package_title' => 'Gold' ],
				]
			]
		);

		$this->add_control('eael_mcpt_render_packages_controllers',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => '<div style="display:grid;justify-content:center;align-items:center;">
							<button class="eael-mcpt-action-btn elementor-button" style="background: #d30c5c;">' . __( 'Sync Pricing Packages', 'essential-addons-elementor' ) . '</button>
							<p class="elementor-control-field-description">' . __( 'After adding or removing pricing packages, you need to click on the button to sync all the packages.', 'essential-addons-elementor' ) . '</p>
						</div>'
			]
		);
		
		$this->end_controls_section();

		/**
         * Pricing Table Packages
		 * Render according Packages repeater
         */
		
		$post_id = Helper::current_revision_id();
		$post_documents = Plugin::$instance->documents->get( $post_id );
		if ( is_object( $post_documents ) ) {
			$ea_elements_data = $post_documents->get_elements_data();
			Plugin::$instance->db->iterate_data( $ea_elements_data, function ( $element ) {
				if ( isset( $element['widgetType'] ) && $element['widgetType'] === 'eael-multicolumn-pricing-table' ) {
					if ( ! empty( $element['id'] ) && ! empty( $element['settings']['eael_mcpt_packages'] ) ) {
						$features = [];
						$default_content = [ '50', '100', 'Unlimited' ];
						if ( ! empty( $element['settings']['eael_mcpt_feature_titles'] ) ) {
							$features = $element['settings']['eael_mcpt_feature_titles'];
						}

						$element_id = $element['id'];
						$current_packages = $element['settings']['eael_mcpt_packages'];
						foreach( $current_packages as $pack_index => $package ){
							$package_id     = $package['_id'];
							$this->start_controls_section(
								"eael_mcpt_package_{$element_id}_{$package_id}_content",
								[
									'label' => '<span style="font-size:10px;">&#11153;</span> ' . $package['eael_mcpt_package_title'],
									'tab' => Controls_Manager::TAB_CONTENT,
									'condition' => [
										'eael_mcpt_widget_id' => $element['id']
									]
								]
							);

							$repeater 	= "repeater_{$package_id}";
							$$repeater 	= new Repeater();

							$$repeater->add_control(
								'eael_mcpt_feature_title',
								[ 'type'    => Controls_Manager::HIDDEN ]
							);
					
							$$repeater->add_control(
								'eael_mcpt_feature_icon',
								[
									'label'   => esc_html__( 'Icon', 'essential-addons-elementor'),
									'type'    => Controls_Manager::ICONS,
								]
							);
					
							$$repeater->add_control(
								'eael_mcpt_feature_individually',
								[
									'label'        => esc_html__( 'Individual Color', 'essential-addons-elementor' ),
									'type'         => Controls_Manager::SWITCHER,
									'return_value' => 'yes',
									'ai'           => [ 'active' => false, ],
								]
							);
					
							$$repeater->add_control(
								'eael_mcpt_feature_individual_icon_color',
								[
									'label'     => esc_html__( 'Icon Color', 'essential-addons-elementor' ),
									'type'      => Controls_Manager::COLOR,
									'default'   => '#333',
									'selectors' => [
										'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature{{CURRENT_ITEM}} .eael-mcpt-feature-icon' => 'color: {{VALUE}}',
										'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature{{CURRENT_ITEM}} svg.eael-mcpt-feature-icon' => 'fill: {{VALUE}}'
									],
									'condition' => [
										'eael_mcpt_feature_individually' => 'yes',
									]
								]
							);
					
							$$repeater->add_control(
								'eael_mcpt_feature_show_content',
								[
									'label'        => esc_html__( 'Show Content', 'essential-addons-elementor' ),
									'type'         => Controls_Manager::SWITCHER,
									'return_value' => 'yes',
									'ai'           => [ 'active' => false, ],
								]
							);
					
							$$repeater->add_control(
								'eael_mcpt_feature_content_position',
								[
									'label'   => esc_html__( 'Content Position', 'essential-addons-elementor' ),
									'type'    => Controls_Manager::CHOOSE,
									'options' => [
										'left' => [
											'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
											'icon'  => 'eicon-h-align-left',
										],
										'right' => [
											'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
											'icon'  => 'eicon-h-align-right',
										],
									],
									'default' => 'left',
									'toggle'  => false,
									'condition' => [
										'eael_mcpt_feature_show_content' => 'yes',
									]
								]
							);

							$$repeater->add_responsive_control(
								'eael_mcpt_feature_content_icon_gap',
								[
									'label'      => esc_html__( 'Spacing', 'essential-addons-elementor' ),
									'type'       => Controls_Manager::SLIDER,
									'size_units' => [ 'px' ],
									'range'      => [
										'px' => [
											'min' => 0,
											'max' => 500,
											'step' => 1,
										],
									],
									'default' => [
										'unit' => 'px',
										'size' => 5,
									],
									'selectors' => [
										'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature{{CURRENT_ITEM}} .eael-mcpt-feature-value' => 'gap: {{SIZE}}{{UNIT}};',
									],
									'condition' => [
										'eael_mcpt_feature_show_content' => 'yes',
									]
								]
							);
					
							$$repeater->add_control(
								'eael_mcpt_feature_individual_content_color',
								[
									'label'     => esc_html__( 'Content Color', 'essential-addons-elementor' ),
									'type'      => Controls_Manager::COLOR,
									'default'   => '#333',
									'selectors' => [
										'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature{{CURRENT_ITEM}} .eael-mcpt-feature-content' => 'color: {{VALUE}}',
										'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature{{CURRENT_ITEM}} .eael-mcpt-feature-content *' => 'color: {{VALUE}}',
									],
									'condition' => [
										'eael_mcpt_feature_individually' => 'yes',
										'eael_mcpt_feature_show_content' => 'yes',
									]
								]
							);
					
							$$repeater->add_control(
								'eael_mcpt_feature_content',
								[
									'label'       => '',
									'type'        => Controls_Manager::WYSIWYG,
									'ai'          => [ 'active' => false, ],
									'default'     => esc_html__( 'Content', 'essential-addons-elementor'),
									'condition'   => [
										'eael_mcpt_feature_show_content' => 'yes',
									]
								]
							);

							$default_values = [];
							foreach( $features as $key => $feature ) {
								$icon = [
									'value'   => $pack_index < 1 && $key > 2 ? 'far fa-times-circle' : 'far fa-check-circle',
									'library' => 'fa-regular',
								];

								$default_values[] = [
									'eael_mcpt_feature_title' => $feature['eael_mcpt_feature_title'],
									'eael_mcpt_feature_title_id' => $feature['_id'],
									'eael_mcpt_feature_show_content' => 0 === $key ? 'yes' : 'no',
									'eael_mcpt_feature_content' => isset( $default_content[ $pack_index ] ) ? $default_content[ $pack_index ] : 'Unlimited',
									'eael_mcpt_feature_icon' => 0 === $key ? '' : $icon,
								];
							}
					
							$this->add_control(
								"eael_mcpt_package_{$element_id}_{$package_id}_features",
								[
									'type'         => Controls_Manager::REPEATER,
									'seperator'    => 'before',
									'item_actions' => [
										'add'       => false,
										'duplicate' => false,
										'remove'    => false,
										'sort'      => false,
									],
									'default'      => $default_values,
									'fields'      => $$repeater->get_controls() ,
									'title_field' => '{{{eael_mcpt_feature_title}}}',
								]
							);

							$this->end_controls_section();
						}
					}
				}
			});
		}

		/**
		 * Pricing Table Title General Style
		 */
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_general_style',
			[
				'label' => esc_html__( 'General', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_column_gap',
			[
				'label'      => esc_html__( 'Column Gap', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.retro-layout .eael-mcpt-columns' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_layout' => 'retro-layout',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'eael_mcpt_background',
				'selector'  => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.modern-layout .eael-multicolumn-pricing-table .eael-mcpt-column-0, 
								{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.modern-layout .eael-multicolumn-pricing-table .eael-mcpt-package, 
								{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.modern-layout .eael-multicolumn-pricing-table .eael-mcpt-button-cell,
								{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.modern-layout .eael-mcpt-collaps',
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout',
				]
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-multicolumn-pricing-table' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout',
				]
			]
		);

		$this->end_controls_section();

		/**
		 * Pricing Table featured badge Style
		 */
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_featured_style',
			[
				'label' => esc_html__( 'Featured Badge', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_featured_badge_alignment',
			[
				'label'   => esc_html__( 'Alignment', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'toggle'  => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_featured_badge_text_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge .eael-mcpt-featured-badge-text, {{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-multicolumn-pricing-table-mobile .eael-mcpt-featured .eael-mcpt-featured-badge .eael-mcpt-featured-badge-text',
			]
		);

		$this->add_control(
			'eael_mcpt_featured_badge_text_color',
			[
				'label' => esc_html__( 'Text Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000',
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge' => 'color: {{VALUE}}',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge svg.eael-mcpt-featured-badge-icon' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_featured_badge_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#a3a3a3',
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_featured_badge_icon_size',
			[
				'label'     => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'default' => [
					'size' => 14,
					'unit' => 'px' 
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge .eael-mcpt-featured-badge-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge svg.eael-mcpt-featured-badge-icon' => 'height: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};line-height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'eael_mcpt_featured_badge_border',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge',
			]
		);

		$this->add_control(
			'eael_mcpt_featured_badge_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' 	 => [
					'top'    => '6',
					'right'  => '6',
					'bottom' => '0',
					'left'   => '0',
					'unit'   => 'px'
				],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_featured_badge_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'default'    => [
					'top'    => 5,
					'right'  => 15,
					'bottom' => 5,
					'left'   => 15,
					'unit'   => 'px'
				],
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'eael_mcpt_featured_badge_position',
			[
				'label'      => esc_html__( 'Position', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'size' => '-34',
					'unit' => 'px',
				],
				'range'      => [
					'px' => [
						'min' => -500,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column .eael-mcpt-featured-badge' => 'top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns.has-featured' => 'margin-top: calc({{SIZE}}{{UNIT}}*-1);',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_featured_column_styling_heading',
			[
				'label'     => esc_html__( 'Featured Column', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'eael_mcpt_layout' => 'retro-layout',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'eael_mcpt_featured_badge_background_color',
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column.eael-mcpt-featured-column',
				'condition' => [
					'eael_mcpt_layout' => 'retro-layout',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_featured_column_modern_styling_heading',
			[
				'label'     => esc_html__( 'Featured Column Backgrounds', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_featured_badge_package_background_color',
			[
				'label' => esc_html__( 'Package', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column.eael-mcpt-featured-column .eael-mcpt-package' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_featured_badge_features_background_color',
			[
				'label' => esc_html__( 'Features', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column.eael-mcpt-featured-column .eael-mcpt-feature' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_featured_badge_button_cell_background_color',
			[
				'label' => esc_html__( 'Button Cell', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .has-featured .eael-mcpt-column.eael-mcpt-featured-column .eael-mcpt-button-cell' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout',
				]
			]
		);

		$this->end_controls_section();

		/**
		 * Pricing Table Title Column Style
		 */
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_title_column_style',
			[
				'label' => esc_html__( 'Title Column', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'eael_mcpt_title_column_width',
			[
				'label' => esc_html__( 'Width', 'essential-addons-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 100,
					],
					'px' => [
						'min' => 10,
						'max' => 600,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-column.eael-mcpt-column-0' => 'flex: 0 0 {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_corner_cell_style_heading',
			[
				'label'     => esc_html__( 'Header Content', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'eael_mcpt_corner_cell_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-corner-cell' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'eael_mcpt_text_typography',
				'selector'  => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-corner-cell .eael-mcpt-text',
				'condition' => [
					'eael_mcpt_icon_type' => 'text',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_icon_size',
			[
				'label'     => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 50,
					'unit' => 'px'
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-corner-cell .eael-mcpt-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-corner-cell svg.eael-mcpt-icon' => 'height: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};line-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_icon_type' => 'icon',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_icon_color',
			[
				'label' => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-text' => 'color: {{VALUE}}',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper svg.eael-mcpt-icon' => 'fill: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_icon_type' => [ 'icon', 'text' ],
				]
			]
		);

		$this->add_control(
			'eael_mcpt_image_size_popover',
			[
				'label'        => esc_html__( 'Image Size', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'yes',
				'condition'    => [
					'eael_mcpt_icon_type' => 'image',
				]
			]
		);

		$this->start_popover();

		$this->add_control(
			'eael_mcpt_image_height',
			[
				'label'     => esc_html__( 'Height', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'em' ],
				'range'     => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-corner-cell .eael-mcpt-image' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_icon_type' => 'image',
				]
			]
		);

		$this->add_control(
			'eael_mcpt_image_width',
			[
				'label'     => esc_html__( 'Width', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'em' ],
				'range'     => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-corner-cell .eael-mcpt-image' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_icon_type' => 'image',
				]
			]
		);

		$this->add_control(
			'eael_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-cell.eael-mcpt-corner-cell .eael-mcpt-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_icon_type' => 'image',
				]
			]
		);

		$this->end_popover();

		$this->add_control(
			'eael_mcpt_content_position_popover',
			[
				'label'        => esc_html__( 'Content Position', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'return_value' => 'yes',
			]
		);

		$this->start_popover();

		$this->add_control(
			'eael_mcpt_content_position_horizontal',
			[
				'label'   => esc_html__( 'Horizontal', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-cell.eael-mcpt-corner-cell' => 'display:flex;justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_content_position_vertical',
			[
				'label'   => esc_html__( 'Vertical', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => esc_html__( 'Top', 'essential-addons-elementor' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon' => 'eicon-v-align-middle',
					],
					'end' => [
						'title' => esc_html__( 'Bottom', 'essential-addons-elementor' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-cell.eael-mcpt-corner-cell' => 'display:flex;align-items: {{VALUE}};',
				],
			]
		);

		$this->end_popover();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'eael_mcpt_corner_cell_border',
				'selector'  => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-cell.eael-mcpt-corner-cell',
			]
		);

		$this->add_control(
			'eael_mcpt_corner_cell_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-cell.eael-mcpt-corner-cell' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_corner_cell_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-corner-cell' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_title_column_style_heading',
			[
				'label'     => esc_html__( 'Features Title', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'eael_mcpt_title_column_alignment',
			[
				'label'   => esc_html__( 'Alignment', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default'   => 'left',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.retro-layout .eael-mcpt-title-cell' => 'display:flex;justify-content: {{VALUE}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell.eael-mcpt-title-icon-left' => 'display:flex;justify-content: {{VALUE}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell.eael-mcpt-title-icon-right' => 'display:flex;justify-content: {{VALUE}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell.eael-mcpt-title-icon-top' => 'display:grid;justify-content: {{VALUE}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell.eael-mcpt-title-icon-top .eael-mcpt-feature-title-icon-wrapper' => 'display:flex; justify-content: {{VALUE}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell.eael-mcpt-title-icon-bottom' => 'display:grid;justify-content: {{VALUE}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell.eael-mcpt-title-icon-bottom .eael-mcpt-feature-title-icon-wrapper' => 'display:flex; justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_title_column_background',
			[
				'label' => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout!' => 'modern-layout'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'eael_mcpt_title_column_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell',
			]
		);

		$this->add_control(
			'eael_mcpt_title_column_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell' => 'color: {{VALUE}}',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell .eael-mcpt-feature-title' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_title_column_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .modern-layout.eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell .eael-mcpt-feature-title-icon' => 'color: {{VALUE}}',
					'{{WRAPPER}} .modern-layout.eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell svg.eael-mcpt-feature-title-icon' => 'fill: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_title_icon_size',
			[
				'label'     => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell .eael-mcpt-feature-title-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell svg.eael-mcpt-feature-title-icon' => 'height: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};line-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout'
				]
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_title_column_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' 	 => [
					'top'      => '22',
					'right'    => '22',
					'bottom'   => '22',
					'left'     => '22',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature' => 'padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'eael_mcpt_title_column_border',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell',
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_title_column_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-title-cell' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Pricing Table packages Column Style
		 */
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_feature_packages_style',
			[
				'label' => esc_html__( 'Packages', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'eael_mcpt_package_border_width_preset_2',
			[
				'label'     => esc_html__( 'Border Width', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.modern-layout .eael-mcpt-package:not(.eael-mcpt-title-cell):not(:last-child)' => 'border-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_package_border_color_preset_2',
			[
				'label'     => esc_html__( 'Border Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.modern-layout .eael-mcpt-package:not(.eael-mcpt-title-cell):not(:last-child)' => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout'
				]
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_column_width',
			[
				'label' => esc_html__( 'Width', 'essential-addons-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 100,
					],
					'px' => [
						'min' => 10,
						'max' => 600,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-column:not(.eael-mcpt-column-0)' => 'flex: 0 0 {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_column_height',
			[
				'label' => esc_html__( 'Height', 'essential-addons-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 100,
					],
					'px' => [
						'min' => 10,
						'max' => 600,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-cell.eael-mcpt-package' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-cell.eael-mcpt-corner-cell' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_alignment',
			[
				'label'   => esc_html__( 'Alignment', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package:not(.eael-mcpt-title-cell)' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'eael_mcpt_feature_package_background',
				'types'    => [ 'classic', 'gradient' ],
				'exclude'  => [ 'image' ],
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-column:not(.eael-mcpt-featured-column) .eael-mcpt-package:not(.eael-mcpt-title-cell)',
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_heading',
			[
				'label' => esc_html__( 'Title', 'essential-addons-elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_feature_package_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-title',
			]
		);

		$this->add_control(
			'eael_mcpt_package_title_reflection_gap',
			[
				'label'     => esc_html__( 'Reflection Gap', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'     => [
					'px' => [
						'min' => -500,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper.title-reflect .eael-mcpt-package .eael-mcpt-package-title' => '-webkit-box-reflect: below {{SIZE}}{{UNIT}} linear-gradient(to bottom, transparent, rgba(255, 255, 255, 0.5));',
				],
				'condition' => [
					'eael_mcpt_package_title_effect' => 'reflect'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_text_color',
			[
				'label' => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-title' => 'color: {{VALUE}};fill: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_title_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_title_margin',
			[
				'label'      => esc_html__( 'Margin', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_package_period_heading',
			[
				'label'     => esc_html__( 'Period', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_feature_period_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-period',
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_period_color',
			[
				'label' => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-period' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_period_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-period' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_sale_price_heading',
			[
				'label'     => esc_html__( 'Previous Price', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_feature_sale_price_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-prices .eael-mcpt-package-old-price',
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_sale_price_color',
			[
				'label' => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-prices .eael-mcpt-package-old-price' => 'color: {{VALUE}};fill: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_sale_price_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-prices .eael-mcpt-package-old-price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_price_heading',
			[
				'label'     => esc_html__( 'Price', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_feature_price_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-prices .eael-mcpt-package-price',
			]
		);

		$this->add_control(
			'eael_mcpt_feature_package_price_color',
			[
				'label' => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-prices .eael-mcpt-package-price' => 'color: {{VALUE}};fill: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_price_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-prices .eael-mcpt-package-price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_package_price_margin',
			[
				'label'      => esc_html__( 'Margin', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-package-prices' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Pricing Table Features Column Style
		 */
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_feature_column_style',
			[
				'label' => esc_html__( 'Features', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'eael_mcpt_row_height',
			[
				'label'      => esc_html__( 'Height', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-feature' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-title-cell' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_feature_background',
			[
				'label' => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-column:not(.eael-mcpt-featured-column) .eael-mcpt-feature' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout!' => 'modern-layout'
				]
			]
		);

		$this->add_control(
			'eael_mcpt_feature_column_background_preset_2',
			[
				'label' => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-feature ' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'eael_mcpt_layout' => 'modern-layout'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_feature_column_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-feature',
			]
		);

		$this->add_control(
			'eael_mcpt_feature_column_text_color',
			[
				'label' => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature' => 'color: {{VALUE}};fill: {{VALUE}}',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature .eael-mcpt-feature-icon' => 'color: {{VALUE}};fill: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_feature_column_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature .eael-mcpt-feature-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-feature svg.eael-mcpt-feature-icon' => 'height: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_feature_column_alignment',
			[
				'label'   => esc_html__( 'Alignment', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-feature' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'eael_mcpt_feature_cell_border',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-feature',
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_feature_cell_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-feature' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_feature_cell_padding',
			[
				'label'              => __( 'Padding', 'essential-addons-elementor' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => [ 'px', 'em', '%' ],
				'allowed_dimensions' => [ 'left', 'right' ],
				'selectors'          => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-columns .eael-mcpt-feature' => 'padding-right: {{RIGHT}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}};',
				],
			]
		);
		

		$this->end_controls_section();

		/**
		 * Pricing Table Button Style
		 */
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_button_style',
			[
				'label' => esc_html__( 'Buy Button', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_button_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button',
			]
		);

		$this->add_control(
			'eael_mcpt_button_alignment',
			[
				'label'   => esc_html__( 'Alignment', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button-wrapper' => 'display:flex;justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_button_width',
			[
				'label'      => esc_html__( 'Width', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button-wrapper' => 'width: 100%;',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'eael_mcpt_button_tabs' );

		$this->start_controls_tab(
			'eael_mcpt_button_normal',
			[
				'label' => esc_html__( 'Normal', 'essential-addons-elementor' ),
			]
		);

		$this->add_control(
			'eael_mcpt_button_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_button_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'eael_mcpt_button_border',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button',
			]
		);

		$this->add_control(
			'eael_mcpt_button_border_radius',
			[
				'label'     => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'eael_mcpt_button_hover',
			[
				'label' => esc_html__( 'Hover', 'essential-addons-elementor' ),
			]
		);

		$this->add_control(
			'eael_mcpt_button_hover_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button:hover' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_button_hover_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_button_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_button_hover_border_radius',
			[
				'label'     => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'eael_mcpt_button_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'separator'  => 'before',
				'default'   => [
					'top'    => '10',
					'right'  => '20',
					'bottom' => '10',
					'left'   => '20',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_button_margin',
			[
				'label'      => esc_html__( 'Margin', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-buy-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Pricing Table collaps Button Style
		 */
		$this->start_controls_section(
			'eael_multicolumn_pricing_table_collaps_style',
			[
				'label' => esc_html__( 'Collaps Button', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'eael_mcpt_collaps_feature' => 'yes'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'eael_mcpt_collaps_typography',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label',
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps .eael-mcpt-collaps-label .eael-collaps-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps .eael-mcpt-collaps-label svg.eael-collaps-icon' => 'heigh: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_alignment',
			[
				'label'   => esc_html__( 'Alignment', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'essential-addons-elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default'   => 'left',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps' => 'display:grid;justify-content: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs( 'eael_mcpt_collaps_tabs' );

		$this->start_controls_tab(
			'eael_mcpt_collaps_normal',
			[
				'label' => esc_html__( 'Normal', 'essential-addons-elementor' ),
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label' => 'color: {{VALUE}}',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'eael_mcpt_collaps_border',
				'selector' => '{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label',
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_border_radius',
			[
				'label'     => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'eael_mcpt_collaps_hover',
			[
				'label' => esc_html__( 'Hover', 'essential-addons-elementor' ),
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_hover_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label:hover' => 'color: {{VALUE}}',
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label:hover svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_hover_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label:hover' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'eael_mcpt_collaps_hover_border_radius',
			[
				'label'     => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'eael_mcpt_collaps_padding',
			[
				'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'separator'  => 'before',
				'default'   => [
					'top'    => '10',
					'right'  => '20',
					'bottom' => '10',
					'left'   => '20',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'eael_mcpt_collaps_margin',
			[
				'label'      => esc_html__( 'Margin', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eael-multicolumn-pricing-table-wrapper .eael-mcpt-collaps' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render Pricing Table package html
	 *
	 * @since 3.0.0
	 * @access protected
	 */
	private function get_package_html( $package, $pack_id, $title_tag, $settings, $print_badge = false ) {
		$price      = '';
		$sale_price = '';
		
		if ( 'left' === $package["eael_mcpt_package_currency_position"] ) {
			$price = $package["eael_mcpt_package_currency"] . $package["eael_mcpt_package_price"];
			$sale_price = $package["eael_mcpt_package_currency"] . $package["eael_mcpt_package_sale_price"];
		} else {
			$price = $package["eael_mcpt_package_price"] . $package["eael_mcpt_package_currency"];
			$sale_price = $package["eael_mcpt_package_sale_price"] . $package["eael_mcpt_package_currency"];
		}
		$is_recomended = $print_badge & 'yes' === $package["eael_mcpt_package_is_featured"];
		ob_start();
		if ( $is_recomended && '' !== $package["eael_mcpt_featured_badge_text"] ) {
			echo '<div class="eael-mcpt-featured-badge">';
			if ( 'right' === $package["eael_mcpt_featured_badge_icon_position"] ) {
				echo "<span class='eael-mcpt-featured-badge-text'>" . esc_html( $package["eael_mcpt_featured_badge_text"] ) . "</span>";
			}
			Icons_Manager::render_icon( $package["eael_mcpt_featured_badge_icon"], [ 'class' => 'eael-mcpt-featured-badge-icon' ] );
			if ( 'left' === $package["eael_mcpt_featured_badge_icon_position"] ) {
				echo "<span class='eael-mcpt-featured-badge-text'>" . esc_html( $package["eael_mcpt_featured_badge_text"] ) . "</span>";
			}
			echo '</div>';
		}
		?>
		<div class="eael-mcpt-cell eael-mcpt-package<?php echo " elementor-repeater-item-{$pack_id}" . ( $is_recomended ? " eael-mcpt-featured" : '' ); ?>">
			<?php
				$is_marquee = isset( $settings['eael_mcpt_package_title_effect'] ) && 'marquee' === $settings['eael_mcpt_package_title_effect'];
				if ( $is_marquee ) {
					echo "<div class='eael-mcpt-package-marqueee'>";
				}
				echo sprintf( '<%1$s class="eael-mcpt-package-title">%2$s</%1$s>', esc_attr( $title_tag ), esc_html( $package["eael_mcpt_package_title"] ) ); 
				if ( $is_marquee ) {
					echo "</div>";
				}
			?>
			<div class="eael-mcpt-package-prices">
				<?php 
					if ( 'retro-layout' === $settings['eael_mcpt_layout'] ) {
						if ( ! empty( $package["eael_mcpt_package_sale_price"] ) ) {
							if ( isset( $package['eael_mcpt_package_sale_price_position'] ) && 'before' === $package['eael_mcpt_package_sale_price_position'] ) {
								echo "<span class='eael-mcpt-package-price'>" . esc_html( $sale_price ) . "</span>";
								echo "<span class='eael-mcpt-package-old-price'>" . esc_html( $price ) . "</span>";
							} else {
								echo "<span class='eael-mcpt-package-old-price'>" . esc_html( $price ) . "</span>";
								echo "<span class='eael-mcpt-package-price'>" . esc_html( $sale_price ) . "</span>";
							}
						} else {
							echo "<span class='eael-mcpt-package-price'>" . esc_html( $price ) . "</span>";
						}
						echo "<span class='eael-mcpt-package-period'>" . esc_html( $package["eael_mcpt_package_period_separator"] . $package["eael_mcpt_package_period"] ) . "</span>";
					} else {
						if ( ! empty( $package["eael_mcpt_package_sale_price"] ) ) {
							if ( isset( $package['eael_mcpt_package_sale_price_position'] ) && 'before' === $package['eael_mcpt_package_sale_price_position'] ) {
								echo "<span class='eael-mcpt-package-price'>" . esc_html( $sale_price ) . "</span>";
								echo "<span class='eael-mcpt-package-period'>" . esc_html( $package["eael_mcpt_package_period_separator"] . $package["eael_mcpt_package_period"] ) . "</span>";

								echo "<div class='eael-mcpt-package-old-price-wrapper'>
										<span class='eael-mcpt-package-old-price'>" . esc_html( $price ) . "</span>
										<span class='eael-mcpt-package-period'>" . esc_html( $package["eael_mcpt_package_period_separator"] . $package["eael_mcpt_package_period"] ) . "</span>
									</div>";
							} else {
								echo "<div class='eael-mcpt-package-old-price-wrapper'>
										<span class='eael-mcpt-package-old-price'>" . esc_html( $price ) . "</span>
										<span class='eael-mcpt-package-period'>" . esc_html( $package["eael_mcpt_package_period_separator"] . $package["eael_mcpt_package_period"] ) . "</span>
									</div>";

								echo "<span class='eael-mcpt-package-price'>" . esc_html( $sale_price ) . "</span>";
								echo "<span class='eael-mcpt-package-period'>" . esc_html( $package["eael_mcpt_package_period_separator"] . $package["eael_mcpt_package_period"] ) . "</span>";
									
							}
						} else {
							echo "<span class='eael-mcpt-package-price'>" . esc_html( $price ) . "</span>";
							echo "<span class='eael-mcpt-package-period'>" . esc_html( $package["eael_mcpt_package_period_separator"] . $package["eael_mcpt_package_period"] ) . "</span>";
						}
					}
				?>
			</div>

			<?php
			if( 'retro-layout' === $settings['eael_mcpt_layout'] ) {
				echo $this->get_buy_button_html( $package, $pack_id );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	private function get_feature_html( $feature, $feature_title ) {
		ob_start();
		echo '<div class="eael-mcpt-cell eael-mcpt-feature elementor-repeater-item-' . esc_attr( $feature['_id'] ) . ( isset( $feature['is_hidden'] ) && $feature['is_hidden'] ? ' hide' : '' ) . '">';
			echo "<div class='eael-mcpt-feature-title'>" . esc_html( $feature_title ) . "</div>";
			echo "<div class='eael-mcpt-feature-value'>";
			if ( ( empty( $feature['eael_mcpt_feature_content_position'] ) || 'right' === $feature['eael_mcpt_feature_content_position'] ) && ! empty( $feature['eael_mcpt_feature_icon'] ) ) {
				Icons_Manager::render_icon( $feature['eael_mcpt_feature_icon'], [ 'class' => 'eael-mcpt-feature-icon' ] );
			}

			if ( isset( $feature['eael_mcpt_feature_show_content'] ) && 'yes' === $feature['eael_mcpt_feature_show_content'] ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped;
				echo '<div class="eael-mcpt-feature-content">' . $this->parse_text_editor( $feature['eael_mcpt_feature_content'] ) . '</div>';
			}

			if ( isset( $feature['eael_mcpt_feature_content_position'] ) && 'left' === $feature['eael_mcpt_feature_content_position'] && ! empty( $feature['eael_mcpt_feature_icon'] ) ) {
				Icons_Manager::render_icon( $feature['eael_mcpt_feature_icon'], [ 'class' => 'eael-mcpt-feature-icon' ] );
			}
			echo '</div>';
		echo '</div>';
		return ob_get_clean();
	}

	private function get_html_for_collaps( $settings, $device = 'desktop' ) {
		if( 'yes' !== $settings['eael_mcpt_collaps_feature'] ) {
			return '';
		}
		ob_start();
		echo '<div class="eael-mcpt-collaps collapsed" data-device="desktop">';
			echo '<span class="eael-mcpt-collaps-label open">';
			if ( 'left' === $settings['eael_mcpt_collaps_feature_icon_position'] ) {
				Icons_Manager::render_icon( $settings['eael_mcpt_collaps_expanded_icon'], [ 'class' => 'eael-collaps-icon' ] );
			}
			echo esc_html( $settings['eael_mcpt_collaps_expanded_label'] );
			if ( 'right' === $settings['eael_mcpt_collaps_feature_icon_position'] ) {
				Icons_Manager::render_icon( $settings['eael_mcpt_collaps_expanded_icon'], [ 'class' => 'eael-collaps-icon' ] );
			}
			echo '</span>';
			echo '<span class="eael-mcpt-collaps-label collaps show">';
			if ( 'left' === $settings['eael_mcpt_collaps_feature_icon_position'] ) {
				Icons_Manager::render_icon( $settings['eael_mcpt_collaps_closed_icon'], [ 'class' => 'eael-collaps-icon' ] );
			}
			echo esc_html( $settings['eael_mcpt_collaps_closed_label'] );
			if ( 'right' === $settings['eael_mcpt_collaps_feature_icon_position'] ) {
				Icons_Manager::render_icon( $settings['eael_mcpt_collaps_closed_icon'], [ 'class' => 'eael-collaps-icon' ] );
			}
			echo '</span>';
		echo '</div>';

		return ob_get_clean();
	}

	private function get_buy_button_html( $package, $pack_id ) {
		if( 'yes' !== $package["eael_mcpt_package_enable_button"] ) {
			return '';
		}
		
		$attr_key = 'eael_mcpt_package_link_' . $pack_id;
		$this->add_link_attributes( $attr_key, $package["eael_mcpt_package_link"] );
		$this->add_render_attribute( $attr_key, 'class', 'eael-mcpt-buy-button' );
		$button_html  = '<div class="eael-mcpt-buy-button-wrapper">';
		$button_html .=  '<a ' . $this->get_render_attribute_string( $attr_key ) . '>' . esc_html( $package["eael_mcpt_package_button_text"] ) . '</a>';
		$button_html .= '</div>';

		return $button_html;
	}

	private function get_corner_cell_html( $settings ) {
		ob_start();
		echo "<div class='eael-mcpt-cell eael-mcpt-corner-cell'>";
		if ( 'text' === $settings['eael_mcpt_icon_type'] && ! empty( $settings['eael_mcpt_text'] ) ) {
			echo '<span class="eael-mcpt-text">' . esc_html( $settings['eael_mcpt_text'] ) . '</span>';
		}
		else if ( 'icon' === $settings['eael_mcpt_icon_type'] && ! empty( $settings['eael_mcpt_icon'] ) ) {
			Icons_Manager::render_icon( $settings['eael_mcpt_icon'], [ 'class' => 'eael-mcpt-icon' ] );
		} else if ( 'image' === $settings['eael_mcpt_icon_type'] && ! empty( $settings['eael_mcpt_image']['url'] ) ) {
			echo '<img src="' . esc_url( $settings['eael_mcpt_image']['url'] ) . '" alt="Table Image" class="eael-mcpt-image">';
		}
		echo "</div>";

		return ob_get_clean();
	}

	private function get_item_by_id( $itemns, $id ) {
		foreach ($itemns as $item) {
			if ( isset( $item['eael_mcpt_feature_title_id'] ) && $id === $item['eael_mcpt_feature_title_id'] ) {
				return $item;
			}
		}
		return null;
	}

	protected function render() {
        $settings = $this->get_settings_for_display();
		$packages = $settings['eael_mcpt_packages'];
		$packages_count = count( $packages );

		if ( $packages_count < 1 ) {
			return;
		}
		$this->add_render_attribute( 'eael_mcpt_wrapper', 'class', 'eael-multicolumn-pricing-table-wrapper' );
		$this->add_render_attribute( 'eael_mcpt_wrapper', 'class', $settings['eael_mcpt_layout'] );
		$this->add_render_attribute( 'eael_mcpt_wrapper', 'data-column', $packages_count + 1 );

		$max_features = count( $settings['eael_mcpt_feature_titles'] );
		$title_tag  = Helper::eael_validate_html_tag( $settings['eael_mcpt_package_title_tag'] );
		$feature_titles = $settings['eael_mcpt_feature_titles'];
		$badge_printed = $has_featured = false;
		$element_id = $this->get_id();

		$is_hidden = false;
		$showing_rows = $max_features;
		if ( 'yes' === $settings['eael_mcpt_collaps_feature'] ) {
			$is_hidden = true;
			$showing_rows = $settings['eael_mcpt_collaps_feature_rows'];
			$this->add_render_attribute( 'eael_mcpt_wrapper', 'class', 'collapsable' );
			$this->add_render_attribute( 'eael_mcpt_wrapper', 'data-row', $showing_rows );
		}

		$column_html = [];
		$column_html["column_0"][] = $this->get_corner_cell_html( $settings );

		for ( $cell_count = 0; $cell_count < $max_features ; $cell_count++) {
			$is_hidden = $cell_count >= $showing_rows;
			$classes = "eael-mcpt-cell eael-mcpt-title-cell elementor-repeater-item-" . $feature_titles[ $cell_count ]["_id"];
			$classes.=  $is_hidden ? ' hide' : '';
			$classes.= ' eael-mcpt-title-icon-' . $settings['eael_mcpt_feature_title_icon_position'];

			ob_start();
			echo "<div class='"  . esc_attr( $classes ) . "'>";
			if ( 'modern-layout' === $settings['eael_mcpt_layout'] && isset( $feature_titles[ $cell_count ] ) && in_array( $settings['eael_mcpt_feature_title_icon_position'], [ 'left', 'top' ] ) ) {
				echo 'top' === $settings['eael_mcpt_feature_title_icon_position'] ? '<div class="eael-mcpt-feature-title-icon-wrapper">' : '' ;
				Icons_Manager::render_icon( $feature_titles[ $cell_count ]['eael_mcpt_feature_title_icon'], [ 'class' => 'eael-mcpt-feature-title-icon' ] );
				echo 'top' === $settings['eael_mcpt_feature_title_icon_position'] ? '</div>' : '' ;
			}
			echo "<span class='eael-mcpt-feature-title'>" . esc_html( $feature_titles[ $cell_count ]['eael_mcpt_feature_title'] ) . "</span>";
			if ( 'modern-layout' === $settings['eael_mcpt_layout'] && isset( $feature_titles[ $cell_count ] ) && in_array( $settings['eael_mcpt_feature_title_icon_position'], [ 'right', 'bottom' ] ) ) {
				echo 'bottom' === $settings['eael_mcpt_feature_title_icon_position'] ? '<div class="eael-mcpt-feature-title-icon-wrapper">' : '' ;
				Icons_Manager::render_icon( $feature_titles[ $cell_count ]['eael_mcpt_feature_title_icon'], [ 'class' => 'eael-mcpt-feature-title-icon' ] );
				echo 'bottom' === $settings['eael_mcpt_feature_title_icon_position'] ? '</div>' : '' ;
			}
			echo "</div>";

			$column_html["column_0"][] = ob_get_clean();
		}

		if( 'modern-layout' === $settings['eael_mcpt_layout'] ) {
			$column_html["column_0"][] = "<div class='eael-mcpt-cell eael-mcpt-title-cell" . ( $is_hidden ? ' hide' : '' ) . "'></div>";
		}

		$has_featured = false;
		$featured_key = 0;
		foreach( $packages as $key => $package ) {
			$pack          = $key + 1;
			$pack_id       = $package['_id'];
			$featured 	   = ! $has_featured && 'yes' === $package["eael_mcpt_package_is_featured"];
				
			if ( $featured ) {
				$featured_key = $pack;
				$has_featured = true;
			}
			
			$column_html["column_{$pack}"][] = $this->get_package_html( $package, $pack_id, $title_tag, $settings, $featured );

			if ( isset( $settings["eael_mcpt_package_{$element_id}_{$pack_id}_features"] ) ) {
				$feature_title = $package["eael_mcpt_package_title"];
				for ( $cell_count = 1; $cell_count <= $max_features ; $cell_count++) {
					$feature_html = '';
					$feature_title = $feature_titles[ $cell_count-1 ]['eael_mcpt_feature_title'];
					$title_id = $feature_titles[ $cell_count-1 ]['_id'];
					$is_hidden = $cell_count > $showing_rows;
					if ( isset( $settings["eael_mcpt_package_{$element_id}_{$pack_id}_features"][$cell_count-1] ) ) {	
						$feature = $this->get_item_by_id( $settings["eael_mcpt_package_{$element_id}_{$pack_id}_features"], $title_id );
						$feature['is_hidden'] = $is_hidden;
						$feature_html .= $this->get_feature_html( $feature, $feature_title );
					} else{
						$feature_html .= "<div class='eael-mcpt-cell eael-mcpt-feature" . ( $is_hidden ? ' hide' : '' ) . "'></div>";
					}
					
					$column_html["column_{$pack}"][] = $feature_html;
				}
			} else {
				$content = '';
				$default_content  = [ '50', '100', 'Unlimited' ];
				$time_circle_svg  = '<svg class="eael-mcpt-feature-icon e-font-icon-svg e-far-times-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm101.8-262.2L295.6 256l62.2 62.2c4.7 4.7 4.7 12.3 0 17l-22.6 22.6c-4.7 4.7-12.3 4.7-17 0L256 295.6l-62.2 62.2c-4.7 4.7-12.3 4.7-17 0l-22.6-22.6c-4.7-4.7-4.7-12.3 0-17l62.2-62.2-62.2-62.2c-4.7-4.7-4.7-12.3 0-17l22.6-22.6c4.7-4.7 12.3-4.7 17 0l62.2 62.2 62.2-62.2c4.7-4.7 12.3-4.7 17 0l22.6 22.6c4.7 4.7 4.7 12.3 0 17z"></path></svg>';
				$check_circle_svg = '<svg class="eael-mcpt-feature-icon e-font-icon-svg e-far-check-circle" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>';
				
				for ( $cell_count = 0; $cell_count < $max_features ; $cell_count++) {
					$content = '';
					if ( 0 === $cell_count && isset( $default_content[ $key ] ) ) {
						$content = $default_content[ $key ];
					} else if ( $key < 1 && $cell_count > 2 ) {
						$content = $time_circle_svg;
					} else {
						$content = $check_circle_svg;
					}

					$column_html["column_{$pack}"][] = "<div class='eael-mcpt-cell eael-mcpt-feature'>{$content}</div>";
				}
			}

			if( 'modern-layout' === $settings['eael_mcpt_layout'] ) {
				$column_html["column_{$pack}"][] = "<div class='eael-mcpt-cell eael-mcpt-button-cell" . ( $is_hidden ? ' hide' : '' ) . "'> " . $this->get_buy_button_html( $package, $pack_id ) . " </div>";
			}
		}
		
		if ( 'no' !== $settings['eael_mcpt_package_title_effect'] ) {
			$this->add_render_attribute( 'eael_mcpt_wrapper', 'class', 'title-' . $settings['eael_mcpt_package_title_effect'] );
		}
		
		?>
		<div <?php $this->print_render_attribute_string( 'eael_mcpt_wrapper' ) ?>>
			<div class="eael-multicolumn-pricing-table">
				<?php 
				$badge_printed = false;
				echo "<div class='eael-mcpt-columns" . ( $has_featured ? ' has-featured' : '' ) . "'>";
				for( $column = 0; $column <= $packages_count; $column++ ) {
					if ( ! is_array( $column_html["column_{$column}"] ) ) {
						continue;
					}
					$classes = "eael-mcpt-column eael-mcpt-column-{$column}";
					if ( $column === $featured_key ) {
						$classes .= ' eael-mcpt-featured-column';
					}
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo "<div class='{$classes}'>";

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo implode( '', $column_html["column_{$column}"] );
					echo "</div>";
				}
				echo "</div>";
				?>
			</div>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->get_html_for_collaps( $settings, 'desktop' );
			?>
		</div>
		<?php
	}
}