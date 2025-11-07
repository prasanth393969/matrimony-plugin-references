<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor button control.
 *
 * A base control for creating a button control. Displays a button that can
 * trigger an event.
 *
 * @since 1.9.0
 */
class FacetWP_Button_Widget extends \Elementor\Widget_Button {
    
    /**
    * Get widget name.
    *
    * Retrieve widget name.
    *
    * @since 1.0.0
    * @access public
    * @return string Widget name.
    */
       public function get_name() {
        return 'facetwp-button';
       }

    /**
    * Get widget title.
    *
    * Retrieve widget title.
    *
    * @since 1.0.0
    * @access public
    * @return string Widget title.
    */
    public function get_title() {
        return esc_html__( 'FacetWP Button', 'fwp-front' );
    }

    /**
    * Get widget icon.
    *
    * Retrieve oEmbed widget icon.
    *
    * @since 1.0.0
    * @access public
    * @return string Widget icon.
    */
    public function get_icon() {
        return 'facetwp-icon';
    }

    /**
     * Get custom help URL.
     *
     * Retrieve a URL where the user can get more information about the widget.
     *
     * @since 1.0.0
     * @access public
     * @return string Widget help URL.
     */
    public function get_custom_help_url() {
        return 'https://facetwp.com/help-center/using-facetwp-with/elementor/#facetwp-elementor-widgets';
    }

    /**
    * Get widget categories.
    *
    * Retrieve the list of categories the widget belongs to.
    *
    * @since 1.0.0
    * @access public
    * @return array Widget categories.
    */
    public function get_categories() {
        return [ 'facetwp' ];
    }

    /**
    * Get widget keywords.
    *
    * Retrieve the list of keywords the widget belongs to.
    *
    * @since 1.0.0
    * @access public
    * @return array Widget keywords.
    */
    public function get_keywords() {
        return [ 'facetwp', 'button', 'submit', 'mobile' ];
    }
   
    /**
     * Register widget controls.
     *
     * Add input fields to allow the user to customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {

        $this->start_controls_section(
            'section_button',
            [
                'label' => esc_html__( 'FacetWP Button', 'elementor' ),
            ]
        );

        $options = [];
        $desc = '';
        $options['apply'] = 'Apply button';
        $desc = 'An <a href="https://facetwp.com/how-to-disable-facet-auto-refresh-and-add-a-submit-button/" target="_blank">Apply button</a> disables auto-refresh and applies facets on button click.';
        if ( class_exists( 'FacetWP_Flyout_Addon') ) {
            $options['flyout'] = 'Flyout button';
            $desc .= '<br />A <a href="https://facetwp.com/help-center/add-on-features-and-extras/mobile-flyout/" target="_blank">Flyout button</a> opens the Mobile Flyout panel.';
        }
        if ( class_exists( 'FacetWP_Submit_Addon' ) ) {
            $options['submit'] = 'Submit button';
            $desc .= '<br />A <a href="https://facetwp.com/help-center/add-on-features-and-extras/submit-button/" target="_blank">Submit button</a> redirects to a separate results page.';
        }

        $this->add_control(
            'facetwp_button_type',
            [
                'label' => esc_html__( 'Choose Type', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $options,
                'selectors' => [
                    '{{WRAPPER}}' => 'cursor: pointer',
                ],
                'description' => $desc,
            ]
        );

        $this->add_control(
            'facetwp_link',
            [
                'label' => esc_html__( 'Listing Page', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'ai' => [
                    'active' => false,
                ],
                'placeholder' => esc_html__( '/my-result-page/', 'facetwp-elementor' ),
                'default' => '',
                'condition' => [
                    'facetwp_button_type' => 'submit',
                ],
                'description' => esc_html__( 'URL to redirect to on submit of facet selections.', 'facetwp-elementor' ),
            ]
        );

        $this->register_button_content_controls();

        $this->remove_control( 'button_type' );

        $this->remove_control( 'link' );

        $this->add_control(
            'link',
            [
                'label' => esc_html__( 'Link', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => [
                    'url' => '',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__( 'Button', 'elementor' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->register_button_style_controls();

        $this->end_controls_section();

    }

    /**
    * Render button widget output on the frontend.
    *
    * Written in PHP and used to generate the final HTML.
    *
    * @since 1.0.0
    * @access protected
    */
    protected function render() {
        $this->render_button();
    }
}