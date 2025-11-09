<?php
namespace Essential_Addons_Elementor\Pro\Elements;

use \Elementor\Controls_Manager;
use \Elementor\Widget_Base;
use Essential_Addons_Elementor\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // If this file is called directly, abort.
}

class Figma_To_Elementor extends Widget_Base
{

    public function get_name()
    {
        return 'eael-figma-to-elementor';
    }
    private function beta_svg_icon(){
        return ' <svg class="eael-beta-icon" xmlns="http://www.w3.org/2000/svg" width="41" height="16" viewBox="0 0 41 20" fill="none" style="vertical-align: middle;margin-top: 3px;">
            <rect width="40.2853" height="19.3213" rx="4.64263" fill="#DDDCDC"/>
            <path d="M5.76119 15.1606V4.97883H9.48988C10.2124 4.97883 10.8107 5.09814 11.2846 5.33678C11.7586 5.5721 12.1132 5.89194 12.3485 6.2963C12.5839 6.69734 12.7015 7.14975 12.7015 7.65354C12.7015 8.07778 12.6236 8.43574 12.4679 8.72741C12.3121 9.01576 12.1033 9.24777 11.8414 9.42343C11.5829 9.59578 11.2979 9.72172 10.9863 9.80127V9.9007C11.3244 9.91727 11.6542 10.0266 11.9757 10.2288C12.3005 10.4277 12.569 10.7111 12.7811 11.079C12.9932 11.4469 13.0993 11.8943 13.0993 12.4213C13.0993 12.9417 12.9766 13.409 12.7314 13.8233C12.4894 14.2343 12.1149 14.5607 11.6078 14.8027C11.1007 15.0413 10.4527 15.1606 9.66389 15.1606H5.76119ZM7.29741 13.8432H9.51474C10.2505 13.8432 10.7775 13.7007 11.0957 13.4156C11.4139 13.1306 11.573 12.7743 11.573 12.3467C11.573 12.0252 11.4918 11.7302 11.3294 11.4618C11.167 11.1933 10.935 10.9795 10.6333 10.8204C10.335 10.6614 9.98041 10.5818 9.56942 10.5818H7.29741V13.8432ZM7.29741 9.38366H9.35565C9.70034 9.38366 10.0102 9.31737 10.2853 9.18479C10.5637 9.05222 10.7842 8.86661 10.9466 8.62797C11.1123 8.38602 11.1951 8.10099 11.1951 7.77286C11.1951 7.35193 11.0476 6.99895 10.7527 6.71391C10.4577 6.42887 10.0053 6.28635 9.39542 6.28635H7.29741V9.38366ZM18.0932 15.3148C17.3408 15.3148 16.6929 15.154 16.1493 14.8325C15.6091 14.5077 15.1915 14.052 14.8965 13.4653C14.6048 12.8754 14.459 12.1843 14.459 11.3922C14.459 10.61 14.6048 9.92059 14.8965 9.324C15.1915 8.72741 15.6025 8.26173 16.1294 7.92698C16.6597 7.59223 17.2795 7.42485 17.9888 7.42485C18.4197 7.42485 18.8373 7.49611 19.2417 7.63863C19.646 7.78115 20.0089 8.00487 20.3304 8.30979C20.6519 8.61472 20.9055 9.01079 21.0911 9.498C21.2767 9.9819 21.3695 10.5702 21.3695 11.2629V11.7899H15.2992V10.6763H19.9128C19.9128 10.2852 19.8333 9.93882 19.6742 9.63721C19.5151 9.33228 19.2914 9.09199 19.003 8.91633C18.718 8.74066 18.3832 8.65283 17.9988 8.65283C17.5811 8.65283 17.2166 8.75558 16.905 8.96107C16.5968 9.16325 16.3581 9.4284 16.1891 9.75653C16.0234 10.0813 15.9405 10.4343 15.9405 10.8155V11.6855C15.9405 12.1959 16.03 12.6301 16.209 12.9881C16.3913 13.346 16.6448 13.6195 16.9696 13.8084C17.2944 13.994 17.6739 14.0868 18.1081 14.0868C18.3899 14.0868 18.6467 14.047 18.8787 13.9675C19.1107 13.8846 19.3113 13.762 19.4803 13.5996C19.6493 13.4372 19.7786 13.2366 19.8681 12.998L21.275 13.2516C21.1623 13.6659 20.9602 14.0288 20.6685 14.3403C20.3801 14.6486 20.0172 14.8889 19.5797 15.0612C19.1455 15.2302 18.65 15.3148 18.0932 15.3148ZM26.5835 7.52428V8.71746H22.4123V7.52428H26.5835ZM23.5309 5.69474H25.0174V12.9185C25.0174 13.2068 25.0605 13.4239 25.1467 13.5697C25.2328 13.7123 25.3439 13.81 25.4798 13.8631C25.619 13.9128 25.7698 13.9376 25.9322 13.9376C26.0515 13.9376 26.1559 13.9293 26.2454 13.9128C26.3349 13.8962 26.4045 13.8829 26.4542 13.873L26.7227 15.101C26.6365 15.1341 26.5138 15.1673 26.3548 15.2004C26.1957 15.2369 25.9968 15.2568 25.7582 15.2601C25.3671 15.2667 25.0025 15.1971 24.6644 15.0513C24.3263 14.9054 24.0529 14.6801 23.8441 14.3751C23.6353 14.0702 23.5309 13.6874 23.5309 13.2267V5.69474ZM30.4501 15.3297C29.9662 15.3297 29.5287 15.2402 29.1376 15.0612C28.7465 14.8789 28.4366 14.6154 28.2079 14.2707C27.9825 13.926 27.8699 13.5034 27.8699 13.003C27.8699 12.5721 27.9527 12.2175 28.1184 11.9391C28.2842 11.6606 28.5079 11.4402 28.7896 11.2778C29.0713 11.1154 29.3862 10.9928 29.7342 10.9099C30.0822 10.8271 30.4368 10.7641 30.7981 10.721C31.2555 10.668 31.6267 10.6249 31.9118 10.5918C32.1968 10.5553 32.4039 10.4973 32.5332 10.4177C32.6625 10.3382 32.7271 10.2089 32.7271 10.03V9.99516C32.7271 9.56098 32.6045 9.22456 32.3592 8.98593C32.1172 8.74729 31.756 8.62797 31.2754 8.62797C30.7749 8.62797 30.3805 8.73901 30.0922 8.96107C29.8071 9.17982 29.6099 9.42343 29.5005 9.69189L28.1035 9.37371C28.2692 8.9097 28.5112 8.53517 28.8294 8.25013C29.1509 7.96178 29.5204 7.75297 29.938 7.62371C30.3556 7.49114 30.7948 7.42485 31.2555 7.42485C31.5604 7.42485 31.8836 7.46131 32.225 7.53422C32.5697 7.60383 32.8912 7.73309 33.1895 7.92201C33.4911 8.11093 33.738 8.38105 33.9302 8.73238C34.1225 9.08039 34.2186 9.5328 34.2186 10.0896V15.1606H32.7669V14.1166H32.7072C32.6111 14.3088 32.4669 14.4978 32.2747 14.6834C32.0824 14.869 31.8355 15.0231 31.5339 15.1457C31.2323 15.2684 30.871 15.3297 30.4501 15.3297ZM30.7733 14.1365C31.1842 14.1365 31.5356 14.0553 31.8272 13.8929C32.1222 13.7305 32.3459 13.5184 32.4984 13.2565C32.6542 12.9914 32.7321 12.708 32.7321 12.4064V11.422C32.679 11.475 32.5763 11.5248 32.4238 11.5712C32.2747 11.6142 32.104 11.6524 31.9118 11.6855C31.7195 11.7153 31.5323 11.7435 31.35 11.77C31.1677 11.7932 31.0152 11.8131 30.8926 11.8297C30.6042 11.8661 30.3407 11.9275 30.1021 12.0136C29.8668 12.0998 29.6779 12.2241 29.5353 12.3865C29.3961 12.5456 29.3265 12.7577 29.3265 13.0229C29.3265 13.3908 29.4624 13.6692 29.7342 13.8581C30.006 14.0437 30.3523 14.1365 30.7733 14.1365Z" fill="black"/>
        </svg>';
    }

