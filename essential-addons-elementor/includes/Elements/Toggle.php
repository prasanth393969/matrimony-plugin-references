<?php
namespace Essential_Addons_Elementor\Pro\Elements;

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;
use \Elementor\Utils;
use \Elementor\Widget_Base;
use \Elementor\Plugin;
use \Elementor\Control_Media;
use Elementor\Icons_Manager;

use \Essential_Addons_Elementor\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Toggle Widget
 */
class Toggle extends Widget_Base {
    /**
     * Retrieve toggle widget name.
     *
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'eael-toggle';
    }
    
    /**
     * Retrieve toggle widget title.
     *
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'Toggle', 'essential-addons-elementor' );
    }
    
    /**
     * Retrieve the list of categories the toggle widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'essential-addons-elementor' ];
    }
    
    public function get_keywords()
    {
        return [
            'toggle',
            'ea toggle',
            'ea content toggle',
            'content toggle',
            'content switcher',
            'switcher',
            'ea switcher',
            'ea',
            'essential addons',
            'Liquid Glass Effect',
            'Glassmorphism',
            'Frost Effect',
        ];
    }

    protected function is_dynamic_content():bool {
        if( Plugin::$instance->editor->is_edit_mode() ) {
            return false;
        }

		$primary_content_type   = $this->get_settings('primary_content_type');
		$secondary_content_type = $this->get_settings('secondary_content_type');
		$is_dynamic_content = 'template' === $primary_content_type || 'template' === $secondary_content_type;

        return $is_dynamic_content;
    }

    public function has_widget_inner_wrapper(): bool {
        return ! Helper::eael_e_optimized_markup();
    }
    
    public function get_custom_help_url()
    {
        return 'https://essential-addons.com/elementor/docs/content-toggle/';
    }
    
    /**
     * Retrieve toggle widget icon.
     *
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eaicon-content-toggle';
    }
    
    /**
     * Register toggle widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @access protected
     */
    protected function register_controls() {
        
        /**       General Settings     */
        $this->start_controls_section(
            'eael_toggle_general_settings',
            [
                'label' => esc_html__( 'General Settings', 'essential-addons-elementor' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $image_dir_url = EAEL_PRO_PLUGIN_URL . 'assets/admin/images/layout-previews/toggle-';
        $this->add_control(
			'eael_toggle_effects_style',
			[
				'label'   => esc_html__( 'Effects Style', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'default',
                'options'     => [
					'default' => [
						'title' => esc_html__( 'Default', 'essential-addons-elementor' ),
						'image'  => $image_dir_url . 'default.png',
					],
					'glossy' => [
						'title' => esc_html__( 'Liquid Glass', 'essential-addons-elementor' ),
						'image'  => $image_dir_url . 'glossy.png',
					],
					'grasshopper' => [
						'title' => esc_html__( 'Crystalmorphism', 'essential-addons-elementor' ),
						'image'  => $image_dir_url . 'grasshopper.png',
					],
				],
				'label_block' => true,
                'toggle'      => false,
                'image_choose'=> true,
			]
		);

        $this->end_controls_section();

        /*-----------------------------------------------------------------------------------*/
        /*	CONTENT TAB
        /*-----------------------------------------------------------------------------------*/
        
        /**
         * Content Tab: Primary
         */
        $this->start_controls_section(
            'section_primary',
            [
                'label'                 => __( 'Primary', 'essential-addons-elementor' ),
            ]
        );
        
        $this->add_control(
            'primary_label',
            [
                'label'                 => __( 'Label', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::TEXT,
                'dynamic'	            => [ 'active' => true ],
                'default'               => __( 'Light', 'essential-addons-elementor' ),
                'ai' => [
					'active' => false,
				],
            ]
        );
        
        $this->add_control(
            'primary_content_type',
            [
                'label'                 => __( 'Content Type', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::SELECT,
                'options'               => [
                    'image'         => __( 'Image', 'essential-addons-elementor' ),
                    'content'       => __( 'Content', 'essential-addons-elementor' ),
                    'template'      => __( 'Saved Templates', 'essential-addons-elementor' ),
                ],
                'default'               => 'content',
            ]
        );
        
        $this->add_control(
            'primary_templates',
	        [
		        'label'       => __( 'Choose Template', 'essential-addons-elementor' ),
		        'type'        => 'eael-select2',
		        'source_name' => 'post_type',
		        'source_type' => 'elementor_library',
		        'label_block' => true,
		        'condition'   => [
			        'primary_content_type' => 'template',
		        ],
	        ]
        );
        
        $this->add_control(
            'primary_content',
            [
                'label'                 => __( 'Content', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::WYSIWYG,
                'default'               => __( 'Primary Content', 'essential-addons-elementor' ),
                'condition'             => [
                    'primary_content_type'      => 'content',
                ],
            ]
        );
        
        $this->add_control(
            'primary_image',
            [
                'label'                 => __( 'Image', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::MEDIA,
                'default'               => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition'             => [
                    'primary_content_type'      => 'image',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
        
        $this->end_controls_section();
        
        /**
         * Content Tab: Secondary
         */
        $this->start_controls_section(
            'section_secondary',
            [
                'label'                 => __( 'Secondary', 'essential-addons-elementor' ),
            ]
        );
        
        $this->add_control(
            'secondary_label',
            [
                'label'                 => __( 'Label', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::TEXT,
                'dynamic'	            => [ 'active' => true ],
                'default'               => __( 'Dark', 'essential-addons-elementor' ),
                'ai' => [
					'active' => false,
				],
            ]
        );
        
        $this->add_control(
            'secondary_content_type',
            [
                'label'                 => __( 'Content Type', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::SELECT,
                'options'               => [
                    'image'         => __( 'Image', 'essential-addons-elementor' ),
                    'content'       => __( 'Content', 'essential-addons-elementor' ),
                    'template'      => __( 'Saved Templates', 'essential-addons-elementor' ),
                ],
                'default'               => 'content',
            ]
        );
        
        $this->add_control(
            'secondary_templates',
	        [
		        'label'       => __( 'Choose Template', 'essential-addons-elementor' ),
		        'type'        => 'eael-select2',
		        'source_name' => 'post_type',
		        'source_type' => 'elementor_library',
		        'label_block' => true,
		        'condition'   => [
			        'secondary_content_type' => 'template',
		        ],
	        ]
        );
        
        $this->add_control(
            'secondary_content',
            [
                'label'                 => __( 'Content', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::WYSIWYG,
                'default'               => __( 'Secondary Content', 'essential-addons-elementor' ),
                'condition'             => [
                    'secondary_content_type'      => 'content',
                ],
            ]
        );
        
        $this->add_control(
            'secondary_image',
            [
                'label'                 => __( 'Image', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::MEDIA,
                'default'               => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'condition'             => [
                    'secondary_content_type'      => 'image',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
        
        $this->end_controls_section();
        
        /**
         * Style Tab: Overlay
         */
        $this->start_controls_section(
            'section_toggle_switch_style',
            [
                'label'             => __( 'Switch', 'essential-addons-elementor' ),
                'tab'               => Controls_Manager::TAB_STYLE,
                'condition'         => [
                    'eael_toggle_effects_style' => 'default',
                ],
            ]
        );
        
        $this->add_control(
            'toggle_switch_alignment',
            [
                'label'                 => __( 'Alignment', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::CHOOSE,
                'default'               => 'center',
                'options'               => [
                    'left'          => [
                        'title'     => __( 'Left', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-left',
                    ],
                    'center'        => [
                        'title'     => __( 'Center', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-center',
                    ],
                    'right'         => [
                        'title'     => __( 'Right', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class'          => 'eael-toggle-',
                'frontend_available'    => true,
            ]
        );
        
        $this->add_control(
            'switch_style',
            [
                'label'                 => __( 'Switch Style', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::SELECT,
                'options'               => [
                    'round'         => __( 'Round', 'essential-addons-elementor' ),
                    'rectangle'     => __( 'Rectangle', 'essential-addons-elementor' ),
                ],
                'default'               => 'round',
            ]
        );
        
        $this->add_responsive_control(
            'toggle_switch_size',
            [
                'label'                 => __( 'Switch Size', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::SLIDER,
                'default'               => [
                    'size' => 26,
                    'unit' => 'px',
                ],
                'size_units'            => [ 'px' ],
                'range'                 => [
                    'px'   => [
                        'min' => 15,
                        'max' => 60,
                    ],
                ],
                'tablet_default'        => [
                    'unit' => 'px',
                ],
                'mobile_default'        => [
                    'unit' => 'px',
                ],
                'selectors'             => [
                    '{{WRAPPER}} .eael-toggle-switch-container' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'toggle_switch_spacing',
            [
                'label'                 => __( 'Headings Spacing', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::SLIDER,
                'default'               => [
                    'size' => 15,
                    'unit' => 'px',
                ],
                'size_units'            => [ 'px', '%' ],
                'range'                 => [
                    'px'   => [
                        'max' => 80,
                    ],
                ],
                'tablet_default'        => [
                    'unit' => 'px',
                ],
                'mobile_default'        => [
                    'unit' => 'px',
                ],
                'selectors'             => [
                    '{{WRAPPER}} .eael-toggle-switch-container' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'toggle_switch_gap',
            [
                'label'                 => __( 'Margin Bottom', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::SLIDER,
                'default'               => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'size_units'            => [ 'px', '%' ],
                'range'                 => [
                    'px'   => [
                        'max' => 80,
                    ],
                ],
                'tablet_default'        => [
                    'unit' => 'px',
                ],
                'mobile_default'        => [
                    'unit' => 'px',
                ],
                'selectors'             => [
                    '{{WRAPPER}} .eael-toggle-switch-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->start_controls_tabs( 'tabs_switch' );
        
        $this->start_controls_tab(
            'tab_switch_primary',
            [
                'label'             => __( 'Primary', 'essential-addons-elementor' ),
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'              => 'toggle_switch_primary_background',
                'types'             => [ 'classic', 'gradient' ],
                'selector'          => '{{WRAPPER}} .eael-toggle-slider',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'                  => 'toggle_switch_primary_border',
                'label'                 => __( 'Border', 'essential-addons-elementor' ),
                'placeholder'           => '1px',
                'default'               => '1px',
                'selector'              => '{{WRAPPER}} .eael-toggle-switch-container',
            ]
        );
        
        $this->add_control(
            'toggle_switch_primary_border_radius',
            [
                'label'                 => __( 'Border Radius', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::DIMENSIONS,
                'size_units'            => [ 'px', '%' ],
                'selectors'             => [
                    '{{WRAPPER}} .eael-toggle-switch-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'tab_switch_secondary',
            [
                'label'             => __( 'Secondary', 'essential-addons-elementor' ),
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'              => 'toggle_switch_secondary_background',
                'types'             => [ 'classic', 'gradient' ],
                'selector'          => '{{WRAPPER}} .eael-toggle-switch-on .eael-toggle-slider',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'                  => 'toggle_switch_secondary_border',
                'label'                 => __( 'Border', 'essential-addons-elementor' ),
                'placeholder'           => '1px',
                'default'               => '1px',
                'selector'              => '{{WRAPPER}} .eael-toggle-switch-container.eael-toggle-switch-on',
            ]
        );
        
        $this->add_control(
            'toggle_switch_secondary_border_radius',
            [
                'label'                 => __( 'Border Radius', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::DIMENSIONS,
                'size_units'            => [ 'px', '%' ],
                'selectors'             => [
                    '{{WRAPPER}} .eael-toggle-switch-container.eael-toggle-switch-on' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_control(
            'switch_controller_heading',
            [
                'label'                 => __( 'Controller', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::HEADING,
                'separator'             => 'before',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'              => 'toggle_controller_background',
                'types'             => [ 'classic', 'gradient' ],
                'selector'          => '{{WRAPPER}} .eael-toggle-slider::before',
            ]
        );
        
        $this->add_control(
            'toggle_controller_border_radius',
            [
                'label'                 => __( 'Border Radius', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::DIMENSIONS,
                'size_units'            => [ 'px', '%' ],
                'selectors'             => [
                    '{{WRAPPER}} .eael-toggle-slider::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();

        /**
         * Style Tab: Switch (Grasshopper)
         */
        $this->eael_grasshopper_toggle_switch_style();

        /**
         * Style Tab: Switch (Glossy)
         */
        $this->eael_glossy_toggle_switch_style();

        /**
         * Style Tab: Label
         */
        $this->start_controls_section(
            'section_label_style',
            [
                'label'             => __( 'Label', 'essential-addons-elementor' ),
                'tab'               => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'label_horizontal_position',
            [
                'label'                 => __( 'Position', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::CHOOSE,
                'label_block'           => false,
                'default'               => 'middle',
                'options'               => [
                    'top'          => [
                        'title'    => __( 'Top', 'essential-addons-elementor' ),
                        'icon'     => 'eicon-v-align-top',
                    ],
                    'middle'       => [
                        'title'    => __( 'Middle', 'essential-addons-elementor' ),
                        'icon'     => 'eicon-v-align-middle',
                    ],
                    'bottom'       => [
                        'title'    => __( 'Bottom', 'essential-addons-elementor' ),
                        'icon'     => 'eicon-v-align-bottom',
                    ],
                ],
                'selectors_dictionary'  => [
                    'top'      => 'flex-start',
                    'middle'   => 'center',
                    'bottom'   => 'flex-end',
                ],
                'selectors'             => [
                    '{{WRAPPER}} .eael-toggle-switch-inner' => 'align-items: {{VALUE}}',
                ],
            ]
        );
        
        $this->start_controls_tabs( 'tabs_label_style' );
        
        $this->start_controls_tab(
            'tab_label_primary',
            [
                'label'             => __( 'Primary', 'essential-addons-elementor' ),
            ]
        );
        
        $this->add_control(
            'label_text_color_primary',
            [
                'label'             => __( 'Text Color', 'essential-addons-elementor' ),
                'type'              => Controls_Manager::COLOR,
                'default'           => '',
                'selectors'         => [
                    '{{WRAPPER}} .eael-primary-toggle-label' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'label_active_text_color_primary',
            [
                'label'             => __( 'Active Text Color', 'essential-addons-elementor' ),
                'type'              => Controls_Manager::COLOR,
                'default'           => '',
                'selectors'         => [
                    '{{WRAPPER}} .eael-primary-toggle-label.active' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'              => 'label_typography_primary',
                'label'             => __( 'Typography', 'essential-addons-elementor' ),
                'global' => [
	                'default' => Global_Typography::TYPOGRAPHY_ACCENT
                ],
                'selector'          => '{{WRAPPER}} .eael-primary-toggle-label',
                'separator'         => 'before',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'tab_label_secondary',
            [
                'label'             => __( 'Secondary', 'essential-addons-elementor' ),
            ]
        );
        
        $this->add_control(
            'label_text_color_secondary',
            [
                'label'             => __( 'Text Color', 'essential-addons-elementor' ),
                'type'              => Controls_Manager::COLOR,
                'default'           => '',
                'selectors'         => [
                    '{{WRAPPER}} .eael-secondary-toggle-label' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'label_active_text_color_secondary',
            [
                'label'             => __( 'Active Text Color', 'essential-addons-elementor' ),
                'type'              => Controls_Manager::COLOR,
                'default'           => '',
                'selectors'         => [
                    '{{WRAPPER}} .eael-secondary-toggle-label.active' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'              => 'label_typography_secondary',
                'label'             => __( 'Typography', 'essential-addons-elementor' ),
                'global' => [
	                'default' => Global_Typography::TYPOGRAPHY_ACCENT
                ],
                'selector'          => '{{WRAPPER}} .eael-secondary-toggle-label',
                'separator'         => 'before',
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->end_controls_section();
        
        /**
         * Style Tab: Content
         */
        $this->start_controls_section(
            'section_content_style',
            [
                'label'             => __( 'Content', 'essential-addons-elementor' ),
                'tab'               => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'content_alignment',
            [
                'label'                 => __( 'Alignment', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::CHOOSE,
                'default'               => 'center',
                'options'               => [
                    'left'          => [
                        'title'     => __( 'Left', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-left',
                    ],
                    'center'        => [
                        'title'     => __( 'Center', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-center',
                    ],
                    'right'         => [
                        'title'     => __( 'Right', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-right',
                    ],
                ],
                'selectors'         => [
                    '{{WRAPPER}} .eael-toggle-content-wrap' => 'text-align: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'content_text_color',
            [
                'label'             => __( 'Text Color', 'essential-addons-elementor' ),
                'type'              => Controls_Manager::COLOR,
                'default'           => '',
                'selectors'         => [
                    '{{WRAPPER}} .eael-toggle-content-wrap' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'              => 'content_typography',
                'label'             => __( 'Typography', 'essential-addons-elementor' ),
                'global' => [
	                'default' => Global_Typography::TYPOGRAPHY_ACCENT
                ],
                'selector'          => '{{WRAPPER}} .eael-toggle-content-wrap',
            ]
        );
        
        $this->end_controls_section();
        
    }

    // Common useable functions
    public function eael_toggle_icon_controller( $control_id, $condiiton ) {
        $this->add_control(
			$control_id,
			[
				'label'   => esc_html__( 'Icon', 'essential-addons-elementor' ),
				'type'    => Controls_Manager::ICONS,
                'condition' => [
					$condiiton => 'yes',
				],
			]
		);
    }

    // Grasshopper Toggle Switch Style
    public function eael_grasshopper_toggle_switch_style() {
        $this->start_controls_section(
            'eael_grasshopper_toggle_switch_style',
            [
                'label'             => __( 'Switch', 'essential-addons-elementor' ),
                'tab'               => Controls_Manager::TAB_STYLE,
                'condition'         => [
                    'eael_toggle_effects_style' => 'grasshopper',
                ],
            ]
        );

        $this->add_control(
            'eael_grasshopper_toggle_switch_alignment',
            [
                'label'                 => __( 'Alignment', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::CHOOSE,
                'default'               => 'center',
                'options'               => [
                    'left'          => [
                        'title'     => __( 'Left', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-left',
                    ],
                    'center'        => [
                        'title'     => __( 'Center', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-center',
                    ],
                    'right'         => [
                        'title'     => __( 'Right', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class'          => 'eael-toggle-',
                'frontend_available'    => true,
            ]
        );

        $this->add_control(
			'eael_grasshopper_toggle_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default' => '#80808087',
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
			'eael_grasshopper_toggle_backdrop',
			[
				'label' => esc_html__( 'Backdrop Filter', 'essential-addons-elementor' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch' => 'backdrop-filter: blur({{SIZE}}{{UNIT}}) url(#switcher);',
				],
			]
		);

        $this->add_responsive_control(
			'eael_grasshopper_toggle_width',
			[
				'label'      => esc_html__( 'Width', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 210,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_responsive_control(
			'eael_grasshopper_toggle_height',
			[
				'label'      => esc_html__( 'Height', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 67,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-toggle-flip-switch .eael-card-face' => 'height: {{SIZE}}{{UNIT}};',
                    
				],
			]
		);

        $this->add_responsive_control(
            'eael_grasshopper_toggle_switch_gap',
            [
                'label'     => __( 'Margin Bottom', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::SLIDER,
                'default'   => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [
                        'max' => 80,
                    ],
                ],
                'tablet_default' => [
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toggle-switch-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
			'eael_grasshopper_toggle_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem', 'custom' ],
				'default'    => [
					'top'    => 30,
					'right'  => 30,
					'bottom' => 30,
					'left'   => 30,
					'unit'   => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->start_controls_tabs(
			'eael_grasshopper_toggle_style_tabs'
		);

		$this->start_controls_tab(
			'eael_grasshopper_toggle_primary_tab',
			[
				'label' => esc_html__( 'Primary', 'essential-addons-elementor' ),
			]
		);

        $this->add_control(
			'eael_grasshopper_toggle_primary_icon_show',
			[
				'label'        => esc_html__( 'Show Icon', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

        $this->eael_toggle_icon_controller( 'eael_grasshopper_toggle_primary_icon', 'eael_grasshopper_toggle_primary_icon_show' );

        $this->add_control(
			'eael_grasshopper_toggle_primary_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default'   => '#FFD203',
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="1"] svg path, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="2"] svg path' => 'fill: {{VALUE}}',
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="1"] i, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="2"] i' => 'color: {{VALUE}}',
				],
                'condition' => [
					'eael_grasshopper_toggle_primary_icon_show' => 'yes',
				],
			]
		);

        $this->add_control(
			'eael_grasshopper_toggle_primary_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 35,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="1"] svg, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="2"] svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="1"] i, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="2"] i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
                'condition' => [
					'eael_grasshopper_toggle_primary_icon_show' => 'yes',
				],
			]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
			'eael_grasshopper_toggle_secondary_tab',
			[
				'label' => esc_html__( 'Secondary', 'essential-addons-elementor' ),
			]
		);

        $this->add_control(
			'eael_grasshopper_toggle_secondary_icon_show',
			[
				'label'        => esc_html__( 'Show Icon', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

        $this->eael_toggle_icon_controller( 'eael_grasshopper_toggle_secondary_icon', 'eael_grasshopper_toggle_secondary_icon_show' );

        $this->add_control(
			'eael_grasshopper_toggle_secondary_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default'   => '#000000A8',
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="2"] svg path, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="1"] svg path' => 'fill: {{VALUE}}',
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="2"] i, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="1"] i' => 'color: {{VALUE}}',
				],
                'condition' => [
					'eael_grasshopper_toggle_secondary_icon_show' => 'yes',
				],
			]
		);

        $this->add_control(
			'eael_grasshopper_toggle_secondary_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="2"] svg, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="1"] svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-toggle-flip-switch input[c-option="1"]:checked ~ label[data-option="2"] i, {{WRAPPER}} .eael-toggle-flip-switch input[c-option="2"]:checked ~ label[data-option="1"] i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
                'condition' => [
					'eael_grasshopper_toggle_secondary_icon_show' => 'yes',
				],
			]
		);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
			'eael_grasshopper_toggle_switch_card',
			[
				'label'     => esc_html__( 'Liquid Glass', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_control(
			'eael_grasshopper_toggle_switch_card_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default'   => '#fff3',
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch .eael-card-face' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
			'eael_grasshopper_toggle_switch_card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem', 'custom' ],
				'default'    => [
					'top'    => 30,
					'right'  => 30,
					'bottom' => 30,
					'left'   => 30,
					'unit'   => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-toggle-flip-switch .eael-card-face' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->end_controls_section();
    }

    // Glossy Toggle Switch Style
    public function eael_glossy_toggle_switch_style() {
        $this->start_controls_section(
            'eael_glossy_toggle_switch_style',
            [
                'label'             => __( 'Switch', 'essential-addons-elementor' ),
                'tab'               => Controls_Manager::TAB_STYLE,
                'condition'         => [
                    'eael_toggle_effects_style' => 'glossy',
                ],
            ]
        );

        $this->add_control(
            'eael_glossy_toggle_switch_alignment',
            [
                'label'                 => __( 'Alignment', 'essential-addons-elementor' ),
                'type'                  => Controls_Manager::CHOOSE,
                'default'               => 'center',
                'options'               => [
                    'left'          => [
                        'title'     => __( 'Left', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-left',
                    ],
                    'center'        => [
                        'title'     => __( 'Center', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-center',
                    ],
                    'right'         => [
                        'title'     => __( 'Right', 'essential-addons-elementor' ),
                        'icon'      => 'eicon-h-align-right',
                    ],
                ],
                'prefix_class'          => 'eael-toggle-',
                'frontend_available'    => true,
            ]
        );

        $this->add_control(
			'eael_glossy_toggle_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default' => '#bcbbbb6b',
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_control(
			'eael_glossy_toggle_backdrop',
			[
				'label' => esc_html__( 'Backdrop Filter', 'essential-addons-elementor' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher' => 'backdrop-filter: blur({{SIZE}}{{UNIT}}) url(#switcher);',
				],
			]
		);

        $this->add_responsive_control(
			'eael_glossy_toggle_width',
			[
				'label'      => esc_html__( 'Width', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 135,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher' => 'width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-glossy-switcher:has(input[c-option="2"]:checked)::after' => 'translate: calc({{SIZE}}{{UNIT}} - ({{eael_glossy_toggle_switch_card_width.SIZE}}px + 10px)) 0;',
				],
			]
		);

        $this->add_control(
			'eael_glossy_toggle_height',
			[
				'label'      => esc_html__( 'Height', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-glossy-switcher::after' => 'height: calc({{SIZE}}{{UNIT}} - 10{{UNIT}});',
				],
			]
		);

        $this->add_responsive_control(
            'eael_glossy_toggle_switch_gap',
            [
                'label'     => __( 'Margin Bottom', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::SLIDER,
                'default'   => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'size_units' => [ 'px', '%' ],
                'range'      => [
                    'px' => [
                        'max' => 80,
                    ],
                ],
                'tablet_default' => [
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-toggle-switch-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
			'eael_glossy_toggle_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem', 'custom' ],
				'default'    => [
					'top'    => 30,
					'right'  => 30,
					'bottom' => 30,
					'left'   => 30,
					'unit'   => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_responsive_control(
			'eael_glossy_toggle_padding',
			[
				'label'      => esc_html__( 'Icon Position', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem', 'custom' ],
				'default'    => [
					'top'    => 8,
					'right'  => 22,
					'bottom' => 10,
					'left'   => 22,
					'unit'   => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->start_controls_tabs(
			'eael_glossy_toggle_style_tabs'
		);

		$this->start_controls_tab(
			'eael_glossy_toggle_primary_tab',
			[
				'label' => esc_html__( 'Primary', 'essential-addons-elementor' ),
			]
		);

        $this->add_control(
			'eael_glossy_toggle_primary_icon_show',
			[
				'label'        => esc_html__( 'Show Icon', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

        $this->eael_toggle_icon_controller( 'eael_glossy_toggle_primary_icon', 'eael_glossy_toggle_primary_icon_show' );

        $this->add_control(
			'eael_glossy_toggle_primary_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default'   => '#FFD203',
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher__option:has(input:checked) svg path, 
                    {{WRAPPER}} .eael-glossy-switcher__option:has(input:checked) i' => 'fill: {{VALUE}}; color: {{VALUE}}',
				],
                'condition' => [
					'eael_glossy_toggle_primary_icon_show' => 'yes',
				],
			]
		);

        $this->add_responsive_control(
			'eael_glossy_toggle_primary_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 25,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher .eael-glossy-switcher__option:has(input:checked) .switcher__icon svg' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-glossy-switcher .eael-glossy-switcher__option:has(input:checked) .switcher__icon i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
                'condition' => [
					'eael_glossy_toggle_primary_icon_show' => 'yes',
				],
			]
		);

        $this->add_responsive_control(
			'eael_glossy_toggle_primary_translate',
			[
				'label'      => esc_html__( 'Translate', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => -500,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => -3,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher:has(input[c-option="1"]:checked)::after' => 'translate: {{SIZE}}{{UNIT}} 0;',
				],
			]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
			'eael_glossy_toggle_secondary_tab',
			[
				'label' => esc_html__( 'Secondary', 'essential-addons-elementor' ),
			]
		);

        $this->add_control(
			'eael_glossy_toggle_secondary_icon_show',
			[
				'label'        => esc_html__( 'Show Icon', 'essential-addons-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'essential-addons-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'essential-addons-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

        $this->eael_toggle_icon_controller( 'eael_glossy_toggle_secondary_icon', 'eael_glossy_toggle_secondary_icon_show' );

        $this->add_control(
			'eael_glossy_toggle_secondary_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default'   => '#000000A8',
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher__option:not([type=checked]) svg path, 
                    {{WRAPPER}} .eael-glossy-switcher__option:not([type=checked]) i' => 'fill: {{VALUE}}; color: {{VALUE}}',
				],
                'condition' => [
					'eael_glossy_toggle_secondary_icon_show' => 'yes',
				],
			]
		);

        $this->add_responsive_control(
			'eael_glossy_toggle_secondary_icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 24,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher .eael-glossy-switcher__option:not([type=checked]) .switcher__icon svg' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eael-glossy-switcher .eael-glossy-switcher__option:not([type=checked]) .switcher__icon i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
                'condition' => [
					'eael_glossy_toggle_secondary_icon_show' => 'yes',
				],
			]
		);

        $this->add_responsive_control(
			'eael_glossy_toggle_secondary_translate',
			[
				'label'      => esc_html__( 'Translate', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => -500,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => -3,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher:has(input[c-option="2"]:checked)::after' => 'transform: translateX({{SIZE}}{{UNIT}});',
				],
			]
		);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
			'eael_glossy_toggle_switch_card',
			[
				'label'     => esc_html__( 'Liquid Glass', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_control(
			'eael_glossy_toggle_switch_card_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'essential-addons-elementor' ),
				'type'      => Controls_Manager::COLOR,
                'default'   => '#fff3',
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher::after' => 'background-color: {{VALUE}}',
				],
			]
		);

        $this->add_responsive_control(
			'eael_glossy_toggle_switch_card_width',
			[
				'label'      => esc_html__( 'Width', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 55,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher::after' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'eael_glossy_toggle_switch_card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'rem', 'custom' ],
				'default'    => [
					'top'    => 20,
					'right'  => 20,
					'bottom' => 20,
					'left'   => 20,
					'unit'   => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .eael-glossy-switcher::after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->end_controls_section();
    }
    
    /**
     * Render toggle widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute( 'toggle-container', 'class', 'eael-toggle-container' );
        
        $this->add_render_attribute( 'toggle-container', 'id', 'eael-toggle-container-' . esc_attr( $this->get_id() ) );
        $this->add_render_attribute( 'toggle-switch-wrap', 'class', 'eael-toggle-switch-wrap' );
        
        if ( 'glossy' === $settings['eael_toggle_effects_style'] ) { 
            $this->add_render_attribute( 'toggle-switch-container', 'class', 'eael-glossy-switcher' );
        }

        $this->add_render_attribute( 'toggle-switch-container', 'class', 'eael-toggle-switch-container' );

        
        $this->add_render_attribute( 'toggle-switch-container', 'class', 'eael-toggle-switch-' . $settings['switch_style'] );
        
        $this->add_render_attribute( 'toggle-content-wrap', 'class', 'eael-toggle-content-wrap primary' );
        ?>
        <div <?php $this->print_render_attribute_string( 'toggle-container' ); ?>>
            <div <?php $this->print_render_attribute_string( 'toggle-switch-wrap' ); ?>>
                <div class="eael-toggle-switch-inner">

                    <?php if ( 'grasshopper' !== $settings['eael_toggle_effects_style'] ) { ?>
                        <div class="eael-primary-toggle-label">
                            <?php echo esc_attr( $settings['primary_label'] ); ?>
                        </div>
                    <?php } ?>

                    <div tabindex="0" <?php $this->print_render_attribute_string( 'toggle-switch-container' ); ?>>
                        <?php if ( 'default' === $settings['eael_toggle_effects_style'] ) { ?>
                            <label class="eael-toggle-switch">
                                <input type="checkbox">
                                <span class="eael-toggle-slider"></span>
                            </label>
                        <?php } elseif ( 'glossy' === $settings['eael_toggle_effects_style'] ) {
                            ?>
                        <label class="eael-glossy-switcher__option eael-toggle-switch" data-option="1">
                            <input class="eael-glossy-switcher__input" type="radio" c-option="1" name="theme-<?php echo esc_attr( $this->get_id() ); ?>" checked=""/>
                            <span class="switcher__icon">
                                <?php
                                if ( 'yes' === $settings['eael_glossy_toggle_primary_icon_show'] ) {
                                    if ( empty($settings['eael_glossy_toggle_primary_icon']['value'] ) ) {
                                        echo Utils::file_get_contents(EAEL_PRO_PLUGIN_PATH . 'assets/front-end/img/light.svg');
                                    } else {
                                        Icons_Manager::render_icon($settings['eael_glossy_toggle_primary_icon'], ['aria-hidden' => 'true']);
                                    }
                                }
                                ?>
                            </span>
                        </label>

                        <label class="eael-glossy-switcher__option eael-toggle-switch" data-option="2">
                            <input class="eael-glossy-switcher__input" type="radio" c-option="2" name="theme-<?php echo esc_attr( $this->get_id() ); ?>" />
                            <span class="switcher__icon">
                                <?php
                                if ( 'yes' === $settings['eael_glossy_toggle_secondary_icon_show'] ) {
                                    if (empty($settings['eael_glossy_toggle_secondary_icon']['value'])) {
                                        echo Utils::file_get_contents(EAEL_PRO_PLUGIN_PATH . 'assets/front-end/img/dark.svg');
                                    } else {
                                        Icons_Manager::render_icon($settings['eael_glossy_toggle_secondary_icon'], ['aria-hidden' => 'true']);
                                    }
                                }
                                ?>
                            </span>
                        </label>

                        <div class="switcher__filter">
                            <svg style="display: none">
                                <filter id="switcher" x="0" y="0" width="100%" height="100%" filterUnits="objectBoundingBox">
                                    <feTurbulence type="fractalNoise" baseFrequency="0.003 0.007" numOctaves="1" result="turbulence" />
                                    <feDisplacementMap in="SourceGraphic" in2="turbulence" scale="200" xChannelSelector="R" yChannelSelector="G" />
                                </filter>
                            </svg>
                        </div>
                            <?php
                        } elseif ( 'grasshopper' === $settings['eael_toggle_effects_style'] ) {
                            ?>
                            <div class="eael-toggle-flip-switch eael-toggle-flip-switch-<?php echo esc_attr( $this->get_id() ); ?>">
                                <input type="radio" class="eael-flip-switcher__input" c-option="1" id="switch-opt-1-<?php echo esc_attr( $this->get_id() ); ?>" name="flip-switch-<?php echo esc_attr( $this->get_id() ); ?>" checked="" />
                                <input type="radio" class="eael-flip-switcher__input" c-option="2" id="switch-opt-2-<?php echo esc_attr( $this->get_id() ); ?>" name="flip-switch-<?php echo esc_attr( $this->get_id() ); ?>" />

                                <label for="switch-opt-1-<?php echo esc_attr( $this->get_id() ); ?>" class="eael-switch-button" data-option="1">
                                    <?php
                                    if ( 'yes' === $settings['eael_grasshopper_toggle_primary_icon_show'] ) {
                                        if ( empty( $settings['eael_grasshopper_toggle_primary_icon']['value'] ) ) {
                                            echo Utils::file_get_contents(EAEL_PRO_PLUGIN_PATH . 'assets/front-end/img/light.svg');
                                        } else {
                                            Icons_Manager::render_icon($settings['eael_grasshopper_toggle_primary_icon'], ['aria-hidden' => 'true']);
                                        }
                                    }
                                    ?>
                                    <div class="eael-primary-toggle-label">
                                        <?php echo esc_attr( $settings['primary_label'] ); ?>
                                    </div>
                                </label>

                                <label for="switch-opt-2-<?php echo esc_attr( $this->get_id() ); ?>" class="eael-switch-button" data-option="2">
                                    <?php
                                    if ( 'yes' === $settings['eael_grasshopper_toggle_secondary_icon_show'] ) {
                                        if ( empty( $settings['eael_grasshopper_toggle_secondary_icon']['value'] ) ) {
                                            echo Utils::file_get_contents(EAEL_PRO_PLUGIN_PATH . 'assets/front-end/img/dark.svg');
                                        } else {
                                            Icons_Manager::render_icon($settings['eael_grasshopper_toggle_secondary_icon'], ['aria-hidden' => 'true']);
                                        }
                                    }
                                    ?>
                                    <div class="eael-secondary-toggle-label">
                                        <?php echo esc_attr( $settings['secondary_label'] ); ?>
                                    </div>
                                </label>

                                <div class="eael-toggle-switch-card">
                                    <div class="eael-card-face card-front"></div>
                                    <div class="eael-card-face eael-card-back"></div>
                                </div>
                                
                                <div class="switcher__filter">
                                    <svg style="display: none">
                                        <filter id="switcher" x="0" y="0" width="100%" height="100%" filterUnits="objectBoundingBox">
                                            <feTurbulence type="fractalNoise" baseFrequency="0.003 0.007" numOctaves="1" result="turbulence" />
                                            <feDisplacementMap in="SourceGraphic" in2="turbulence" scale="200" xChannelSelector="R" yChannelSelector="G" />
                                        </filter>
                                    </svg>
                                </div>
                            </div>
                            <?php
                        } ?>
                    </div>

                    <?php if ( 'grasshopper' !== $settings['eael_toggle_effects_style'] ) { ?>
                        <div class="eael-secondary-toggle-label">
                            <?php echo esc_attr( $settings['secondary_label'] ); ?>
                        </div>
                    <?php } ?>

                </div>
            </div>
            <div <?php $this->print_render_attribute_string( 'toggle-content-wrap' ); ?>>
                <div class="eael-toggle-primary-wrap">
                    <?php
                    if ( $settings['primary_content_type'] == 'content' ) {
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo $this->parse_text_editor( $settings['primary_content'] );
                    } elseif ( $settings['primary_content_type'] == 'image' ) {
                        $this->add_render_attribute( 'primary-image', 'src', esc_url( $settings['primary_image']['url'] ) );
                        $this->add_render_attribute( 'primary-image', 'alt', Control_Media::get_image_alt( $settings['primary_image'] ) );
                        $this->add_render_attribute( 'primary-image', 'title', Control_Media::get_image_title( $settings['primary_image'] ) );
                        
                        echo '<img '; $this->print_render_attribute_string( 'primary-image' ); echo '>';
                    } elseif ( $settings['primary_content_type'] == 'template' ) {
	                    if ( ! empty( $settings['primary_templates'] ) ) {
		                    // WPML Compatibility
		                    if ( ! is_array( $settings['primary_templates'] ) ) {
			                    $settings['primary_templates'] = apply_filters( 'wpml_object_id', $settings['primary_templates'], 'wp_template', true );
		                    }

		                    Helper::eael_onpage_edit_template_markup( get_the_ID(), $settings['primary_templates'] );
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		                    echo Plugin::$instance->frontend->get_builder_content( $settings['primary_templates'], true );
	                    }
                    }
                    ?>
                </div>
                <div class="eael-toggle-secondary-wrap">
                    <?php
                    if ( $settings['secondary_content_type'] == 'content' ) {
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo $this->parse_text_editor( $settings['secondary_content'] );
                    } elseif ( $settings['secondary_content_type'] == 'image' ) {
                        $this->add_render_attribute( 'secondary-image', 'src', esc_url( $settings['secondary_image']['url'] ) );
                        $this->add_render_attribute( 'secondary-image', 'alt', Control_Media::get_image_alt( $settings['secondary_image'] ) );
                        $this->add_render_attribute( 'secondary-image', 'title', Control_Media::get_image_title( $settings['secondary_image'] ) );
                        
                        echo '<img '; $this->print_render_attribute_string( 'secondary-image' ); echo '>';
                    } elseif ( $settings['secondary_content_type'] == 'template' ) {
	                    if ( ! empty( $settings['secondary_templates'] ) ) {
		                    // WPML Compatibility
		                    if ( ! is_array( $settings['secondary_templates'] ) ) {
			                    $settings['secondary_templates'] = apply_filters( 'wpml_object_id', $settings['secondary_templates'], 'wp_template', true );
		                    }

		                    Helper::eael_onpage_edit_template_markup( get_the_ID(), $settings['secondary_templates'] );
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		                    echo Plugin::$instance->frontend->get_builder_content( $settings['secondary_templates'], true );
	                    }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render toggle widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @access protected
     */
    protected function content_template() {
    }
}
