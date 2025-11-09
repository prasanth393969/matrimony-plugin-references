<?php
namespace Essential_Addons_Elementor\Pro\Elements;

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Icons_Manager;
use \Elementor\Utils;
use \Elementor\Widget_Base;
use Essential_Addons_Elementor\Classes\Helper;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// If this file is called directly, abort.

class Image_Comparison extends Widget_Base {
    public function get_name() {
        return 'eael-image-comparison';
    }

    public function get_title() {
        return esc_html__( 'Image Comparison', 'essential-addons-elementor' );
    }

    public function get_icon() {
        return 'eaicon-image-comparison';
    }

    public function get_categories() {
        return ['essential-addons-elementor'];
    }

    public function get_keywords() {
        return [
            'image',
            'compare',
            'ea image compare',
            'ea image comparison',
            'table',
            'before after image',
            'before and after image',
            'before after slider',
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

    /**
     * Get interaction mode with backward compatibility
     *
     * @param array $settings Widget settings
     * @return array Array with 'hover' and 'click' boolean values
     */
    private function get_interaction_mode( $settings ) {
        // Check if new unified control is set
        if ( isset( $settings['eael_image_comp_interaction'] ) && $settings['eael_image_comp_interaction'] !== '' ) {
            $interaction = $settings['eael_image_comp_interaction'];
            return [
                'hover' => $interaction === 'hover',
                'click' => $interaction === 'click',
                'toggle' => $interaction === 'toggle'
            ];
        }

        // Fallback to legacy controls for backward compatibility
        return [
            'hover' => isset( $settings['eael_image_comp_move'] ) && $settings['eael_image_comp_move'] === 'yes',
            'click' => isset( $settings['eael_image_comp_click'] ) && $settings['eael_image_comp_click'] === 'yes',
            'toggle' => false
        ];
    }

    /**
     * Migrate legacy settings to new unified control
     *
     * @param array $settings Widget settings
     * @return array Migrated settings
     */
    private function migrate_legacy_settings( $settings ) {
        // Only migrate if new control is not set but legacy controls exist
        if ( empty( $settings['eael_image_comp_interaction'] ) || $settings['eael_image_comp_interaction'] === '' ) {
            $hover_enabled = isset( $settings['eael_image_comp_move'] ) && $settings['eael_image_comp_move'] === 'yes';
            $click_enabled = isset( $settings['eael_image_comp_click'] ) && $settings['eael_image_comp_click'] === 'yes';

            // Determine the new interaction mode based on legacy settings
            if ( $hover_enabled && $click_enabled ) {
                // If both are enabled, prioritize hover (as it's more common)
                $settings['eael_image_comp_interaction'] = 'hover';
            } elseif ( $hover_enabled ) {
                $settings['eael_image_comp_interaction'] = 'hover';
            } elseif ( $click_enabled ) {
                $settings['eael_image_comp_interaction'] = 'click';
            } else {
                $settings['eael_image_comp_interaction'] = 'none';
            }
        }

        return $settings;
    }

    public function get_custom_help_url() {
        return 'https://essential-addons.com/elementor/docs/image-comparison/';
    }

    /**
     * Override get_settings_for_display to apply migration logic
     *
     * @param string $setting_key Optional setting key
     * @return array|mixed Settings array or specific setting value
     */
    public function get_settings_for_display( $setting_key = null ) {
        $settings = parent::get_settings_for_display( $setting_key );

        // Apply migration logic if we're getting all settings
        if ( null === $setting_key ) {
            $settings = $this->migrate_legacy_settings( $settings );
        }

        return $settings;
    }

    protected function register_controls() {

        // Content Controls
        $this->start_controls_section(
            'eael_image_comparison_images',
            [
                'label' => esc_html__( 'Images', 'essential-addons-elementor' ),
            ]
        );

        $this->start_controls_tabs( 'eael_image_comparison_tabs' );

        $this->start_controls_tab(
            'eael_image_comparison_before_tab',
            [
                'label' => __( 'Before', 'essential-addons-elementor' ),
            ]
        );

        $this->add_control(
            'before_image_label',
            [
                'label'       => __( 'Label', 'essential-addons-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true,
                'default'     => 'Before',
                'title'       => __( 'Input before image label', 'essential-addons-elementor' ),
                'ai' => [
					'active' => false,
				],
            ]
        );

        $this->add_control(
            'before_image',
            [
                'label'   => "",
                'type'    => Controls_Manager::MEDIA,
	            'dynamic' => [
		            'active' => true,
	            ],
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'before_image_alt',
            [
                'label'       => __( 'ALT Tag', 'essential-addons-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic' => [ 'active' => true ],
                'label_block' => true,
                'default'     => '',
                'placeholder' => __( 'Enter alter tag for the image', 'essential-addons-elementor' ),
                'title'       => __( 'Input image alter tag here', 'essential-addons-elementor' ),
                'ai' => [
					'active' => false,
				],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'eael_image_comparison_after_tab',
            [
                'label' => __( 'After', 'essential-addons-elementor' ),
            ]
        );

        $this->add_control(
            'after_image_label',
            [
                'label'       => __( 'Label', 'essential-addons-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'default'     => 'After',
                'title'       => __( 'Input after image label', 'essential-addons-elementor' ),
                'ai' => [
					'active' => false,
				],
            ]
        );
        $this->add_control(
            'after_image',
            [
                'label'   => "",
                'type'    => Controls_Manager::MEDIA,
	            'dynamic' => [
		            'active' => true,
	            ],
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'after_image_alt',
            [
                'label'       => __( 'ALT Tag', 'essential-addons-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic' => [ 'active' => true ],
                'label_block' => true,
                'default'     => '',
                'placeholder' => __( 'Enter alter tag for the image', 'essential-addons-elementor' ),
                'title'       => __( 'Input image alter tag here', 'essential-addons-elementor' ),
                'ai' => [
					'active' => false,
				],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'        => 'eael_before_image_size',
                'exclude'     => [ 'custom' ],
				'default'     => 'full',
                'separator'   => 'before',
			]
		);

        $this->end_controls_section();

        $this->start_controls_section(
            'eael_image_comparison_settings',
            [
                'label' => esc_html__( 'Settings', 'essential-addons-elementor' ),
            ]
        );

        $this->add_control(
            'eael_image_comp_offset',
            [
                'label'      => esc_html__( 'Original Image Visibility', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range'      => ['%' => ['min' => 10, 'max' => 90]],
                'default'    => ['size' => 70, 'unit' => '%'],
            ]
        );

        $this->add_control(
            'eael_image_comp_orientation',
            [
                'label'   => esc_html__( 'Orientation', 'essential-addons-elementor' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'horizontal' => [
                        'title' => __( 'Horizontal', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-h-align-stretch',
                    ],
                    'vertical'   => [
                        'title' => __( 'Vertical', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-v-align-stretch',
                    ],
                ],
                'default' => 'horizontal',
            ]
        );

        $this->add_control(
            'eael_image_comp_overlay',
            [
                'label'     => esc_html__( 'Wants Overlay ?', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __( 'yes', 'essential-addons-elementor' ),
                'label_off' => __( 'no', 'essential-addons-elementor' ),
                'default'   => 'yes',
            ]
        );

        // New unified interaction control
        $this->add_control(
            'eael_image_comp_interaction',
            [
                'label'   => esc_html__( 'Slider Interaction', 'essential-addons-elementor' ),
                'type'    => Controls_Manager::CHOOSE,
                'default' => 'none',
                'options' => [
                    'none'  => [
                        'title' => esc_html__( 'None', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-ban',
                    ],
                    'click' => [
                        'title' => esc_html__( 'Click', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-click',
                    ],
                    'hover' => [
                        'title' => esc_html__( 'Hover', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-scroll',
                    ],
                    'toggle' => [
                        'title' => esc_html__( 'Toggle', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-dual-button',
                    ],
                ],
                'description' => esc_html__( 'Choose how users can interact with the slider.', 'essential-addons-elementor' ),
            ]
        );

        // Legacy controls for backward compatibility (hidden)
        $this->add_control(
            'eael_image_comp_move',
            [
                'label'     => esc_html__( 'Move Slider On Hover', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __( 'yes', 'essential-addons-elementor' ),
                'label_off' => __( 'no', 'essential-addons-elementor' ),
                'default'   => 'no',
                'classes'   => 'elementor-hidden', // Hide from UI
            ]
        );

        $this->add_control(
            'eael_image_comp_click',
            [
                'label'     => esc_html__( 'Move Slider On Click', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __( 'yes', 'essential-addons-elementor' ),
                'label_off' => __( 'no', 'essential-addons-elementor' ),
                'default'   => 'no',
                'classes'   => 'elementor-hidden', // Hide from UI
            ]
        );

        $this->end_controls_section();

        // Toggle Button Settings Section
        $this->start_controls_section(
            'eael_image_comp_toggle_settings',
            [
                'label' => esc_html__( 'Toggle Button', 'essential-addons-elementor' ),
                'condition' => [
                    'eael_image_comp_interaction' => 'toggle',
                ],
            ]
        );

        $this->add_responsive_control(
            'eael_image_comp_toggle_position_x',
            [
                'label' => esc_html__( 'Position X', 'text-domain' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-img-comp-wrapper .eael-img-comp-toggle-btns' => 'left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'eael_image_comp_toggle_position_y',
            [
                'label' => esc_html__( 'Position Y', 'text-domain' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-img-comp-wrapper .eael-img-comp-toggle-btns' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'eael_image_comp_step',
            [
                'label'   => esc_html__( 'Animation Speed', 'essential-addons-elementor' ),
                'type'    => Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range'      => ['%' => ['min' => 1, 'max' => 50]],
                'default'    => ['size' => 10, 'unit' => '%'],
            ]
        );

        $this->add_control(
            'eael_toggle_content_type',
            [
                'label'   => esc_html__( 'Content Type', 'essential-addons-elementor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'text',
                'options' => [
                    'text' => esc_html__( 'Text', 'essential-addons-elementor' ),
                    'icon' => esc_html__( 'Icon', 'essential-addons-elementor' ),
                    'both' => esc_html__( 'Text & Icon', 'essential-addons-elementor' ),
                ],
            ]
        );

        $this->start_controls_tabs( 'eael_image_comparison_toggle_tabs' );
        $this->start_controls_tab( 'eael_image_comparison_toggle_before_tab', [ 'label' => esc_html__( 'Before', 'essential-addons-elementor' ) ] );

        $this->add_control(
            'eael_toggle_before_text',
            [
                'label'       => esc_html__( 'Label', 'essential-addons-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'Show Before',
                'dynamic'     => [
                    'active' => true,
                ],
                'condition'   => [
                    'eael_toggle_content_type' => ['text', 'both'],
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'eael_toggle_before_icon',
            [
                'label'       => '',
                'type'        => Controls_Manager::ICONS,
                'default'     => [
                    'value'   => 'fas fa-eye',
                    'library' => 'fa-solid',
                ],
                'condition'   => [
                    'eael_toggle_content_type' => ['icon', 'both'],
                ],
            ]
        );

        $this->end_controls_tab();
        $this->start_controls_tab( 'eael_image_comparison_toggle_after_tab', [ 'label' => esc_html__( 'After', 'essential-addons-elementor' ) ] );
        
        $this->add_control(
            'eael_toggle_after_text',
            [
                'label'       => esc_html__( 'Label', 'essential-addons-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => 'Show After',
                'dynamic'     => [
                    'active' => true,
                ],
                'condition'   => [
                    'eael_toggle_content_type' => ['text', 'both'],
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );

        $this->add_control(
            'eael_toggle_after_icon',
            [
                'label'       => '',
                'type'        => Controls_Manager::ICONS,
                'default'     => [
                    'value'   => 'fas fa-eye-slash',
                    'library' => 'fa-solid',
                ],
                'condition'   => [
                    'eael_toggle_content_type' => ['icon', 'both'],
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

        $this->start_controls_section(
            'eael_image_comparison_styles',
            [
                'label' => esc_html__( 'Image Container Styles', 'essential-addons-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'eael_image_container_width',
            [
                'label'     => esc_html__( 'Set max width for the container?', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => __( 'yes', 'essential-addons-elementor' ),
                'label_off' => __( 'no', 'essential-addons-elementor' ),
                'default'   => 'yes',
            ]
        );

        $this->add_responsive_control(
            'eael_image_container_width_value',
            [
                'label'      => __( 'Container Max Width', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'default'    => [
                    'size' => 80,
                    'unit' => '%',
                ],
                'size_units' => ['%', 'px'],
                'range'      => [
                    '%'  => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .eael-img-comp-container' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
                'condition'  => [
                    'eael_image_container_width' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'eael_img_comp_border',
                'selector' => '{{WRAPPER}} .eael-img-comp-container',
            ]
        );

        $this->add_control(
            'eael_img_comp_border_radius',
            [
                'label'     => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .eael-img-comp-container' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * Style tab: overlay background
         */

        $this->start_controls_section(
            'section_overlay_style',
            [
                'label'     => __( 'Overlay', 'essential-addons-elementor' ),
                'tab'       => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'eael_image_comp_overlay' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'eael_img_cmp_overlay_background',
                'label'    => __( 'Background', 'essential-addons-elementor' ),
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .eael-img-comp-container .twentytwenty-overlay:hover',
            ]
        );

        $this->end_controls_section();

        /**
         * Style Tab: Handle
         */
        $this->start_controls_section(
            'section_handle_style',
            [
                'label' => __( 'Handle', 'essential-addons-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'tabs_handle_style' );

        $this->start_controls_tab(
            'tab_handle_normal',
            [
                'label' => __( 'Normal', 'essential-addons-elementor' ),
            ]
        );

        $this->add_control(
            'handle_icon_color',
            [
                'label'     => __( 'Icon Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
	                '{{WRAPPER}} .twentytwenty-left-arrow'  => 'border-right-color: {{VALUE}}',
	                '{{WRAPPER}} .twentytwenty-right-arrow' => 'border-left-color: {{VALUE}}',
	                '{{WRAPPER}} .twentytwenty-up-arrow'    => 'border-bottom-color: {{VALUE}}',
	                '{{WRAPPER}} .twentytwenty-down-arrow'  => 'border-top-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'handle_background',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .twentytwenty-handle',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'handle_border',
                'label'       => __( 'Border', 'essential-addons-elementor' ),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .twentytwenty-handle',
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'handle_border_radius',
            [
                'label'      => __( 'Border Radius', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .twentytwenty-handle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'handle_box_shadow',
                'selector' => '{{WRAPPER}} .twentytwenty-handle',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_handle_hover',
            [
                'label' => __( 'Hover', 'essential-addons-elementor' ),
            ]
        );

        $this->add_control(
            'handle_icon_color_hover',
            [
                'label'     => __( 'Icon Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
	                '{{WRAPPER}} .twentytwenty-handle:hover .twentytwenty-left-arrow'  => 'border-right-color: {{VALUE}}',
	                '{{WRAPPER}} .twentytwenty-handle:hover .twentytwenty-right-arrow' => 'border-left-color: {{VALUE}}',
	                '{{WRAPPER}} .twentytwenty-handle:hover .twentytwenty-up-arrow'    => 'border-bottom-color: {{VALUE}}',
	                '{{WRAPPER}} .twentytwenty-handle:hover .twentytwenty-down-arrow'  => 'border-top-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'handle_background_hover',
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .twentytwenty-handle:hover',
            ]
        );

        $this->add_control(
            'handle_border_color_hover',
            [
                'label'     => __( 'Border Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .twentytwenty-handle:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        /**
         * Style Tab: Divider
         */
        $this->start_controls_section(
            'section_divider_style',
            [
                'label' => __( 'Divider', 'essential-addons-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label'     => __( 'Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .twentytwenty-horizontal .twentytwenty-handle:before, {{WRAPPER}} .twentytwenty-horizontal .twentytwenty-handle:after, {{WRAPPER}} .twentytwenty-vertical .twentytwenty-handle:before, {{WRAPPER}} .twentytwenty-vertical .twentytwenty-handle:after' => 'background: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_width',
            [
                'label'          => __( 'Width', 'essential-addons-elementor' ),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'size' => 3,
                    'unit' => 'px',
                ],
                'size_units'     => ['px', '%'],
                'range'          => [
                    'px' => [
                        'max' => 20,
                    ],
                ],
                'tablet_default' => [
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'unit' => 'px',
                ],
                'selectors'      => [
                    '{{WRAPPER}} .twentytwenty-horizontal .twentytwenty-handle:before, {{WRAPPER}} .twentytwenty-horizontal .twentytwenty-handle:after' => 'width: {{SIZE}}{{UNIT}}; margin-left: calc(-{{SIZE}}{{UNIT}}/2);',
                    '{{WRAPPER}} .twentytwenty-vertical .twentytwenty-handle:before, {{WRAPPER}} .twentytwenty-vertical .twentytwenty-handle:after' => 'height: {{SIZE}}{{UNIT}}; margin-top: calc(-{{SIZE}}{{UNIT}}/2);',
                ],
            ]
        );

        $this->end_controls_section();

        /**
         * Style Tab: Label
         */
        $this->start_controls_section(
            'section_label_style',
            [
                'label' => __( 'Label', 'essential-addons-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_horizontal_position',
            [
                'label'        => __( 'Position', 'essential-addons-elementor' ),
                'type'         => Controls_Manager::CHOOSE,
                'label_block'  => false,
                'default'      => 'top',
                'options'      => [
                    'top'    => [
                        'title' => __( 'Top', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'middle' => [
                        'title' => __( 'Middle', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-v-align-middle',
                    ],
                    'bottom' => [
                        'title' => __( 'Bottom', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-v-align-bottom',
                    ],
                ],
                'prefix_class' => 'eael-ic-label-horizontal-',
                'condition'    => [
                    'orientation' => 'horizontal',
                ],
            ]
        );

        $this->add_control(
            'label_vertical_position',
            [
                'label'        => __( 'Position', 'essential-addons-elementor' ),
                'type'         => Controls_Manager::CHOOSE,
                'label_block'  => false,
                'options'      => [
                    'left'   => [
                        'title' => __( 'Left', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right'  => [
                        'title' => __( 'Right', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default'      => 'center',
                'prefix_class' => 'eael-ic-label-vertical-',
                'condition'    => [
                    'orientation' => 'vertical',
                ],
            ]
        );

        $this->add_responsive_control(
            'label_align',
            [
                'label'      => __( 'Align', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => [
                        'max' => 200,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}}.eael-ic-label-horizontal-top .twentytwenty-horizontal .twentytwenty-before-label:before,
                    {{WRAPPER}}.eael-ic-label-horizontal-top .twentytwenty-horizontal .twentytwenty-after-label:before'    => 'top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twentytwenty-horizontal .twentytwenty-before-label:before'                                                   => 'left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twentytwenty-horizontal .twentytwenty-after-label:before'                                                    => 'right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.eael-ic-label-horizontal-bottom .twentytwenty-horizontal .twentytwenty-before-label:before,
                    {{WRAPPER}}.eael-ic-label-horizontal-bottom .twentytwenty-horizontal .twentytwenty-after-label:before' => 'bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twentytwenty-vertical .twentytwenty-before-label:before'                                                     => 'top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .twentytwenty-vertical .twentytwenty-after-label:before'                                                      => 'bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.eael-ic-label-vertical-left .twentytwenty-vertical .twentytwenty-before-label:before,
                    {{WRAPPER}}.eael-ic-label-vertical-left .twentytwenty-vertical .twentytwenty-after-label:before'       => 'left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.eael-ic-label-vertical-right .twentytwenty-vertical .twentytwenty-before-label:before,
                    {{WRAPPER}}.eael-ic-label-vertical-right .twentytwenty-vertical .twentytwenty-after-label:before'      => 'right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( 'tabs_label_style' );

        $this->start_controls_tab(
            'tab_label_before',
            [
                'label' => __( 'Before', 'essential-addons-elementor' ),
            ]
        );

        $this->add_control(
            'label_text_color_before',
            [
                'label'     => __( 'Text Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .twentytwenty-before-label:before' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'label_bg_color_before',
            [
                'label'     => __( 'Background Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .twentytwenty-before-label:before' => 'background: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'label_border',
                'label'       => __( 'Border', 'essential-addons-elementor' ),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .twentytwenty-before-label:before',
            ]
        );

        $this->add_control(
            'label_border_radius',
            [
                'label'      => __( 'Border Radius', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .twentytwenty-before-label:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_label_after',
            [
                'label' => __( 'After', 'essential-addons-elementor' ),
            ]
        );

        $this->add_control(
            'label_text_color_after',
            [
                'label'     => __( 'Text Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .twentytwenty-after-label:before' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'label_bg_color_after',
            [
                'label'     => __( 'Background Color', 'essential-addons-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .twentytwenty-after-label:before' => 'background: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'label_border_after',
                'label'       => __( 'Border', 'essential-addons-elementor' ),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .twentytwenty-after-label:before',
            ]
        );

        $this->add_control(
            'label_border_radius_after',
            [
                'label'      => __( 'Border Radius', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .twentytwenty-after-label:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'label_typography',
                'label'     => __( 'Typography', 'essential-addons-elementor' ),
                'global' => [
	                'default' => Global_Typography::TYPOGRAPHY_ACCENT
                ],
                'selector'  => '{{WRAPPER}} .twentytwenty-before-label:before, {{WRAPPER}} .twentytwenty-after-label:before',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'label_padding',
            [
                'label'      => __( 'Padding', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .twentytwenty-before-label:before, {{WRAPPER}} .twentytwenty-after-label:before' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator'  => 'before',
            ]
        );

        $this->end_controls_section();

        // Toggle Button Style Section
        $this->start_controls_section(
            'eael_toggle_button_style',
            [
                'label' => esc_html__( 'Toggle Button', 'essential-addons-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'eael_image_comp_interaction' => 'toggle',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'toggle_button_typography',
                'label'     => esc_html__( 'Typography', 'essential-addons-elementor' ),
                'selector'  => '{{WRAPPER}} .eael-img-comp-toggle-btns .eael-img-comp-toggle-btn .eael-img-comp-toggle-text',
                'condition' => [
                    'eael_toggle_content_type' => ['text', 'both'],
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_button_width',
            [
                'label'      => esc_html__( 'Width', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range'      => [
                    'px' => [
                        'min' => 50,
                        'max' => 300,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .eael-img-comp-toggle-btns .eael-img-comp-toggle-btn' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_button_height',
            [
                'label'      => esc_html__( 'Height', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 30,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .eael-img-comp-toggle-btns .eael-img-comp-toggle-btn' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_button_padding',
            [
                'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .eael-img-comp-toggle-btns' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_icon_size',
            [
                'label'      => esc_html__( 'Icon Size', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .eael-img-comp-toggle-btns .eael-img-comp-toggle-btn .eael-img-comp-toggle-icon:not(svg)' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .eael-img-comp-toggle-btns .eael-img-comp-toggle-btn svg.eael-img-comp-toggle-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'eael_toggle_content_type' => ['icon', 'both'],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'toggle_button_background',
                'label'    => esc_html__( 'Background', 'essential-addons-elementor' ),
                'types'    => ['classic', 'gradient'],
                'exclude'  => [
                    'image',
                ],
                'selector' => '{{WRAPPER}} .eael-img-comp-toggle-btns',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'toggle_button_border',
                'selector' => '{{WRAPPER}} .eael-img-comp-toggle-btns',
            ]
        );

        $this->add_control(
            'toggle_button_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'text-domain' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors' => [
                    '{{WRAPPER}} .eael-img-comp-toggle-btns' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_button_switch_heading',
            [
                'label' => esc_html__( 'Active Switch', 'text-domain' ),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'toggle_button_switch_background',
                'types' => [ 'classic', 'gradient' ],
                'exclude'  => [
                    'image',
                ],
                'selector' => '{{WRAPPER}} .eael-img-comp-toggle-btns::before',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'toggle_button_switch_border',
                'selector' => '{{WRAPPER}} .eael-img-comp-toggle-btns::before',
            ]
        );

        $this->add_responsive_control(
            'toggle_button_switch_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'text-domain' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors' => [
                    '{{WRAPPER}} .eael-img-comp-toggle-btns::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( 'toggle_button_style_tabs' );
        
        $this->start_controls_tab(
            'toggle_button_normal_tab',
            [
                'label' => esc_html__( 'Normal', 'text-domain' ),
            ]
        );
        
        $this->add_control(
            'toggle_button_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'essential-addons-elementor' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eael-img-comp-wrapper .eael-img-comp-toggle-btns .eael-img-comp-toggle-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eael-img-comp-wrapper .eael-img-comp-toggle-btns .eael-img-comp-toggle-icon' => 'color: {{VALUE}};fill: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'toggle_button_active_tab',
            [
                'label' => esc_html__( 'Active', 'text-domain' ),
            ]
        );
        
        $this->add_control(
            'toggle_button_text_color_active',
            [
                'label'     => esc_html__( 'Text Color', 'essential-addons-elementor' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .eael-img-comp-wrapper .eael-img-comp-toggle-btns .active .eael-img-comp-toggle-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .eael-img-comp-wrapper .eael-img-comp-toggle-btns .active .eael-img-comp-toggle-icon' => 'color: {{VALUE}};fill: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();

        $this->end_controls_section();

    }

    protected function render() {
        /**
         * Getting the options from user.
         */
        $settings = $this->get_settings_for_display();
        $before_image = $settings['before_image'];
        $after_image = $settings['after_image'];
        $eael_compar_image_size = $settings['eael_before_image_size_size'];
        $eael_compar_before_image_url = wp_get_attachment_image_src( $before_image['id'], $eael_compar_image_size );
        $eael_compar_after_image_url = wp_get_attachment_image_src( $after_image['id'], $eael_compar_image_size );

        // Get interaction mode with backward compatibility
        $interaction_mode = $this->get_interaction_mode( $settings );

        $this->add_render_attribute(
            'wrapper',
            [
                'id'                => 'eael-image-comparison-' . esc_attr( $this->get_id() ),
                'class'             => ['eael-img-comp-container','twentytwenty-container'],
                'data-offset'       => ( $settings['eael_image_comp_offset']['size'] / 100 ),
                'data-orientation'  => $settings['eael_image_comp_orientation'],
                'data-before_label' => $settings['before_image_label'],
                'data-after_label'  => $settings['after_image_label'],
                'data-overlay'      => $settings['eael_image_comp_overlay'],
                'data-onhover'      => $interaction_mode['hover'] ? 'yes' : 'no',
                'data-onclick'      => $interaction_mode['click'] ? 'yes' : 'no',
            ]
        );
        echo '<div class="eael-img-comp-wrapper">';
        if ( $interaction_mode['toggle'] ) {
                $speed = $settings['eael_image_comp_step']['size'] ?? 10;
                $speed = 50 - $speed;
                $this->add_render_attribute( 'toggle-button', 'data-step', $speed );
                $this->add_render_attribute( 'toggle-button', 'class', 'eael-img-comp-toggle-btns' );
            ?>
            <div <?php $this->print_render_attribute_string( 'toggle-button' ); ?>>
                <button class="eael-img-comp-toggle-btn active" data-state="after">
                    <?php if( $settings['eael_toggle_content_type'] === 'icon' || $settings['eael_toggle_content_type'] === 'both' ) { ?>
                        <?php Icons_Manager::render_icon( $settings['eael_toggle_before_icon'], [ 'aria-hidden' => 'true', 'class' => 'eael-img-comp-toggle-icon' ] ); ?>
                    <?php } ?>
                    <span class="eael-img-comp-toggle-text"><?php echo esc_html( $settings['eael_toggle_before_text'] ); ?></span>
                </button>
                <button class="eael-img-comp-toggle-btn" data-state="before">
                    <?php if( $settings['eael_toggle_content_type'] === 'icon' || $settings['eael_toggle_content_type'] === 'both' ) { ?>
                        <?php Icons_Manager::render_icon( $settings['eael_toggle_after_icon'], [ 'aria-hidden' => 'true', 'class' => 'eael-img-comp-toggle-icon' ] ); ?>
                    <?php } ?>
                    <span class="eael-img-comp-toggle-text"><?php echo esc_html( $settings['eael_toggle_after_text'] ); ?></span>
                </button>
            </div>
        <?php }
        echo '<div '; $this->print_render_attribute_string( 'wrapper' ); echo '>
			<img class="eael-before-img" alt="' . esc_attr( $settings['before_image_alt'] ) . '" src="' . ( $eael_compar_before_image_url ? esc_url( $eael_compar_before_image_url[0] ) : esc_url( $before_image['url'] ) ) . '">
			<img class="eael-after-img" alt="' . esc_attr( $settings['after_image_alt'] ) . '" src="' . ( $eael_compar_after_image_url ? esc_url( $eael_compar_after_image_url[0] ) : esc_url( $after_image['url'] ) ) . '">
        </div>';
		echo '</div>';
    }
}