    public function get_title()
    {
        return __( 'Figma to Elementor Converter', 'essential-addons-elementor' ) . $this->beta_svg_icon();
    }

    public function get_categories()
    {
        return [ 'essential-addons-elementor' ];
    }

    public function get_keywords()
    {
        return [
            'ea figma to elementor',
            'figma to elementor',
            'figma to wp',
            'ea',
            'essential addons'
         ];
    }

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    public function has_widget_inner_wrapper(): bool
    {
        return ! Helper::eael_e_optimized_markup();
    }

    public function get_custom_help_url()
    {
        return 'https://essential-addons.com/docs/ea-figma-to-elementor-converter/';
    }

    public function get_icon()
    {
        return 'eaicon-figma-to-elementor';
    }

    protected function register_controls()
    {

        /**
         *    CONTENT TAB
         */

        $this->start_controls_section(
            'section_figma_to_elementor',
            [
                'label' => __( 'Content', 'essential-addons-elementor' )
             ]
        );

        $this->add_control(
            'eael_figma_to_elementor_import_from',
            [
                'label'   => __( 'Import From', 'essential-addons-elementor' ),
                'type'    => Controls_Manager::CHOOSE,
                'toggle'  => false,
                'options' => [
                    'json' => [
                        'title' => __( 'JSON', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-code'
                     ],
                    'file' => [
                        'title' => __( 'File', 'essential-addons-elementor' ),
                        'icon'  => 'eicon-upload'
                     ]
                 ],
                'default' => 'json'
             ]
        );

        $this->add_control(
            'eael_figma_to_elementor_json',
            [
                'type'      => Controls_Manager::RAW_HTML,
                'raw'       => '<textarea class="eael_figma_to_elementor_json" rows="15" placeholder = "' . __( 'Paste your Figma JSON here', 'essential-addons-elementor' ) . '"></textarea>',
                'condition' => [
                    'eael_figma_to_elementor_import_from' => 'json'
                 ]
             ]
        );

        $this->add_control(
            'eael_figma_to_elementor_import_button',
            [
                'label'     => '',
                'type'      => Controls_Manager::BUTTON,
                'text'      => __( 'Import', 'essential-addons-elementor' ),
                'event'     => 'eael:figmajson:import',
                'condition' => [
                    'eael_figma_to_elementor_import_from' => 'json'
                 ]
             ]
        );

        $this->add_control(
            'eael_figma_to_elementor_file',
            [
                'label'      => __( 'Upload JSON File', 'essential-addons-elementor' ),
                'type'       => Controls_Manager::MEDIA,
                'media_type' => 'application/json',
                'dynamic'    => [
                    'active' => true
                 ],
                'condition'  => [
                    'eael_figma_to_elementor_import_from' => 'file'
                 ]
             ]
        );

        $this->add_control(
            'eael_figma_to_elementor_file_import_button',
            [
                'label'     => '',
                'type'      => Controls_Manager::BUTTON,
                'text'      => __( 'Import', 'essential-addons-elementor' ),
                'event'     => 'eael:figmajsonfile:import',
                'condition' => [
                    'eael_figma_to_elementor_import_from' => 'file'
                 ]
             ]
        );

        $this->end_controls_section();
    }

    /**
     * Render counter widget output on the frontend.
     */
    protected function render()
    {}
}
