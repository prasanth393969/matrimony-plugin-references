<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FacetWP Filter Widget.
 *
 * Adds a facet filter to a page
 *
 * @since 1.0.0
 */
class FacetWP_Facet_Widget extends \Elementor\Widget_Base {

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
        return 'facetwp-facet';
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
        return esc_html__( 'FacetWP Facet', 'fwp-front' );
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
     * add admin styles for editor
     */
    public function get_style_depends() {

        return [ 'facetwp-admin' ];
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
        return [ 'facetwp', 'filter', 'facet' ];
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

        $this->add_control(
            'filter_heading',
            [
                'label' => esc_html__( 'Header', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Add facet heading', 'facetwp-elementor' ),
                'ai' => [
                    'active' => false,
                ],
            ]
        );
        
        $possible_tags = [
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'p',
            'span',
        ];

        $tags = array_combine( $possible_tags, $possible_tags );

        $this->add_control(
            'filter_html_tag',
            [
                'label' => esc_html__( 'Header Tag', 'elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $tags,
                'default' => 'h3',
                'condition' => [
                    'filter_heading!' => '',
                ],
                
            ]
        );

        $facets = FWP()->helper->get_facets();

        $options = [];

        foreach ( $facets AS $facet ) {
            $options[$facet['name']] = $facet['label'] . ' (' . $facet['name'] . ')';
        }

        ksort( $options );

        $options['selections'] = 'User Selections';

        $this->add_control(
            'filter_name',
            [
                'label' => esc_html__( 'Facet', 'facetwp-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $options,
                'description' => esc_html__( 'Facets do not display in the editor.  A placeholder is shown.', 'facetwp-elementor' ),
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
        $facet_name = $settings['filter_name'];
        $heading = $settings['filter_heading'];
        $facet = FWP()->helper->get_facet_by_name( $facet_name );
        $label = ( 'selections' == $facet_name ) ? 'User Selections' : ( ( empty( $facet ) ) ? 'Select a facet' : $facet['label'] );

        
        ?>

        <div class="facet-wrap">
            <?php if ( '' !== $heading ) : ?>
                <<?php \Elementor\Utils::print_validated_html_tag( $settings['filter_html_tag'] ); ?>>
                    <?php echo $heading; ?>
                </<?php \Elementor\Utils::print_validated_html_tag( $settings['filter_html_tag'] ); ?>>
            <?php endif; ?>
            <?php         
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {				
                echo '<div class"facetwp-placeholder"><i class="facetwp-logo" title="FacetWP">&nbsp;</i> ' . $label . '</div>';
            }
            if ( 'selections' == $facet_name ) {
                echo facetwp_display( 'selections' );
            } else {
                echo facetwp_display( 'facet', $facet_name );
            }        
            ?>
        </div>

        <?php
    }

}
