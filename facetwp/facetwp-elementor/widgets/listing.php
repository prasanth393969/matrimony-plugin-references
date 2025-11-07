<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FacetWP Template Widget.
 *
 * Adds a facet template to a page
 *
 * @since 1.0.0
 */
class FacetWP_Listing_Widget extends \Elementor\Widget_Base {

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
        return 'facetwp-listing';
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
        return esc_html__( 'FacetWP Listing', 'fwp-front' );
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
        return [ 'facetwp', 'template', 'facet', 'listing' ];
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
            'content_section',
            [
                'label' => esc_html__( 'Content', 'facetwp-elementor' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $templates = FWP()->helper->get_templates();

        $options = [];

        foreach ( $templates AS $template ) {
            $options[$template['name']] = $template['label'] . ' (' . $template['name'] . ')';
        }

        ksort( $options );

        $this->add_control(
            'template_name',
            [
                'label' => esc_html__( 'Listing', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $options
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => esc_html__( 'Static', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'On', 'facetwp-elementor' ),
                'label_off' => esc_html__( 'Off', 'facetwp-elementor' ),
                'return_value' => 'on',
                'default' => 'off',
                'description' => esc_html__( 'Enable to create a static listing template that does not react to facets.', 'facetwp-elementor' ),
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Render widget output
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();
        $template = $settings['template_name'];
        $additional_args = [];

        if ( 'on' === $settings['show_title'] ) {
            $additional_args['static'] = true;
        }

        echo facetwp_display( 'template', $template, $additional_args );
 
    }

}
