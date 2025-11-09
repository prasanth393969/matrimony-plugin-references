<?php

namespace Essential_Addons_Elementor\Pro\Elements;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;
use Essential_Addons_Elementor\Pro\Classes\Helper;

class Sphere_Photo_Viewer extends Widget_Base {

	public function get_name() {
		return 'eael-sphere-photo-viewer';
	}

	public function get_title() {
		return esc_html__( '360 Degree Photo Viewer', 'essential-addons-elementor' );
	}

	public function get_icon() {
		return 'eaicon-photo-sphere';
	}

	public function get_categories() {
		return [ 'essential-addons-elementor' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 * @since  3.5.2
	 * @access public
	 *
	 */
	public function get_keywords() {
		return [
			'sphere photo viewer',
			'360',
			'360 photo',
			'360 photo viewer',
			'sphere photo',
			'photo viewer',
			'photo',
			'ea',
			'essential addons',
		];
	}

	public function get_custom_help_url() {
		return 'https://essential-addons.com/elementor/docs/ea-360-degree-photo-viewer/';
	}

	protected function register_controls() {
		/**
		 * General Settings
		 */
		$this->start_controls_section(
			'ea_section_spv_content',
			[
				'label' => esc_html__( 'Content', 'essential-addons-elementor' ),
			]
		);

		$this->add_control(
			'ea_spv_image',
			[
				'label'   => __( 'Image', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => [ 'active' => true ],
				'default' => [
					'url' => 'https://app.essential-addons.com/360-photo-viewer/placeholder.jpeg'
				],
				'ai'      => [
					'active' => false
				]
			]
		);

		$this->add_control(
			'ea_spv_caption_switch',
			[
				'label'        => esc_html__( 'Caption', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes'
			]
		);

		$this->add_control(
			'ea_spv_caption',
			[
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
				'default'     => __( 'Enter Your Caption Here', 'essential-addons-elementor' ),
				'placeholder' => __( 'Enter Your Caption Here', 'essential-addons-elementor' ),
				'ai'          => [
					'active' => false
				],
				'condition'   => [
					'ea_spv_caption_switch' => 'yes'
				]
			]
		);

		$this->add_control(
			'ea_spv_description_switch',
			[
				'label'        => esc_html__( 'Description', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes'
			]
		);

		$this->add_control(
			'ea_spv_description',
			[
				'type'        => Controls_Manager::WYSIWYG,
				'label_block' => true,
				'default'     => esc_html__( 'Add a brief overview or interesting facts about the image here. This is a placeholder description. Replace with details about the location, key features, or any relevant information for viewers.', 'essential-addons-elementor' ),
				'ai'          => [ 'active' => false ],
				'dynamic'     => [ 'active' => true ],
				'condition'   => [
					'ea_spv_description_switch' => 'yes'
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'ea_section_spv_options_settings',
			[
				'label' => esc_html__( 'Settings', 'essential-addons-elementor' ),
			]
		);

		$this->add_responsive_control(
			'ea_spv_height',
			[
				'label'      => esc_html__( 'Photo Viewer Height', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'default'    => [
					'size' => 500,
					'unit' => 'px'
				],
				'range'      => [
					'px' => [
						'min'  => 100,
						'max'  => 1000,
						'step' => 10
					],
					'vh' => [
						'min' => 1,
						'max' => 100
					]
				],
				'selectors'  => [
					'{{WRAPPER}} .eael-sphere-photo-wrapper > div' => 'height: {{SIZE}}{{UNIT}}'
				]
			]
		);

		$this->add_control(
			'ea_spv_zoom_lvl',
			[
				'label'   => esc_html__( 'Initial Zoom Level', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 20
				],
				'range'   => [
					'px' => [
						'min'  => 1,
						'max'  => 100,
						'step' => 1
					],
				],
			]
		);

		$this->add_control(
			'ea_spv_fisheye',
			[
				'label'        => __( 'Fisheye Effect', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'essential-addons-elementor' ),
				'label_off'    => __( 'No', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'ea_spv_autorotate_switch',
			[
				'label'        => esc_html__( 'Enable Auto-Rotation', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Off', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before'
			]
		);

		$this->add_control(
			'ea_spv_autorotate_delay',
			[
				'label'     => esc_html__( 'Auto-Rotation Delay (ms)', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 100
				],
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 5000,
						'step' => 100
					],
				],
				'condition' => [
					'ea_spv_autorotate_switch' => 'yes'
				]
			]
		);

		$this->add_control(
			'ea_spv_autorotate_speed',
			[
				'label'     => esc_html__( 'Auto-Rotation Speed', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => .2
				],
				'range'     => [
					'px' => [
						'min'  => .01,
						'max'  => 2,
						'step' => .01
					],
				],
				'condition' => [
					'ea_spv_autorotate_switch' => 'yes'
				]
			]
		);

		$this->add_control(
			'ea_spv_autorotate_pitch',
			[
				'label'     => esc_html__( 'Auto-Rotation Pitch', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0
				],
				'range'     => [
					'px' => [
						'min'  => - 90,
						'max'  => 90,
						'step' => 1
					],
				],
				'condition' => [
					'ea_spv_autorotate_switch' => 'yes'
				]
			]
		);

		$this->add_control(
			'ea_spv_autorotate_pan',
			[
				'label'     => esc_html__( 'Pan Correction', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0
				],
				'range'     => [
					'px' => [
						'min'  => - 1,
						'max'  => 1,
						'step' => 0.01
					],
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'ea_spv_autorotate_tilt',
			[
				'label'   => esc_html__( 'Tilt Correction', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0
				],
				'range'   => [
					'px' => [
						'min'  => - 1,
						'max'  => 1,
						'step' => 0.01
					],
				],
			]
		);

		$this->add_control(
			'ea_spv_autorotate_roll',
			[
				'label'   => esc_html__( 'Roll Axis Correction', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0
				],
				'range'   => [
					'px' => [
						'min'  => - 1,
						'max'  => 1,
						'step' => 0.01
					],
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'ea_section_spv_markers_settings',
			[
				'label' => esc_html__( 'Markers', 'essential-addons-elementor' ),
			]
		);

		$this->add_control(
			'ea_spv_markers_switch',
			[
				'label'        => esc_html__( 'Show Markers', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before'
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'ea_spv_markers_tooltip',
			[
				'label'       => esc_html__( 'Tooltip', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Enter Marker Title Here', 'essential-addons-elementor' ),
				'placeholder' => esc_html__( 'Enter Marker Title Here', 'essential-addons-elementor' ),
				'dynamic'     => [ 'active' => true ],
				'ai'          => [
					'active' => false,
				],
			]
		);

		$repeater->add_control(
			'ea_spv_markers_content',
			[
				'label'       => esc_html__( 'Content', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Detailed content goes here. Provide insights or interesting facts about this marker point.', 'essential-addons-elementor' ),
				'placeholder' => esc_html__( 'Brief Description for Tooltip (e.g., Click for more details!)', 'essential-addons-elementor' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'left_position',
			[
				'label'   => __( 'X Position', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => - 180,
						'max'  => 180,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
			]
		);

		$repeater->add_control(
			'top_position',
			[
				'label'   => __( 'Y Position', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => - 90,
						'max'  => 90,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
			]
		);

		$repeater->add_control(
			'ea_spv_markers_img',
			[
				'label'   => esc_html__( 'Image', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => EAEL_PRO_PLUGIN_URL . 'assets/front-end/img/marker.svg',
				],
				'ai'      => [
					'active' => false,
				],
			]
		);

		$repeater->add_control(
			'custom_dimension',
			[
				'label'       => esc_html__( 'Image Dimension', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::IMAGE_DIMENSIONS,
				'description' => esc_html__( 'Crop the original image size to any custom size. Set custom width or height to keep the original size ratio.', 'essential-addons-elementor' ),
				'default'     => [
					'width'  => '32',
					'height' => '32',
				],
			]
		);

		$this->add_control(
			'ea_spv_markers_list',
			[
				'label'       => esc_html__( 'Marker Pointers', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'default'     => [
					[
						'ea_spv_markers_tooltip' => esc_html__( 'Marker 1', 'essential-addons-elementor' ),
						'ea_spv_markers_content' => esc_html__( 'Detailed content goes here. Provide insights or interesting facts about this marker point.', 'essential-addons-elementor' ),
					],
				],
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{ ea_spv_markers_tooltip }}',
				'condition'   => [
					'ea_spv_markers_switch' => 'yes',
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'ea_section_spv_navbar_settings',
			[
				'label' => esc_html__( 'Navigation Bar', 'essential-addons-elementor' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'type',
			[
				'label'   => esc_html__( 'Navigator type', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''            => esc_html__( 'Select Type', 'essential-addons-elementor' ),
					'autorotate'  => esc_html__( 'autorotate', 'essential-addons-elementor' ),
					'zoomOut'     => esc_html__( 'zoomOut', 'essential-addons-elementor' ),
					'zoomRange'   => esc_html__( 'zoomRange', 'essential-addons-elementor' ),
					'zoomIn'      => esc_html__( 'zoomIn', 'essential-addons-elementor' ),
					'zoom'        => esc_html__( 'zoom', 'essential-addons-elementor' ),
					'moveLeft'    => esc_html__( 'moveLeft', 'essential-addons-elementor' ),
					'moveRight'   => esc_html__( 'moveRight', 'essential-addons-elementor' ),
					'moveUp'      => esc_html__( 'moveUp', 'essential-addons-elementor' ),
					'moveDown'    => esc_html__( 'moveDown', 'essential-addons-elementor' ),
					'move'        => esc_html__( 'move', 'essential-addons-elementor' ),
					'download'    => esc_html__( 'download', 'essential-addons-elementor' ),
					'description' => esc_html__( 'description', 'essential-addons-elementor' ),
					'caption'     => esc_html__( 'caption', 'essential-addons-elementor' ),
					'fullscreen'  => esc_html__( 'fullscreen', 'essential-addons-elementor' ),
					'markers'     => esc_html__( 'markers', 'essential-addons-elementor' ),
					'markersList' => esc_html__( 'markersList', 'essential-addons-elementor' ),
				],
			]
		);

		$this->add_control(
			'ea_spv_navbar',
			[
				'label'       => esc_html__( 'Items', 'essential-addons-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'default'     => [
					[
						'type' => 'autorotate',
					],
					[
						'type' => 'zoomOut',
					],
					[
						'type' => 'zoomRange',
					],
					[
						'type' => 'zoomIn',
					],
					[
						'type' => 'moveLeft',
					],
					[
						'type' => 'moveRight',
					],
					[
						'type' => 'moveUp',
					],
					[
						'type' => 'moveDown',
					],
					[
						'type' => 'download',
					],
					[
						'type' => 'description',
					],
					[
						'type' => 'markers',
					],
					[
						'type' => 'markersList',
					],
					[
						'type' => 'caption',
					],
					[
						'type' => 'fullscreen',
					],
				],
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{ type }}'
			]
		);

		$this->end_controls_section();

		/**
		 * -------------------------------------------
		 * Tab Style General Style
		 * -------------------------------------------
		 */
		$this->start_controls_section(
			'ea_section_spv_general_style',
			[
				'label' => esc_html__( 'Navigation Bar', 'essential-addons-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'ea_spv_navbar_visibility',
			[
				'label'     => __( 'Visibility', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'none' => [
						'title' => __( 'Hide', 'essential-addons-elementor' ),
						'icon'  => 'eaicon-eye-slash-solid',
					],
					'flex' => [
						'title' => __( 'Show', 'essential-addons-elementor' ),
						'icon'  => 'eaicon-eye-solid',
					],
				],
				'default'   => 'flex',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .psv-navbar' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'ea_spv_navbar_alignment',
			[
				'label'     => __( 'Alignment', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [
						'title' => __( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'     => [
						'title' => __( 'Center', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end'   => [
						'title' => __( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'flex-start',
				'toggle'    => false,
				'condition' => [
					'ea_spv_caption_switch!' => 'yes'
				],
				'selectors' => [
					'{{WRAPPER}} .psv-navbar' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'ea_spv_navbar_button_position_left',
			[
				'label'     => esc_html__( 'X Position', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0
				],
				'range'     => [
					'px' => [
						'min'  => - 100,
						'max'  => 100,
						'step' => 1
					],
				],
				'selectors' => [
					'{{WRAPPER}} .psv-navbar' => 'left: {{SIZE}}px;',
				]
			]
		);

		$this->add_control(
			'ea_spv_navbar_button_position_top',
			[
				'label'     => esc_html__( 'Y Position', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0
				],
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1
					],
				],
				'selectors' => [
					'{{WRAPPER}} .psv-navbar' => 'bottom: {{SIZE}}px;',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'ea_spv_nav_background',
				'types'     => [ 'classic', 'gradient' ],
				'selector'  => '{{WRAPPER}} .psv-navbar',
				'condition' => [
					'ea_spv_navbar_visibility!' => 'none'
				]
			]
		);

		$this->add_responsive_control(
			'ea_spv_navbar_padding',
			[
				'label'              => __( 'Padding', 'essential-addons-elementor' ),
				'type'               => Controls_Manager::DIMENSIONS,
				'size_units'         => [ 'px' ],
				'allowed_dimensions' => [ 'left', 'right' ],
				'default'            => [
					'right'    => '0',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'          => [
					'{{WRAPPER}} .psv-navbar' => 'padding: 0 {{RIGHT}}{{UNIT}} 0 {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'ea_spv_nav_icon_heading',
			[
				'label'     => esc_html__( 'Navigation Items', 'essential-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'ea_spv_caption_switch' => 'yes',
				]
			]
		);

		$this->add_control(
			'ea_spv_nav_icon_color',
			[
				'label'     => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .psv-navbar .psv-button svg path'                                => 'stroke: {{VALUE}}',
					'{{WRAPPER}} .psv-navbar .psv-button .psv-zoom-range-handle, 
					{{WRAPPER}} .psv-navbar .psv-button .psv-zoom-range-line' => 'background: {{VALUE}}',
				],
				'condition' => [
					'ea_spv_navbar_visibility!' => 'none'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'ea_spv_nav_icon_background',
				'fields_options' => [
					'background' => [
						'label' => esc_html__( 'Icon Background', 'essential-addons-elementor' ),
					]
				],
				'types'          => [ 'classic' ],
				'selector'       => '{{WRAPPER}} .psv-navbar .psv-button',
				'condition'      => [
					'ea_spv_navbar_visibility!' => 'none'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'ea_spv_nav_icon_active_background',
				'fields_options' => [
					'background' => [
						'label' => esc_html__( 'Active Icon Background', 'essential-addons-elementor' ),
					]
				],
				'types'          => [ 'classic' ],
				'selector'       => '{{WRAPPER}} .psv-navbar .psv-button.psv-button--active',
				'condition'      => [
					'ea_spv_navbar_visibility!' => 'none'
				]
			]
		);

		$this->add_control(
			'ea_spv_nav_caption_heading',
			[
				'label'     => esc_html__( 'Caption', 'essential-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'ea_spv_caption_switch' => 'yes',
				]
			]
		);

		$this->add_responsive_control(
			'ea_spv_navbar_caption_alignment',
			[
				'label'     => __( 'Alignment', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .psv-navbar .psv-button.psv-caption' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'ea_spv_caption_switch' => 'yes',
				]
			]
		);

		$this->add_control(
			'ea_spv_nav_title_color',
			[
				'label'     => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .psv-navbar .psv-caption-content' => 'color: {{VALUE}}',
				],
				'condition' => [
					'ea_spv_caption_switch' => 'yes',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'ea_spv_nav_caption_background',
				'types'     => [ 'classic' ],
				'selector'  => '{{WRAPPER}} .psv-navbar .psv-button.psv-caption',
				'condition' => [
					'ea_spv_caption_switch' => 'yes'
				]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'ea_spv_nav_title_typography',
				'selector'  => '{{WRAPPER}} .psv-navbar .psv-caption-content',
				'condition' => [
					'ea_spv_caption_switch' => 'yes',
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'ea_section_spv_marker_style',
			[
				'label'     => esc_html__( 'Markers', 'essential-addons-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'ea_spv_markers_switch' => 'yes',
				]
			]
		);

		$this->add_control(
			'ea_spv_markers_hoverscale_switch',
			[
				'label'        => esc_html__( 'Hover Scale', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Off', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'ea_spv_markers_hoverscale_amount',
			[
				'label'   => esc_html__( 'Scale', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 2
				],
				'range'   => [
					'px' => [
						'min'  => 0.1,
						'max'  => 5,
						'step' => 0.1
					],
				]
			]
		);

		$this->add_control(
			'ea_spv_markers_hoverscale_duration',
			[
				'label'   => esc_html__( 'Duration', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 300
				],
				'range'   => [
					'px' => [
						'min'  => 100,
						'max'  => 2000,
						'step' => 100
					],
				]
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'ea_section_spv_panel_style',
			[
				'label'      => esc_html__( 'Panel Content', 'essential-addons-elementor' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'  => "ea_spv_markers_switch",
							'value' => 'yes',
						],
						[
							'name'  => 'ea_spv_description_switch',
							'value' => 'yes',
						],
					],
				]
			]
		);

		$this->add_responsive_control(
			'ea_spv_panel_content_alignment',
			[
				'label'     => __( 'Alignment', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => __( 'Left', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => __( 'Right', 'essential-addons-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'toggle'    => false,
				'selectors' => [
					'{{WRAPPER}} .psv-panel .psv-panel-content' => 'text-align: {{VALUE}};',
				]
			]
		);

		$this->add_control(
			'ea_spv_panel_content_color',
			[
				'label'     => esc_html__( 'Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .psv-panel .psv-panel-content' => 'color: {{VALUE}}',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'ea_spv_panel_content_background',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .psv-panel .psv-panel-content',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'ea_spv_panel_content_typography',
				'selector' => '{{WRAPPER}} .psv-panel .psv-panel-content'
			]
		);

		$this->add_responsive_control(
			'ea_spv_panel_content_padding',
			[
				'label'      => __( 'Padding', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'      => '1',
					'right'    => '1',
					'bottom'   => '1',
					'left'     => '1',
					'unit'     => 'em',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .psv-panel .psv-panel-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings        = $this->get_settings_for_display();
		$container_id    = "eael-psv-{$this->get_id()}";
		$sphere_settings = [
			'caption'          => $this->parse_text_editor( $settings['ea_spv_caption'] ?? '' ),
			'panorama'         => empty( $settings['ea_spv_image']['id'] ) ? $settings['ea_spv_image']['url'] : wp_get_original_image_url( $settings['ea_spv_image']['id'] ),
			'container'        => $container_id,
			'description'      => $this->parse_text_editor( $settings['ea_spv_description'] ?? '' ),
			'defaultZoomLvl'   => empty( $settings['ea_spv_zoom_lvl']['size'] ) ? 20 : $settings['ea_spv_zoom_lvl']['size'],
			'fisheye'          => $settings['ea_spv_fisheye'] === 'yes',
			'sphereCorrection' => [
				'pan'  => floatval( $settings['ea_spv_autorotate_pan']['size'] ) * pi(),
				'tilt' => floatval( $settings['ea_spv_autorotate_tilt']['size'] ) * pi() / 2,
				'roll' => floatval( $settings['ea_spv_autorotate_roll']['size'] ) * pi()
			]
		];

		if ( $settings['ea_spv_autorotate_switch'] === 'yes' ) {
			$sphere_settings['plugins'][][0] = [
				'autostartDelay'  => empty( $settings['ea_spv_autorotate_delay']['size'] ) ? 100 : $settings['ea_spv_autorotate_delay']['size'],
				'autorotatePitch' => empty( $settings['ea_spv_autorotate_pitch']['size'] ) ? '5deg' : $settings['ea_spv_autorotate_pitch']['size'] . 'deg',
				'autorotateSpeed' => empty( $settings['ea_spv_autorotate_speed']['size'] ) ? .2 : $settings['ea_spv_autorotate_speed']['size']
			];
		}

		if ( $settings['ea_spv_markers_switch'] === 'yes' ) {
			$markers = [];
			$uid     = wp_rand();
			foreach ( $settings['ea_spv_markers_list'] as $key => $marker ) {
				$markers[] = [
					'id'       => "{$uid}_{$key}",
					'position' => [ 'yaw' => $marker['left_position']['size'] . 'deg', 'pitch' => $marker['top_position']['size'] . 'deg' ],
					'size'     => [ 'width' => $marker['custom_dimension']['width'], 'height' => $marker['custom_dimension']['height'] ],
					'anchor'   => 'bottom center',
					'image'    => empty( $marker['ea_spv_markers_img']['url'] ) ? '' : $marker['ea_spv_markers_img']['url'],
					'tooltip'  => esc_html( $marker['ea_spv_markers_tooltip'] ),
					'content'  => esc_html( $marker['ea_spv_markers_content'] )
				];
			}

			$defaultHoverScale = $settings['ea_spv_markers_hoverscale_switch'] === 'yes' ?
				[
					'amount'   => $settings['ea_spv_markers_hoverscale_amount']['size'],
					'duration' => $settings['ea_spv_markers_hoverscale_duration']['size']
				] : false;

			$sphere_settings['plugins'][][0] = [
				'markers'           => $markers,
				'defaultHoverScale' => $defaultHoverScale
			];
		}

		$nav_items = [];
		foreach ( $settings['ea_spv_navbar'] as $key => $nav ) {
			if ( $nav['type'] === 'caption' && $settings['ea_spv_caption_switch'] !== 'yes' ) {
				continue;
			}
			$nav_items[] = $nav['type'];
		}
		$sphere_settings['navbar'] = $nav_items;

		$sphere_settings = json_encode( $sphere_settings );

		$this->add_render_attribute( [
			'sphere-wrapper' => [
				'data-settings' => $sphere_settings
			]
		] )
		?>
		<div class="eael-sphere-photo-wrapper" <?php $this->print_render_attribute_string( 'sphere-wrapper' ) ?>>
			<div id="<?php echo esc_attr( $container_id ); ?>"></div>
		</div>
		<?php
	}
}
