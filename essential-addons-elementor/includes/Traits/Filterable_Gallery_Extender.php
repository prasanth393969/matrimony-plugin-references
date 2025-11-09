<?php
namespace Essential_Addons_Elementor\Pro\Traits;

if (!defined('ABSPATH')) {
   exit;
} // Exit if accessed directly

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Typography;
use \Essential_Addons_Elementor\Pro\Classes\Helper;

trait Filterable_Gallery_Extender{
   /**
     *  Repeater Controller
     *
     * @param [type] $repeater
     * @return void
     * eael_grid_fg_item_animator_popover_control
     */
   public function eael_grid_fg_item_animator_popover_control( $obj ) {

      $obj->add_control(
         'eael_fg_gallery_item_name_heading',
         [
            'label'     => esc_html__('Gallery Item Title', 'essential-addons-elementor'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
            'condition' => [
               'eael_fg_caption_style' => [ 'grid_flow_gallery' ]
            ],
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_tag_writing_mode',
         [
            'label'   => esc_html__( 'Writing Mode', 'essential-addons-elementor' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'vertical-lr',
            'options' => [
               'horizontal-tb' => esc_html__( 'Horizontal TB', 'essential-addons-elementor' ),
               'vertical-lr'   => esc_html__( 'Vertical LR', 'essential-addons-elementor' ),
               'sideways-lr'   => esc_html__( 'Sideways LR', 'essential-addons-elementor' ),
            ],
            'condition' => [
               'eael_fg_caption_style' => [ 'grid_flow_gallery' ]
            ],
         ]
      );

      $obj->add_control(
            'eael_fg_gallery_item_key_heading',
            [
               'label'     => esc_html__('Gallery Filter Title', 'essential-addons-elementor'),
               'type'      => Controls_Manager::HEADING,
               'separator' => 'before',
               'condition' => [
               'eael_fg_caption_style' => [ 'grid_flow_gallery' ]
            ],
            ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_key_heading_enable',
         [
            'label'        => esc_html__( 'Show', 'essential-addons-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => esc_html__( 'Yes', 'essential-addons-elementor' ),
            'label_off'    => esc_html__( 'No', 'essential-addons-elementor' ),
            'return_value' => 'yes',
            'condition'    => [
               'eael_fg_caption_style' => [ 'grid_flow_gallery' ]
            ],
         ]
      );

      $obj->add_control(
         'eael_tag_hr',
         [
            'type' => \Elementor\Controls_Manager::DIVIDER,
            'condition'    => [
               'eael_fg_caption_style' => [ 'grid_flow_gallery' ]
            ],
         ]
      );  

   }

   /**
    * Undocumented function
    *
    * @param [type] $repeater
    * @return void
    */
   public function eael_grid_flow_gallery_icon_control( $repeater ) {
      $repeater->add_control(
         'eael_fg_gallery_item_tag_icon_heading',
         [
            'label'     => esc_html__('Gallery Icon', 'essential-addons-elementor'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
         ]
      );

      $repeater->add_control(
         'eael_fg_gallery_item_tag_icon_enable',
         [
            'label'        => esc_html__( 'Show', 'essential-addons-elementor' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => esc_html__( 'Yes', 'essential-addons-elementor' ),
            'label_off'    => esc_html__( 'No', 'essential-addons-elementor' ),
            'return_value' => 'yes',
         ]
      );

      $repeater->add_control(
         'eael_fg_gallery_item_tag_icon',
         [
            'label'   => esc_html__( 'Select Gallery Icon', 'essential-addons-elementor' ),
            'type'    => \Elementor\Controls_Manager::ICONS,
            'default' => [
               'value'   => 'fas fa-rocket',
               'library' => 'fa-solid',
            ],
         ]
      );

      $repeater->add_control(
         'eael_grid_flow_gallery_notice',
         [
            'type'        => Controls_Manager::NOTICE,
            'notice_type' => 'info',
            'dismissible' => false,
            'content'     => sprintf( '<strong>%s</strong> %s',
                     esc_html__('Icon', 'essential-addons-for-elementor-lite'),
                              sprintf( esc_html__('will be visible only on the "%1$sGrid Flow%2$s" layout.', 'essential-addons-for-elementor-lite'), '<strong>', '</strong>' ) ),
         ]
      );
   }

   /**
     * Undocumented function
     *
     * @param [type] $obj
     * @return void
     */
   public function eael_grid_flow_gallery_style_control( $obj ) {
      $obj->start_controls_section(
			'eael_gril_flow_section',
			[
				'label'     => esc_html__( 'Grid Flow', 'essential-addons-for-elementor-lite' ),
				'tab'       => Controls_Manager::TAB_STYLE,
               'condition' => [
                  'eael_fg_caption_style' => [ 'grid_flow_gallery' ]
               ],
			]
		);

      $obj->add_control(
         'eael_fg_gallery_transition_duration',
         [
            'label'     => esc_html__('Transition Duration (ms)', 'essential-addons-elementor'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [
               'px' => [
                  'min'  => 0,
                  'max'  => 3000,
                  'step' => 100,
               ],
            ],
            'default'   => [
               'size' => 500,
            ],
            'selectors' => [
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-item' => 'transition-duration: {{SIZE}}ms;',
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-item .eael-grid-fg-box' => 'transition-duration: {{SIZE}}ms;',
            ],
         ]
      );

      $obj->add_group_control(
         \Elementor\Group_Control_Background::get_type(),
         [
            'name'      => 'eael_fg_item_background',
            'types'     => [ 'classic', 'gradient' ],
            'selector'  => '{{WRAPPER}} .eael-filterable-gallery-item-wrap .box__shadow',
            'condition' => [ 'eael_fg_caption_style' => 'grid_flow_gallery' ],
            'fields_options' => [
               'background' => [
                     'default' => 'classic',
               ],
               'image' => [
                     'default' => [
                        'url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAOklEQVQoU43MSwoAMAgD0eT+h7ZYaOlHo7N+DNHL2HAGgBWcyGcKbqTghTL4oQiG6IUpOqFEC5bI4QD8PAoKd9j4XwAAAABJRU5ErkJggg==',
                     ],
               ],
            ],
         ]
      );

      $obj->start_controls_tabs(
         'eael_style_grid_tabs'
      );

      $obj->start_controls_tab(
         'eael_style_grid_tab',
         [
            'label' => esc_html__( 'Grid', 'essential-addons-elementor' ),
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_tag_grid',
         [
            'label'     => esc_html__('Gallery Item Title', 'essential-addons-elementor'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_tag_stroke_fill_color',
         [
            'label'     => esc_html__('Fill Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#000',
            'selectors' => [
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-title .eael-grid-fg-title-inner::before,
               {{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-title .eael-grid-fg-title-inner' => '-webkit-text-fill-color: {{VALUE}};',
            ],
         ]
      );

      $obj->add_group_control(
         \Elementor\Group_Control_Text_Stroke::get_type(),
         [
            'name'     => 'eael_fg_gallery_item_tag_text_stroke',
            'selector' => '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-title .eael-grid-fg-title-inner[data-hover], 
               {{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-title .eael-grid-fg-title-inner',
         ]
      );

      $obj->add_group_control(
         Group_Control_Typography::get_type(),
         [
            'name'     => 'eael_fg_gallery_item_tag_stroke_typography',
            'selector' => '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-title .eael-grid-fg-title-inner, 
            {{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-title .eael-grid-fg-title-inner',
            'fields_options' => [
               'typography' => [ 
                     'default' => 'yes' 
               ],
               'font_size' => [
                     'default' => [
                        'size' => 26,
                     ],
                     'tablet_default' => [
                        'size' => 22,
                     ],
                     'mobile_default' => [
                        'size' => 14,
                     ],
               ],
            ],
         ]
      );

      $obj->add_responsive_control(
			'eael_fg_gallery_item_tag_top',
			[
				'label'              => esc_html__( 'Position', 'essential-addons-elementor' ),
				'type'               => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units'         => [ 'px', '%', 'rem', 'custom' ],
				'allowed_dimensions' => [ 'top', 'right' ],
				'selectors'          => [
					'{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-item .eael-grid-fg-title' => 'top: {{TOP}}{{UNIT}}; right: {{RIGHT}}{{UNIT}};',
				],
			]
		);

      $obj->add_control(
         'eael_fg_gallery_item_key_style_heading',
         [
            'label'     => esc_html__('Gallery Filter Title', 'essential-addons-elementor'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_key_text_color',
         [
            'label'     => esc_html__('Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#000',
            'selectors' => [
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-control-name,
               .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-control-name .eael-grid-fg-control-name-inner' => 'color: {{VALUE}};',
            ],
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_key_text_hover_color',
         [
            'label'     => esc_html__('Hover Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#000',
            'selectors' => [
               '{{WRAPPER}} .eael-grid-fg-item:hover .eael-grid-fg-box .eael-grid-fg-control-name' => 'color: {{VALUE}};',
            ],
         ]
      );

      $obj->add_group_control(
         Group_Control_Typography::get_type(),
         [
            'name'     => 'eael_fg_gallery_item_key_typography',
            'selector' => '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-control-name,
            .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .box__text .eael-grid-fg-control-name-inner',
            'fields_options' => [
               'typography' => [ 
                     'default' => 'yes' 
               ],
               'font_size' => [
                     'default' => [
                        'size' => 16,
                     ],
                     'tablet_default' => [
                        'size' => 16,
                     ],
                     'mobile_default' => [
                        'size' => 14,
                     ],
               ],
            ],
         ]
      );

      $obj->add_group_control(
         \Elementor\Group_Control_Text_Stroke::get_type(),
         [
            'name'     => 'eael_fg_gallery_item_key_text_stroke',
            'selector' => '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-control-name,
               .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-control-name .eael-grid-fg-control-name-inner',
         ]
      );

      $obj->add_group_control(
         Group_Control_Border::get_type(),
         [
            'name'     => 'eael_fg_gallery_item_key_border',
            'selector' => '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-control-name .eael-grid-fg-control-name-inner,
               .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-control-name .eael-grid-fg-control-name-inner',
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_key_border_radius',
         [
            'label'      => esc_html__( 'Border Radius', 'essential-addons-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
            'selectors'  => [
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-control-name .eael-grid-fg-control-name-inner,
                  .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-control-name .eael-grid-fg-control-name-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
         ]
      );

      $obj->add_responsive_control(
			'eael_fg_gallery_item_key',
			[
				'label'              => esc_html__( 'Position', 'essential-addons-elementor' ),
				'type'               => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units'         => [ 'px', '%', 'rem', 'custom' ],
				'allowed_dimensions' => [ 'top', 'right' ],
				'selectors'          => [
					'{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-item .eael-grid-fg-control-name' => 'top: {{TOP}}{{UNIT}}; right: {{RIGHT}}{{UNIT}};',
				],
			]
		);

      $obj->add_control(
         'eael_fg_gallery_item_key_border_hover_color',
         [
            'label'     => esc_html__('Border Hover Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#000',
            'selectors' => [
               '{{WRAPPER}} .eael-grid-fg-item:hover .eael-grid-fg-box .eael-grid-fg-control-name .eael-grid-fg-control-name-inner' => 'border-color: {{VALUE}};',
            ],
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_key_padding',
         [
            'label'      => esc_html__( 'Padding', 'essential-addons-elementor' ),
            'type'       => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
            'selectors'  => [
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-control-name .eael-grid-fg-control-name-inner,
                  .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-control-name .eael-grid-fg-control-name-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_tag_iocn',
         [
            'label'     => esc_html__('Icon', 'essential-addons-elementor'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_tag_icon_size',
         [
            'label'      => esc_html__( 'Size', 'essential-addons-elementor' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%', 'rem', 'custom' ],
            'range'      => [
               'px' => [
                  'min'  => 0,
                  'max'  => 100,
                  'step' => 1,
               ],
               '%' => [
                  'min' => 0,
                  'max' => 100,
               ],
            ],
            'default' => [
               'unit' => 'px',
               'size' => 35,
            ],
            'selectors' => [
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                  '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
            ],
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_tag_icon_color',
         [
            'label'     => esc_html__('Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#000',
            'selectors' => [
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-icon svg' => 'fill: {{VALUE}};',
               '{{WRAPPER}} .eael-grid-fg-item .eael-grid-fg-box .eael-grid-fg-icon i' => 'color: {{VALUE}};',
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-icon svg' => 'fill: {{VALUE}};',
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .eael-grid-fg-icon i' => 'color: {{VALUE}};',
            ],
         ]
      );

      $obj->add_control(
         'eael_fg_gallery_item_tag_icon_hover_color',
         [
            'label'     => esc_html__('Hover Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#000',
            'selectors' => [
               '{{WRAPPER}} .eael-grid-fg-item:hover .eael-grid-fg-box .eael-grid-fg-icon svg' => 'fill: {{VALUE}};',
               '{{WRAPPER}} .eael-grid-fg-item:hover .eael-grid-fg-box .eael-grid-fg-icon i' => 'color: {{VALUE}};',
            ],
         ]
      );

      $obj->add_responsive_control(
			'eael_fg_gallery_item_tag_icon_possition',
			[
				'label'              => esc_html__( 'Position', 'essential-addons-elementor' ),
				'type'               => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units'         => [ 'px', '%', 'rem', 'custom' ],
				'allowed_dimensions' => [ 'right', 'bottom' ],
				'selectors'          => [
					'{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-item .eael-grid-fg-icon' => 'right: {{RIGHT}}{{UNIT}}; bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

      $obj->end_controls_tab();

      // Oberlay Style
      $obj->start_controls_tab(
         'eael_style_grid_overlay_tab',
         [
            'label' => esc_html__( 'Overlay', 'essential-addons-elementor' ),
         ]
      );

      $obj->add_control(
         'eael_fg_grid_overlay_bg_color',
         [
            'label'     => esc_html__('Background Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .overlay__item' => 'background-color: {{VALUE}};'
            ],
         ]
      );

      $obj->add_control(
         'eael_fg_grid_overlay_open_bg_color',
         [
            'label'     => esc_html__('Transition Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .overlay__reveal,
               {{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay .overlay__reveal' => 'background-color: {{VALUE}};'
            ],
         ]
      );

      $obj->add_control(
			'eael_fg_grid_overlay_transition_duration',
			[
				'label' => esc_html__( 'Transition Duration (s) ', 'essential-addons-elementor' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 0.1,
						'max'  => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.4,
				],
			]
		);

      $obj->add_control(
         'eael_fg_grid_overlay_title_typography',
         [
            'label'     => esc_html__('Title', 'essential-addons-elementor'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
         ]
      );

      $obj->add_control(
         'eael_fg_grid_overlay_title_color',
         [
            'label'     => esc_html__('Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'selectors' => [
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .overlay__content .fg-title' => 'color: {{VALUE}};'
            ],
         ]
      );

      $obj->add_group_control(
         Group_Control_Typography::get_type(),
         [
            'name'     => 'eael_fg_grid_overlay_title_typography',
            'selector' => '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .overlay__content .fg-title',
         ]
      );

      $obj->add_control(
         'eael_fg_grid_overlay_content_typography',
         [
            'label'     => esc_html__('Content', 'essential-addons-elementor'),
            'type'      => Controls_Manager::HEADING,
            'separator' => 'before',
         ]
      );

      $obj->add_control(
         'eael_fg_grid_overlay_content_color',
         [
            'label'     => esc_html__('Color', 'essential-addons-elementor'),
            'type'      => Controls_Manager::COLOR,
            'default'   => '#444',
            'selectors' => [
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .overlay__content p' => 'color: {{VALUE}};',
            ],
         ]
      );

      $obj->add_group_control(
         Group_Control_Typography::get_type(),
         [
            'name'     => 'eael_fg_grid_overlay_content_typography',
            'selector' => '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-overlay.overlay--open .overlay__content p',
         ]
      );

      $obj->end_controls_tab();
      $obj->end_controls_tabs();

      $obj->end_controls_section();

   }

   public function eael_harmonic_gallery_style_control( $obj ) {

      $obj->start_controls_section(
			'eael_harmonic_style_section',
			[
				'label'     => esc_html__( 'Harmonic', 'essential-addons-for-elementor-lite' ),
				'tab'       => Controls_Manager::TAB_STYLE,
               'condition' => [
                  'eael_fg_caption_style' => [ 'harmonic_gallery' ]
               ],
			]
		);

      $obj->add_control(
         'eael_harmonic_gallery_transition_duration',
         [
            'label'     => esc_html__('Transition Duration (ms)', 'essential-addons-elementor'),
            'type'      => Controls_Manager::SLIDER,
            'range'     => [
               'px' => [
                  'min'  => 0,
                  'max'  => 3000,
                  'step' => 100,
               ],
            ],
            'default'   => [
               'size' => 500,
            ],
            'selectors' => [
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-hg-grid__cell' => 'transition-duration: {{SIZE}}ms;',
               '{{WRAPPER}} .eael-filter-gallery-wrapper .eael-grid-fg-item .eael-hg-grid__cell-img' => 'transition-duration: {{SIZE}}ms;',
            ],
         ]
      );

      $obj->add_control(
         'eael_hg_title_section',
         [
            'label'     => esc_html__( 'Title', 'essential-addons-elementor' ),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'after',
         ]
      );

      $obj->add_control(
            'eael_hg_title_color',
            [
               'label'     => esc_html__('Color', 'essential-addons-elementor'),
               'type'      => Controls_Manager::COLOR,
               'selectors' => [
                  '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-heading' => 'color: {{VALUE}};'
               ],
            ]
      );

      $obj->add_group_control(
         \Elementor\Group_Control_Typography::get_type(),
         [
            'name' => 'eael_hg_title_typography',
            'selector' => '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-heading',
         ]
      );

      $obj->add_responsive_control(
         'eael_hg_title_margin',
         [
            'label' => esc_html__( 'Margin', 'essential-addons-elementor' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'rem', 'custom' ],
            'selectors' => [
               '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-heading' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
         ]
      );

      $obj->add_responsive_control(
         'eael_hg_title_padding',
         [
            'label' => esc_html__( 'Padding', 'essential-addons-elementor' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'rem', 'custom' ],
            'selectors' => [
               '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-heading' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
         ]
      );

      $obj->add_control(
         'eael_hg_content_section',
         [
            'label'     => esc_html__( 'Content', 'essential-addons-elementor' ),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'after',
         ]
      );

      $obj->add_control(
            'eael_hg_content_color',
            [
               'label'     => esc_html__('Color', 'essential-addons-elementor'),
               'type'      => Controls_Manager::COLOR,
               'selectors' => [
                  '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-text' => 'color: {{VALUE}};'
               ],
            ]
      );

      $obj->add_group_control(
         \Elementor\Group_Control_Typography::get_type(),
         [
            'name' => 'eael_hg_content_typography',
            'selector' => '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-text',
         ]
      );

      $obj->add_responsive_control(
         'eael_hg_content_margin',
         [
            'label' => esc_html__( 'Margin', 'essential-addons-elementor' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'rem', 'custom' ],
            'selectors' => [
               '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
         ]
      );

      $obj->add_responsive_control(
         'eael_hg_content_padding',
         [
            'label' => esc_html__( 'Padding', 'essential-addons-elementor' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'rem', 'custom' ],
            'selectors' => [
               '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
         ]
      );

      $obj->add_control(
         'eael_hg_link_btn_section',
         [
            'label'     => esc_html__( 'Link', 'essential-addons-elementor' ),
            'type'      => \Elementor\Controls_Manager::HEADING,
            'separator' => 'after',
         ]
      );

      $obj->add_control(
            'eael_hg_link_btn_color',
            [
               'label'     => esc_html__('Color', 'essential-addons-elementor'),
               'type'      => Controls_Manager::COLOR,
               'selectors' => [
                  '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-link' => 'color: {{VALUE}};'
               ],
            ]
      );

      $obj->add_group_control(
         \Elementor\Group_Control_Typography::get_type(),
         [
            'name' => 'eael_hg_link_btn_typography',
            'selector' => '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-link',
         ]
      );

      $obj->add_responsive_control(
         'eael_hg_link_btn_padding',
         [
            'label' => esc_html__( 'Padding', 'essential-addons-elementor' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'rem', 'custom' ],
            'selectors' => [
               '{{WRAPPER}} .eael-hg-content .eael-hg-content__item .eael-hg-content__item-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
         ]
      );

      $obj->end_controls_section();

   }

   /**
   * Undocumented function
   *
   * @param [type] $icon
   * @return void
   */
   protected function get_icon_html( $icon ) {
      if ( empty( $icon ) || ! is_array( $icon ) || empty( $icon['value'] ) ) {
            return '';
      }

      ob_start();
      \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
      return ob_get_clean();
   }

   /**
   * Builds content title if it exists
   */
   private function build_content_title( $gallery, $title_tag ) {
      if ( empty( $gallery['title'] ) ) {
            return '';
      }
      return sprintf( '<%s class="fg-title">%s</%s>', $title_tag, esc_html( $gallery['title'] ), $title_tag );
   }

   /**
      * Builds image markup
      */
   private function build_image_markup( $gallery ) {
         return sprintf( '<div class="eael-grid-fg-img"><img class="eael-grid-fg-box__img" src="%s" alt="%s" /></div>', esc_url( $gallery['image'] ), esc_attr( $gallery['title'] ) );
   }

   /**
   * Builds icon markup
   */
   private function build_icon_markup( $gallery, $position ) {
      if ( 'yes' !== $gallery['tag_icon_enable'] ) {
         return;
      }
      return sprintf( '<div class="eael-grid-fg-icon">%s</div>', $this->get_icon_html( $gallery['tag_icon'] ) );
   }

   private function eael_controls_name_markup( $gallery, $settings ) {
      if ( empty( $settings['eael_fg_gallery_item_key_heading_enable'] ) || 'yes' !== $settings['eael_fg_gallery_item_key_heading_enable'] ) {
         return;
      }

      $control_name = ! empty( $gallery['controls_name'] ) ? $gallery['controls_name'] : esc_html__( 'Gallery', 'essential-addons-elementor' );
      // Return markup
      return sprintf( '<h4 class="eael-grid-fg-control-name"><span class="eael-grid-fg-control-name-inner">%s</span></h4>', esc_html( $control_name ) );
   }  

   private function eael_grid_fg_item_name_markup( $gallery, $settings, $title_tag ) {
      $writing_mode    = isset( $settings['eael_fg_gallery_item_tag_writing_mode'] ) ? esc_attr( $settings['eael_fg_gallery_item_tag_writing_mode'] ) : '';
      $styles = sprintf( 'writing-mode: %s;', $writing_mode );
      return sprintf( '<%s style="%s" class="eael-grid-fg-title"><span class="eael-grid-fg-title-inner" data-hover="%s">%s</span></%s>', $title_tag, $styles, esc_attr( $gallery['title'] ), esc_html( $gallery['title'] ), $title_tag );
   }

   private function build_gallery_item( $gallery, $title_tag, $index, $widget_id, $settings ) {
      $class = "eael-filterable-gallery-item-wrap eael-grid-fg-item eael-grid__item eael-cf-" . esc_attr( $gallery['controls'] );
            
      $html = "<div class='{$class}' data-item='#preview-{$widget_id}-{$index}'>";
      $html .= '<div class="eael-grid-fg-box">';
      $html .= '<div class="box__shadow"></div>';
      $html .= $this->build_image_markup( $gallery );
      $html .= $this->eael_grid_fg_item_name_markup( $gallery, $settings, $title_tag );
      $html .= $this->eael_controls_name_markup( $gallery, $settings );
      $html .= $this->build_icon_markup( $gallery, $settings );
      // $html .= '<div class="eael-grid-fg-title eael-gf-box__content">' . $this->build_content_title( $gallery, $title_tag ) . '</div>';
      $html .= '</div></div>';

      return $html;
   }

   protected function render_gallery_items_pro( $settings, $gallery_items_pro, $widget_id ) {
      $gallery_markup = [];
      $title_tag = Helper::eael_validate_html_tag( $settings['title_tag'] );

      foreach ( $gallery_items_pro as $index=> $gallery ) {
            $gallery_markup[] = $this->build_gallery_item( $gallery, $title_tag, $index, $widget_id, $settings );
      }
      return $gallery_markup;
   }

   /**
   * Render Harmonic Gallery
   *
   * @return void
   */

   private function eael_render_harmonic_gallery_grid( $gallery, $index, $widget_id ) {
      $index = $index + 1;
      $image_url = ! empty( $gallery['image'] ) ? esc_url( untrailingslashit( $gallery['image'] ) ) : EAEL_PRO_PLUGIN_URL . '/assets/front-end/img/eael-default-placeholder.png';
      $html = '<div class="eael-filterable-gallery-item-wrap eael-cf-'.esc_attr( $gallery['controls'] ).' eael-hg-grid__cell">';
      $html .= '<div class="eael-hg-grid__cell-img">';
      $html .= '<div class="grid__cell-img-inner" data-item="item-'.$widget_id.'-'.$index.'">';
      $html .= "<img src='{$image_url}' alt='{$gallery['title']}' />";
      $html .= '</div>';
      $html .= '</div>';
      $html .= '</div>';

      return $html;
   }

   /**
   * Render the main gallery wrapper
   *
   * @param [type] $gallery_items_pro
   * @param [type] $gallery_items_to_show
   * @return void
   */

   private function eael_render_blank_cell() {
      return '<div class="eael-filterable-gallery-item-wrap eael-hg-grid__cell eael-hg-grid__cell--blank"></div>';
   }

   private function count_items_in_pattern( $pattern ) {
      $count = 0;
      foreach ( $pattern as $row ) {
            $count += array_sum( $row );
      }
      return $count;
   }

   private function get_grid_pattern() {
      $grid_pattern_3 = [
            [1, 0, 1],
            [0, 1, 0],
            [1, 0, 1],
      ];
      
      $grid_pattern_4 = [
            [1, 0, 1, 1],
            [0, 1, 0, 1],
            [1, 0, 1, 0],
      ];

      $grid_pattern_5 = [
            [1, 0, 1, 1, 0],
            [0, 1, 0, 1, 0],
            [1, 0, 1, 0, 1],
      ];

      $grid_pattern_6 = [
            [1, 0, 1, 1, 0, 1],
            [0, 1, 0, 1, 0, 1],
            [1, 0, 1, 0, 1, 0],
      ];

      $grid_patterns = [
            3 => $grid_pattern_3,
            4 => $grid_pattern_4,
            5 => $grid_pattern_5,
            6 => $grid_pattern_6,
      ];

      return $grid_patterns;
   }

   private function render_harmonic_gallery_wrapper( $gallery_items_pro, $gallery_items_to_show, $items_columns, $widget_id ) {
      $cols              = max( 1, (int)$items_columns );
      // Use all available items instead of limiting by $gallery_items_to_show
      $items_to_show     = count( $gallery_items_pro );
      $gallery_markup    = [];
      $gallery_index     = 0;
      $total_rows_needed = 0;

      // Store the original items order to maintain consistency
      $ordered_items = array_values($gallery_items_pro);

      if ( $cols <= 2 ) {
            $total_rows_needed = ceil( $items_to_show / $cols );
            for ( $row = 0; $row < $total_rows_needed; $row++ ) {
               for ( $col = 0; $col < $cols; $col++ ) {
                  $position = $row * $cols + $col;
                  if ( $gallery_index < $items_to_show ) {
                        $gallery_markup[ $position ] = $this->eael_render_harmonic_gallery_grid( $ordered_items[ $gallery_index ], $gallery_index, $widget_id );
                        $gallery_index++;
                  } else {
                        $gallery_markup[ $position ] = $this->eael_render_blank_cell();
                  }
               }
            }
      } else {
            // Select the appropriate grid pattern
            $grid_patterns = $this->get_grid_pattern();
            
            // Default to grid_pattern if $cols is not 3, 4, 5, or 6
            $grid_pattern    = isset( $grid_patterns[ $cols ] ) ? $grid_patterns[ $cols ] : $grid_patterns[4];
            $rows_per_cycle  = count( $grid_pattern );
            $items_per_cycle = $this->count_items_in_pattern( $grid_pattern );

            // Calculate total rows needed for all items
            $total_rows_needed = ceil( $items_to_show / $items_per_cycle ) * $rows_per_cycle;

            // Render grid - ensure consistent ordering
            for ( $row = 0; $row < $total_rows_needed; $row++ ) {
               $pattern_row = $grid_pattern[ $row % $rows_per_cycle ];
               for ( $col = 0; $col < $cols; $col++ ) {
                  $position = $row * $cols + $col;
                  if ( isset( $pattern_row[ $col ] ) && $pattern_row[ $col ] === 1 && $gallery_index < $items_to_show ) {
                        $gallery_markup[ $position ] = $this->eael_render_harmonic_gallery_grid( $ordered_items[ $gallery_index ], $gallery_index, $widget_id );
                        $gallery_index++;
                  } else {
                        $gallery_markup[ $position ] = $this->eael_render_blank_cell();
                  }
               }
            }
      }
      return $gallery_markup;
   }

   /**
   * Render the navigation section of the harmonic gallery
   */
   private function eael_render_harmonic_gallery_navigation( $gallery_items_pro, $gallery_items_to_show ) {
      ?>
      <nav class="eael-hg-mini-wrapper eael-hg-grid--mini">
            <?php 
               $items_count = min( $gallery_items_to_show, count( $gallery_items_pro ) );
               for ( $i = 0; $i < count( $gallery_items_pro ); $i++ ) {
                  if ( isset( $gallery_items_pro[$i] ) ) {
                     $this->eael_render_harmonic_gallery_navigation_item( $gallery_items_pro[$i], $i );
                  }
               }
            ?>
      </nav>
      <?php
   }

   /**
   * Render the navigation item of the harmonic gallery item
   */
   private function eael_render_harmonic_gallery_navigation_item( $gallery, $index ) {
      $index = $index + 1;
      $image_url = ! empty( $gallery['image'] ) ? esc_url( untrailingslashit( $gallery['image'] ) ) : EAEL_PRO_PLUGIN_URL . '/assets/front-end/img/eael-default-placeholder.png';
      ?>
      <div class="eael-cf-<?php echo esc_attr( $gallery['controls'] ) ?> eael-hg-grid__cell grid__cell-c<?php echo esc_attr( $index ); ?>-r1" data-filter-category="eael-cf-<?php echo esc_attr( $gallery['controls'] ); ?>">
            <div class="eael-hg-grid__cell-img">
               <div class="grid__cell-img-inner">
                  <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $gallery['title'] ); ?>" />
               </div>
            </div>
      </div>
      <?php
   }

   /**
   * Render the gallery content section
   */
   private function eael_render_harmonic_gallery_content( $gallery, $index, $obj, $title_tag, $widget_id ) {
      $index = $index + 1;
      $title_tag = Helper::eael_validate_html_tag( $title_tag );
      ?>
      <div class="eael-hg-content__item eael-cf-<?php echo esc_attr( $gallery['controls'] ); ?>" id="item-<?php echo esc_attr( $widget_id ); ?>-<?php echo esc_attr( $index ); ?>" data-filter-category="eael-cf-<?php echo esc_attr( $gallery['controls'] ); ?>">
               <span class="content__item-number eael-split-oh">
                  <span class="eael-split-oh__inner"><?php echo sprintf( '%02d', $index ); ?></span>
               </span>

               <?php
               printf(
                  '<%1$s class="eael-hg-content__item-heading eael-split-oh"><span class="eael-split-oh__inner">%2$s</span></%1$s>',
                  esc_attr( $title_tag ),
                  esc_html( $gallery['title'] )
               );
               ?>

               <div class="eael-hg-content__item-text">
                  <?php echo wpautop( $gallery['content'] ); ?>
               </div>

               <?php
               if ( $gallery['maybe_link'] == true && ! empty( $gallery['link']['url'] ) ) {
                  $link_key = 'link_' . $index;
                  $obj->add_link_attributes( $link_key, $gallery['link'] );
                  $obj->add_render_attribute( $link_key, 'class', 'eael-hg-content__item-link eael-split-oh' );
                  ?>
                  <a <?php $obj->print_render_attribute_string( $link_key ); ?>>
                        <span class="eael-split-oh__inner"><?php esc_html_e( 'View More', 'essential-addons-elementor' ); ?></span>
                  </a>
                  <?php
               }
               ?>

               <nav class="slide-nav">
                  <div class="slide-nav__img slide-nav__img--prev">
                        
                  </div>
                  <div class="slide-nav__img slide-nav__img--next">
                        
                  </div>
               </nav>
            </div>
      <?php
   }

   /**
   * Undocumented function
   *
   * @param [type] $settings
   * @param [type] $gallery_items_pro
   * @return void
   */
   private function eael_render_harmonic_gallery( $settings, $gallery_items_pro, $widget_id ) {
      $gallery_items_to_show = absint( $settings['eael_fg_items_to_show'] );
      $items_columns         = absint( $settings['columns'] );
      $items_to_show         = min( count( $gallery_items_pro ), (int)$gallery_items_to_show );
      $grid_patterns         = $this->get_grid_pattern();
      $grid_pattern          = isset( $grid_patterns[ $items_columns ] ) ? $grid_patterns[ $items_columns ]: $grid_patterns[4];
      $rows_per_cycle        = count( $grid_pattern );
      $items_per_cycle       = $this->count_items_in_pattern( $grid_pattern );

      // Limit gallery items to the specified number to show
      $limited_gallery_items = array_slice($gallery_items_pro, 0, $items_to_show);
      
      // Calculate total rows needed for the full grid structure
      $gallery_markup = $this->render_harmonic_gallery_wrapper( $limited_gallery_items, $items_to_show, $items_columns, $widget_id );
      $total_rows_needed = ceil( $items_to_show / $items_per_cycle ) * $rows_per_cycle;
      $total_positions = $total_rows_needed * $items_columns;

      // Output all positions including blank cells
      for ($i = 0; $i < $total_positions; $i++) {
         if ( isset( $gallery_markup[ $i ] ) ) {
            echo $gallery_markup[ $i ];
         }
      }
   }

   /**
   * Grid Flow Gallery
   *
   * @param [type] $settings
   * @param [type] $obj
   * @param [type] $gallery_items_pro
   * @return void
   */
   public function add_filterable_gallery_pro_style( $settings, $obj, $gallery_items_pro ) {
      $gallery_settings = [
            'grid_style'               => $settings['eael_fg_grid_style'],
            'popup'                    => $settings['eael_fg_show_popup'],
            // 'duration'              => $filter_duration,
            'gallery_enabled'          => $settings['photo_gallery'],
            'video_gallery_yt_privacy' => $settings['video_gallery_yt_privacy'],
            'control_all_text'         => $settings['eael_fg_all_label_text'],
      ];

      $gallery_to_show = absint($settings['eael_fg_items_to_show']);
      $title_tag       = Helper::eael_validate_html_tag( $settings['title_tag'] );

      $gallery_settings['post_id']   = get_the_ID();
      $gallery_settings['widget_id'] = $obj->get_id();

      $no_more_items_text = esc_html($settings['nomore_items_text']);

      $obj->add_render_attribute('gallery-items-wrap-pro', [
            'data-images-per-page'     => $settings['images_per_page'],
            'data-total-gallery-items' => count($settings['eael_fg_gallery_items']),
            'data-nomore-item-text'    => $no_more_items_text,
      ]);

      $obj->add_render_attribute('gallery-items-wrap-pro', 'data-settings', wp_json_encode($gallery_settings));

      if( 'grid_flow_gallery' === $settings['eael_fg_caption_style'] ) {
            $widget_id = $obj->get_id();
            $gallery_items   = $this->render_gallery_items_pro( $settings, $gallery_items_pro, $widget_id );
            $html_json   = wp_json_encode( $gallery_items );
            $json_base64 = base64_encode( $html_json );
            $obj->add_render_attribute( 'gallery-items-wrap-pro', 'data-init-show', esc_attr($settings['eael_fg_items_to_show'] ) );
            $obj->add_render_attribute( 'gallery-items-wrap-pro', 'data-gallery-items', esc_attr( $json_base64 ) );

            $obj->add_render_attribute('gallery-items-wrap-pro', [
               'class' => ['eael-filter-gallery-container', 'eael-grid-fg'],
               'id' => [ 'eael-grid-fg-'.$widget_id ],
            ]);

            $transition_settings = [
               'transition_duration' => $settings['eael_fg_grid_overlay_transition_duration']['size'] ?? 0.4,
            ];

            $obj->add_render_attribute( 'grid-flow-item-overlfow', [
               'class'           => ['eael-grid-fg-overlay', 'overlay'],
               'id'              => ['overlay-'.$obj->get_id()],
               'data-transition' => wp_json_encode( $transition_settings ),
            ] );

            ?>
            <div class="eael-grid-fg-wrap">
               <div <?php $obj->print_render_attribute_string('gallery-items-wrap-pro'); ?>>
                  <?php
                  for ( $i = 0; $i < $gallery_to_show; $i++ ) {
                        if ( array_key_exists( $i, $gallery_items ) ) {
                           echo $gallery_items[$i];
                        }
                  }
                  ?>
               </div>
            </div>

            <div <?php $obj->print_render_attribute_string('grid-flow-item-overlfow'); ?>>
               <div class="overlay__reveal"></div>
               <?php 
               foreach ( $gallery_items_pro as $index=> $gallery ) {
                  $widget_id = $obj->get_id();
                  ?>
                  <div class="overlay__item" id="preview-<?php echo esc_attr( $widget_id ); ?>-<?php echo esc_attr( $index ); ?>">
                        <div class="eael-grid-fg-box">
                           <div class="box__shadow"></div>
                           <img class="eael-grid-fg-box__img box__img--original" src="<?php echo esc_url( $gallery['image'] ); ?>" alt="<?php esc_attr_e( $gallery['title'] ); ?>"/>
                           <h3 class="eael-grid-fg-title">
                              <span class="eael-grid-fg-title-inner"><?php //echo esc_html( $gallery['tag'] ); ?></span>
                           </h3>
                           <h4 class="eael-grid-fg-control-name">
                              <?php $control_name = $gallery['controls_name'] ? $gallery['controls_name'] : esc_html__( 'Gallery', 'essential-addons-elementor' ); ?>
                              <span class="eael-grid-fg-control-name-inner"><?php echo esc_html( $control_name ); ?></span>
                           </h4>
                           <div class="eael-grid-fg-icon"><?php echo $this->get_icon_html( $gallery['tag_icon'] ); ?></div>
                        </div>
                        <div class="overlay__content">
                           <?php echo $this->build_content_title( $gallery, $title_tag ); ?>
                           <?php echo wpautop( $gallery['content'] ); ?>
                        </div>
                  </div>
                  <?php
               }
               ?>

               <button class="overlay__close">
                  <svg class="icon icon--cross" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                        <path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
                  </svg>
               </button>
            </div>
            <?php
      } elseif( 'harmonic_gallery' == $settings['eael_fg_caption_style'] ) {
            $widget_id             = $obj->get_id();
            $gallery_items_to_show = absint( $settings['eael_fg_items_to_show'] );
            $items_columns         = absint( $settings['columns'] );
            $gallery_items         = $this->render_harmonic_gallery_wrapper( $gallery_items_pro, $gallery_items_to_show, $items_columns, $widget_id );
            $html_json             = wp_json_encode( $gallery_items );
            $json_base64           = base64_encode( $html_json );

            $obj->add_render_attribute( 'gallery-items-wrap-pro', 'data-init-show', esc_attr($settings['eael_fg_items_to_show'] + 5 ) );
            $obj->add_render_attribute( 'gallery-items-wrap-pro', 'data-gallery-items', esc_attr( $json_base64 ) );
            $obj->add_render_attribute('gallery-items-wrap-pro', [
               'class' => [
                  'eael-filter-gallery-container',
                  'eael-hg-wrapper',
                  'eael-hg-items',
               ],
               'id' => [ 'eael-hg-items-'.$widget_id ],
            ]);
            ?>
            <div class="eael-hg-gallery-wrap">
               <div <?php $obj->print_render_attribute_string('gallery-items-wrap-pro'); ?>>
                  <?php $this->eael_render_harmonic_gallery( $settings, $gallery_items_pro, $widget_id ); ?>
               </div>
            </div>

            <!-- Gallery overlay content section -->
            <div class="eael-hg-content" id="eael-hg-content-<?php echo esc_attr( $widget_id ); ?>">
               <?php 
               $items_count = min( $gallery_items_to_show, count( $gallery_items_pro ) );
               for ( $i = 0; $i < count( $gallery_items_pro ); $i++ ) {
                  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                  $this->eael_render_harmonic_gallery_content( $gallery_items_pro[$i], $i, $obj, $title_tag, $widget_id );
               }
               ?>

               <button class="eael-hg-back">
                  <svg viewBox="0 0 50 9" width="100%">
                        <path d="M0 4.5l5-3M0 4.5l5 3M50 4.5h-77"></path>
                  </svg>
               </button>

               <?php 
               //Render the navigation section of the gallery
               $this->eael_render_harmonic_gallery_navigation( $gallery_items_pro, $gallery_items_to_show );
               ?>
            </div>
            <?php
      }
   }
}
