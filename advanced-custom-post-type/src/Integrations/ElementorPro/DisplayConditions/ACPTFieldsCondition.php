<?php

namespace ACPT\Integrations\ElementorPro\DisplayConditions;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Integrations\ElementorPro\Constants\TagsConstants;
use ACPT\Integrations\ElementorPro\DynamicDataProvider;
use ACPT\Utils\Wordpress\WPAttachment;
use Elementor\Controls_Manager;
use ElementorPro\Modules\DisplayConditions\Classes\Comparator_Provider;
use ElementorPro\Modules\DisplayConditions\Classes\Comparators_Checker;
use ElementorPro\Modules\DisplayConditions\Conditions\Base\Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class ACPTFieldsCondition extends Condition_Base {

    /**
     * @var DynamicDataProvider
     */
    private $dynamic_tags_data_provider;

    /**
     * ACPTFieldsCondition constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->dynamic_tags_data_provider = DynamicDataProvider::getInstance();
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return 'acpt_fields';
    }

    /**
     * @return string
     */
    public function get_label()
    {
        return esc_html__( 'ACPT fields', 'elementor-pro' );
    }

    /**
     * @return string
     */
    public function get_group()
    {
        return 'other';
    }

    /**
     * @param $args
     *
     * @return bool
     */
    public function check( $args ) : bool
    {
        $value = $this->get_condition_value( $args );

        if ( false === $value ) {
            return false;
        }

        return Comparators_Checker::check_string_contains_and_empty( $args['comparator'], $args['acpt_field_value'], $value );
    }

    public function get_options()
    {
        $this->add_control(
            'acpt_field',
            [
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_control_options(),
                'default' => null,
                'disabled_options' => ! current_user_can( 'manage_options' ) ? [ 'author_info_email' ] : [],
                'disabled_type' => 'hidden',
            ]
        );

        $this->add_control(
            'comparator',
            [
                'type' => Controls_Manager::SELECT,
                'options' => Comparator_Provider::get_comparators(
                    [
                        Comparator_Provider::COMPARATOR_IS,
                        Comparator_Provider::COMPARATOR_IS_NOT,
                        Comparator_Provider::COMPARATOR_CONTAINS,
                        Comparator_Provider::COMPARATOR_NOT_CONTAIN,
                        Comparator_Provider::COMPARATOR_IS_EMPTY,
                        Comparator_Provider::COMPARATOR_IS_NOT_EMPTY,
                    ]
                ),
                'default' => Comparator_Provider::COMPARATOR_IS,
            ]
        );

        $this->add_control(
            'acpt_field_value',
            [
                'placeholder' => esc_html__( 'Type a value', 'elementor-pro' ),
                'required' => true,
            ]
        );
    }

    /**
     * Conditionally retrieve the value of a dynamic tag or custom field.
     *
     * @param array $args
     *
     * @return string | bool
     */
    private function get_condition_value( array $args )
    {
        $field = explode(TagsConstants::KEY_SEPARATOR, $args['acpt_field']);

        if(empty($field)){
            return false;
        }

        if(!is_array($field)){
            return false;
        }

        if(count($field) !== 5){
            return false;
        }

        $belongsTo = $field[0];
        $find = $field[1];
        $boxName = $field[2];
        $fieldName = $field[3];
        $fieldType = $field[4];

        switch ($belongsTo){

            default:
            case MetaTypes::CUSTOM_POST_TYPE:
                global $post;

                $value = get_acpt_field([
                    'box_name' => $boxName,
                    'field_name' => $fieldName,
                    'post_id' => (int)$post->ID
                ]);
                break;

            case MetaTypes::OPTION_PAGE:
                $value = get_acpt_field([
                    'box_name' => $boxName,
                    'field_name' => $fieldName,
                    'option_page' => $find,
                ]);
                break;
        }

        // @TODO taxonomy meta fields ?????
        // @TODO user meta fields ?????

        if($value === null){
            return false;
        }

        switch ($fieldType){

            case MetaFieldModel::ADDRESS_TYPE:
                $value = $value['address'];
                break;

            case MetaFieldModel::ADDRESS_MULTI_TYPE:
                if(is_array($value)){
                    $val = [];

                    foreach ($value as $item){
                        if(isset($item['address'])){
                            $val[] = $item['address'];
                        }
                    }

                    $value = $val;
                }

                break;

            case MetaFieldModel::FILE_TYPE:

                if($value['file'] instanceof WPAttachment){
                    $value = $value['file']->getId();
                }

                break;

            case MetaFieldModel::URL_TYPE:
                $value = $value['url'];
                break;

            case MetaFieldModel::QR_CODE_TYPE:
                $value = is_string($value) ? $value : $value['url'] ?? '';
                break;

            case MetaFieldModel::BARCODE_TYPE:
                $value = is_string($value) ? $value : $value['text'] ?? '';
                break;

            case MetaFieldModel::DATE_TYPE:
            case MetaFieldModel::DATE_TIME_TYPE:
            case MetaFieldModel::DATE_RANGE_TYPE:
            case MetaFieldModel::TIME_TYPE:
            case MetaFieldModel::PHONE_TYPE:
                $value = $value['value'];
                break;

            case MetaFieldModel::LENGTH_TYPE:
                $value = $value['length'];
                break;

            case MetaFieldModel::WEIGHT_TYPE:
                $value = $value['weight'];
                break;

            case MetaFieldModel::CURRENCY_TYPE:
                $value = $value['currency'];
                break;

            case MetaFieldModel::COUNTRY_TYPE:
                $value = $value['country'];
                break;

            case MetaFieldModel::AUDIO_MULTI_TYPE:
            case MetaFieldModel::GALLERY_TYPE:

                if(is_array($value)){
                    $val = [];

                    /** @var WPAttachment $attachment */
                    foreach ($value as $attachment){
                        $val[] = $attachment->getId();
                    }

                    $value = $val;
                }

                break;
        }

        if($value instanceof \WP_Post){
            $value = $value->ID;
        } elseif($value instanceof \WP_Term){
            $value = $value->term_id;
        } elseif($value instanceof \WP_User){
            $value = $value->ID;
        } elseif($value instanceof WPAttachment){
            $value = $value->getId();
        } elseif(!is_string($value)){
            $value = Strings::convertAnyValueToString($value);
        }

        return (string)$value;
    }

    /**
     * @return array
     */
    private function get_control_options()
    {
        $options = [];

        foreach ($this->dynamic_tags_data_provider->getFields() as $tag => $fields){

            /** @var MetaFieldModel $field */
            foreach ($fields as $field){
                $key = TagsConstants::getKey($field);
                $options[$key] = '['.$field->getFindLabel().'] ' . $field->getUiName();
            }
        }

        return $options;
    }
}
