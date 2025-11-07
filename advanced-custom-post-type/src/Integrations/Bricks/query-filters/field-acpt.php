<?php

namespace Bricks\Integrations\Query_Filters;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Users;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Field_ACPT
{
    protected $name                 = 'ACPT';
    protected $provider_key         = 'acpt';
    public static $is_active        = false;
    public static $actual_meta_keys = []; // Hold the real meta keys for ACPT fields (improve performance)
    private $acpt_dd_tags           = [];

    public function __construct()
    {
        self::$is_active = true;
        // After provider tags are registered, before query-filters set active_filters_query_vars (query-filters.php)
        // Use bricks/dynamic_data/tags_registered hook (#86c3xg01h; @since 2.0)
        add_action( 'bricks/dynamic_data/tags_registered', [ $this, 'init' ] );

        add_action( 'bricks/query_filters/index_post/before', [ $this, 'maybe_register_dd_provider' ], 10 );
        add_action( 'bricks/query_filters/index_user/before', [ $this, 'maybe_register_dd_provider' ], 10 );

        add_filter( 'bricks/query_filters/index_args', [ $this, 'index_args' ], 10, 3 );

        add_filter( 'bricks/query_filters/index_post/meta_exists', [ $this, 'index_post_meta_exists' ], 10, 4 );
        add_filter( 'bricks/query_filters/index_user/meta_exists', [ $this, 'index_user_meta_exists' ], 10, 4 );

        add_filter( 'bricks/query_filters/custom_field_index_rows', [ $this, 'custom_field_index_rows' ], 10, 5 );

        add_action( 'bricks/filter_element/before_set_data_source_from_custom_field', [ $this, 'modify_custom_field_choices' ] );

        add_filter( 'bricks/query_filters/custom_field_meta_query', [ $this, 'custom_field_meta_query' ], 10, 4 );

        add_filter( 'bricks/query_filters/range_custom_field_meta_query', [ $this, 'range_custom_field_meta_query' ], 10, 4 );

        add_filter( 'bricks/query_filters/datepicker_custom_field_meta_query', [ $this, 'datepicker_custom_field_meta_query' ], 10, 4 );

        add_filter( 'bricks/filter_element/datepicker_date_format', [ $this, 'datepicker_date_format' ], 10, 3 );
    }

    /**
     * Retrieve all registered tags from ACPT provider
     */
    public function init()
    {
        $acpt_provider = \Bricks\Integrations\Dynamic_Data\Providers::get_registered_provider( $this->provider_key );

        if ( $acpt_provider ) {
            $this->acpt_dd_tags = $acpt_provider->get_tags();
        }
    }

    /**
     * Get the name of the provider
     */
    public function get_name() 
    {
        return $this->name;
    }

    /**
     * Check if the provider is active
     */
    public static function is_active() 
    {
        return self::$is_active; //{acpt_movie_a_text}
    }

    /**
     * Manually register the provider if it's not registered (due to is_admin() check in providers.php)
     */
    public function maybe_register_dd_provider( $object_id )
    {
        // Check if provider is registered, it might not be registered due to is_admin() check
        $acpt_provider = \Bricks\Integrations\Dynamic_Data\Providers::get_registered_provider( $this->provider_key );
        if ( is_null( $acpt_provider ) && empty( $this->acpt_dd_tags ) ) {
            $classname = 'Bricks\Integrations\Dynamic_Data\Providers\Provider_' . ucfirst( $this->provider_key );

            if ( ! class_exists( $classname ) ) {
                return;
            }

            // Try manually init the provider
            if ( $classname::load_me() ) {
                $acpt_provider      = new $classname( $this->provider_key );
                $this->acpt_dd_tags = $acpt_provider->get_tags();
            }
        }
    }

    /**
     * Modify the actual meta key for custom fields
     * When user hit on Regenerate Index button
     * Otherwise the post with the actual meta key will not be indexed
     *
     * @return array
     */
    public function index_args( $args, $filter_source, $filter_settings )
    {
        $provider = $filter_settings['fieldProvider'] ?? 'none';

        if ( $provider !== $this->provider_key ) {
            return $args;
        }

        // Modify the actual meta key for custom fields
        if ( $filter_source === 'customField' ) {
            $meta_key = $filter_settings['customFieldKey'] ?? false;
            if ( ! $meta_key ) {
                return $args;
            }

            $actual_meta_key = $this->get_meta_key_by_dd_tag( $meta_key );

            $args['meta_query'] = [
                [
                    'key'     => $actual_meta_key,
                    'compare' => 'EXISTS'
                ],
            ];
        }

        return $args;
    }

    /**
     * Modify the index value based on the field type
     * Generate index rows for a given custom field
     *
     * @param $rows
     * @param $object_id
     * @param $meta_key
     * @param $provider
     * @param $object_type
     *
     * @return array
     */
    public function custom_field_index_rows( $rows, $object_id, $meta_key, $provider, $object_type )
    {
        if ( $provider !== $this->provider_key ) {
            return $rows;
        }

        // $meta_key is a dynamic tag
        $actual_meta_key = $this->get_meta_key_by_dd_tag( $meta_key );

        $field_settings = $this->get_field_settings_from_dd_provider($meta_key);
        $acpt_field = $field_settings['field'];

        // $object_type 'post', 'term', 'user' // @TODO option page???? forse dal $acpt_field['belongsTo']
        $acpt_value = get_acpt_field([
            $object_type.'_id' => $object_id,
            'box_name' => $acpt_field['box_name'],
            'field_name' => $acpt_field['field_name'],
            'with_context' => true,
            'return' => 'raw',
        ]);

        // Return if the field is not found
        if ( empty($acpt_value) ) {
            return $rows;
        }

        $acpt_value = $acpt_value['value'] ?? null;

        // Return if the field is not found
        if ( empty($acpt_value) ) {
            return $rows;
        }

        $field_type              = $acpt_field['type'] ?? MetaFieldModel::TEXT_TYPE;
        $acpt_field['brx_label'] = []; // Hold custom label
        $set_value_id            = false;

        switch ( $field_type ) {

            case MetaFieldModel::PHONE_TYPE:

                $val = $acpt_value['value'];
                $dial = $acpt_value['dial'] ?? null;

                $acpt_value                             = $val;
                $acpt_field['brx_label'][ $acpt_value ] =  "+" .$dial . " " . $val;
                break;

            case MetaFieldModel::SELECT_MULTI_TYPE:
            case MetaFieldModel::SELECT_TYPE:
            case MetaFieldModel::CHECKBOX_TYPE:
            case MetaFieldModel::RADIO_TYPE:
                $value         = empty( $acpt_value ) ? [] : (array) $acpt_value;
                $acpt_value = $value;
                break;

            case MetaFieldModel::TOGGLE_TYPE:
                $acpt_value                             = $acpt_value ? 1 : 0;
                $acpt_field['brx_label'][ $acpt_value ] = $acpt_value ? esc_html__( 'True', 'bricks' ) : esc_html__( 'False', 'bricks' );
                break;

            case MetaFieldModel::POST_TYPE:

                $temp_value = empty( $acpt_value ) ? [] : (array) $acpt_value;

                foreach ($temp_value as $obj){
                    if(isset($obj['type']) and isset($obj['id'])){
                        if($obj['type'] === MetaTypes::CUSTOM_POST_TYPE){
                            $acpt_field['brx_label'][ $obj['id'] ] = get_the_title($obj['id']);
                        }

                        if($obj['type'] === MetaTypes::TAXONOMY){
                            $term = get_term($obj['id']);
                            $acpt_field['brx_label'][ $obj['id'] ] = $term->name;
                        }

                        if($obj['type'] === MetaTypes::USER){
                            $user = get_user($obj['id']);
                            $acpt_field['brx_label'][ $obj['id'] ] = Users::getUserLabel($user);
                        }
                    }
                }

                $set_value_id = true;

                break;

            case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
            case MetaFieldModel::POST_OBJECT_TYPE:

                $temp_value    = empty( $acpt_value ) ? [] : (array) $acpt_value;

                // Retrieve the Term Name as label
                foreach ( $temp_value as $postId ) {
                    $acpt_field['brx_label'][ $postId ] = get_the_title($postId);
                }

                $set_value_id = true;

                break;

            case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
            case MetaFieldModel::TERM_OBJECT_TYPE:

                $temp_value    = empty( $acpt_value ) ? [] : (array) $acpt_value;

                // Retrieve the Term Name as label
                foreach ( $temp_value as $termId ) {
                    $term = get_term($termId);
                    $acpt_field['brx_label'][ $termId ] = $term->name;
                }

                $set_value_id = true;

                break;

            case MetaFieldModel::USER_MULTI_TYPE:
            case MetaFieldModel::USER_TYPE:

                $temp_value    = empty( $acpt_value ) ? [] : (array) $acpt_value;

                // Retrieve the Term Name as label
                foreach ( $temp_value as $userId ) {
                    $user = get_user($userId);
                    $acpt_field['brx_label'][ $userId ] = Users::getUserLabel($user);
                }

                $set_value_id = true;

                break;

            case MetaFieldModel::DATE_TYPE:
            case MetaFieldModel::DATE_TIME_TYPE:
            case MetaFieldModel::TIME_TYPE:

                if ( ! empty( $acpt_value ) ) {

                    if(isset($acpt_value['object']) and isset($acpt_value['value'])){

                        /** @var \DateTime $dateTimeObject */
                        $dateTimeObject = $acpt_value['object'];
                        $value = $acpt_value['value'];

                        switch ($field_type){
                            default:
                            case MetaFieldModel::DATE_TYPE:
                                $format = 'Y-m-d';
                                break;

                            case MetaFieldModel::DATE_TIME_TYPE:
                                $format = 'Y-m-d H:i:s';
                                break;

                            case MetaFieldModel::TIME_TYPE:
                                $format = 'H:i:s';
                                break;
                        }

                        // Use the return format if available
                        if ( ! empty( $return_format ) ) {
                            $format = $return_format;
                        }

                        if ( $dateTimeObject instanceof \DateTime ) {
                            $acpt_value = $value;
                            $acpt_field['brx_label'][ $value ] = $dateTimeObject->format( $format );
                        }
                    }
                }

                break;
        }

        // Retrieve label function
        $get_label = function( $value, $field_settings ) {
            $label = $value;

            if ( ! is_array( $value ) ) {
                // Use label if available
                if ( isset( $field_settings['choices'][ $value ] ) ) {
                    $label = $field_settings['choices'][ $value ];
                }

                // Use custom label if available
                if ( isset( $field_settings['brx_label'] ) && isset( $field_settings['brx_label'][ $value ] ) ) {
                    $label = $field_settings['brx_label'][ $value ];
                }
            }

            return $label;
        };

        $final_values = is_array( $acpt_value ) ? $acpt_value : [ $acpt_value ];

        // Generate rows
        foreach ( $final_values as $value ) {
            $rows[] = [
                'filter_id'            => '',
                'object_id'            => $object_id,
                'object_type'          => $object_type,
                'filter_value'         => $value,
                'filter_value_display' => $get_label( $value, $acpt_field ),
                'filter_value_id'      => $set_value_id ? $value : 0,
                'filter_value_parent'  => 0,
            ];
        }

        return $rows;
    }

    /**
     * Decide whether to index the post based on the meta key
     * Index the post if the meta key exists
     *
     * @param $index
     * @param $post_id
     * @param $meta_key
     * @param $provider
     *
     * @return bool
     */
    public function index_post_meta_exists( $index, $post_id, $meta_key, $provider )
    {
        if ( $provider !== $this->provider_key ) {
            return $index;
        }

        // Get the actual meta key
        $actual_meta_key = $this->get_meta_key_by_dd_tag( $meta_key );

        // Check if the meta key exists
        return metadata_exists( 'post', $post_id, $actual_meta_key );
    }

    /**
     * Decide whether to index the user based on the meta key
     * Index the user if the meta key exists
     *
     * @param $index
     * @param $user_id
     * @param $meta_key
     * @param $provider
     *
     * @return bool
     */
    public function index_user_meta_exists( $index, $user_id, $meta_key, $provider )
    {
        if ( $provider !== $this->provider_key ) {
            return $index;
        }

        // Get the actual meta key
        $actual_meta_key = $this->get_meta_key_by_dd_tag( $meta_key );

        // Check if the meta key exists
        return metadata_exists( 'user', $user_id, $actual_meta_key );
    }

    /**
     * Modify the meta query for custom fields based on the field type
     *
     * @param $meta_query
     * @param $filter
     * @param $provider
     * @param $query_id
     *
     * @return array
     */
    public function custom_field_meta_query( $meta_query, $filter, $provider, $query_id )
    {
        if ( $provider !== $this->provider_key ) {
            return $meta_query;
        }

        $settings         = $filter['settings'];
        $filter_value     = $filter['value'];
        $field_type       = $settings['sourceFieldType'] ?? 'post';
        $custom_field_key = $settings['customFieldKey'] ?? false;
        $instance_name    = $filter['instance_name'];
        $combine_logic    = $settings['filterMultiLogic'] ?? 'OR';

        if ( isset( $settings['fieldCompareOperator'] ) ) {
            $compare_operator = $settings['fieldCompareOperator'];
        } else {
            // Default compare operator for filter-select and filter-radio is =, for others is IN
            $compare_operator = in_array( $instance_name, [ 'filter-select', 'filter-radio' ], true ) ? '=' : 'IN';
        }

        // Retrieve the actual meta key from dynamic tag to be used in the query
        $actual_meta_key = $this->get_meta_key_by_dd_tag( $custom_field_key );

        // Get the field settings
        $field_info      = $this->get_field_settings_from_dd_provider( $custom_field_key );
        $acpt_field      = $field_info['field'] ?? [];
        $acpt_field_type = $acpt_field['type'] ?? MetaFieldModel::TEXT_TYPE;

        // Rebuild meta query
        $meta_query = [];

        // Majority of the field type use multiple key to determine the field is multiple or not
        $is_multiple = $acpt_field['multiple'] ?? false;

        // Certain field types are always multiple
        switch ( $acpt_field_type ) {
            case MetaFieldModel::CHECKBOX_TYPE:
            case MetaFieldModel::SELECT_MULTI_TYPE:
            case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
            case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
            case MetaFieldModel::AUDIO_MULTI_TYPE:
            case MetaFieldModel::ADDRESS_MULTI_TYPE:
            case MetaFieldModel::POST_TYPE:
                $is_multiple = true;
                break;
        }

        if ( ! $is_multiple ) {
            // Single value
            $meta_query = [
                'key'     => $actual_meta_key,
                'value'   => $filter_value,
                'compare' => $compare_operator,
            ];
        }

        else {
            // Multiple values and value in serialized format
            if ( in_array( $instance_name, [ 'filter-select', 'filter-radio' ], true ) ) {
                // Radio or select filter, $filter_value is a string
                $meta_query = [
                    'key'     => $actual_meta_key,
                    'value'   => sprintf( '"%s"', $filter_value ),
                    'compare' => 'LIKE',
                ];

            } else {

                // Checkbox filter
                foreach ( $filter_value as $value ) {
                    $meta_query[] = [
                        'key'     => $actual_meta_key,
                        'value'   => sprintf( '"%s"', $value ),
                        'compare' => 'LIKE',
                    ];
                }

                // Add relation
                $meta_query['relation'] = $combine_logic;
            }
        }

        return $meta_query;
    }

    /**
     * Modify the meta query for filter range element
     *
     * @param $meta_query
     * @param $filter
     * @param $provider
     * @param $query_id
     *
     * @return array
     */
    public function range_custom_field_meta_query( $meta_query, $filter, $provider, $query_id )
    {
        if ( $provider !== $this->provider_key ) {
            return $meta_query;
        }

        $settings         = $filter['settings'];
        $custom_field_key = $settings['customFieldKey'] ?? false;

        // Use the actual meta key
        $actual_meta_key = $this->get_meta_key_by_dd_tag( $custom_field_key );

        // Replace the meta_key with the actual meta key
        $meta_query['key'] = $actual_meta_key;

        return $meta_query;
    }

    /**
     * Modify the meta query for Filter - datepicker element
     *
     * @param $meta_query
     * @param $filter
     * @param $provider
     * @param $query_id
     *
     * @return array
     */
    public function datepicker_custom_field_meta_query( $meta_query, $filter, $provider, $query_id )
    {
        if ( $provider !== $this->provider_key ) {
            return $meta_query;
        }

        $settings         = $filter['settings'];
        $custom_field_key = $settings['customFieldKey'] ?? false;
        $mode             = isset( $settings['isDateRange'] ) ? 'range' : 'single';

        // Use the actual meta key
        $actual_meta_key = $this->get_meta_key_by_dd_tag( $custom_field_key );

        // Replace the meta_key with the actual meta key
        if ( $mode === 'single' ) {
            $meta_query['key'] = $actual_meta_key;
        } else {
            foreach ( $meta_query as $key => $query ) {
                $meta_query[ $key ]['key'] = $actual_meta_key;
            }
        }

        return $meta_query;
    }

    /**
     * Modify the custom field choices following the ACPT field choices
     *
     * Direct update element->choices_source
     *
     * @param $element
     */
    public function modify_custom_field_choices( $element )
    {
        $settings         = $element->settings;
        $custom_field_key = $settings['customFieldKey'] ?? false;
        $provider         = $settings['fieldProvider'] ?? 'none';

        if ( ! $custom_field_key || $provider !== $this->provider_key ) {
            return;
        }

        $field_info   = $this->get_field_settings_from_dd_provider( $custom_field_key );
        $acpt_field   = $field_info['field'] ?? [];
        $acpt_choices = $acpt_field['choices'] ?? [];
        $field_type   = $acpt_field['type'] ?? MetaFieldModel::TEXT_TYPE;

        // Taxonomy field can have choices from the terms, build the choices from the terms
//        if ( $field_type === 'taxonomy' ) {
//            $taxonomy = $acf_field['taxonomy'] ?? false;
//            if ( ! $taxonomy ) {
//                return;
//            }
//
//            $terms = get_terms(
//                [
//                    'taxonomy'   => $taxonomy,
//                    'hide_empty' => false,
//                ]
//            );
//
//            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
//                $acpt_choices = [];
//                foreach ( $terms as $term ) {
//                    $acpt_choices[ $term->term_id ] = $term->name;
//                }
//            }
//        }

        // Return if no choices
        if ( empty( $acpt_choices ) ) {
            return;
        }

        // Modify the choices source
        $temp_choices = [];
        $ori_choices  = $element->choices_source;

        foreach ( $acpt_choices as $acpt_value => $acpt_label ) {
            $matched_choice = array_filter(
                $ori_choices,
                function( $choice ) use ( $acpt_value ) {
                    return isset( $choice['filter_value'] ) && \Bricks\Filter_Element::is_option_value_matched( $choice['filter_value'], $acpt_value );
                }
            );

            $matched_choice = array_values( $matched_choice );

            $temp_choices[] = [
                'filter_value'         => $acpt_value,
                'filter_value_display' => $acpt_label,
                'filter_value_id'      => 0,
                'filter_value_parent'  => 0,
                'count'                => ! empty( $matched_choice ) ? $matched_choice[0]['count'] : 0,
            ];
        }

        // Overwrite the choices source
        $element->choices_source = $temp_choices;
    }

    /**
     * Auto detect the date format for Filter - Datepicker following ACPT datepicker field return format
     *
     * @param $date_format
     * @param $provider
     * @param $element
     *
     * @return bool|mixed|string
     */
    public function datepicker_date_format( $date_format, $provider, $element )
    {
        if ( $provider !== $this->provider_key ) {
            return $date_format;
        }
        $settings         = $element->settings;
        $custom_field_key = $settings['customFieldKey'] ?? false;
        $enable_time      = isset( $settings['enableTime'] );

        // Use the actual meta key
        $field_info    = $this->get_field_settings_from_dd_provider( $custom_field_key );
        $acf_field     = $field_info['field'] ?? [];
        $return_format = $acf_field['return_format'] ?? false;

        // Use the return format if available
        if ( $return_format ) {
            $date_format = $return_format;
        } else {
            // Use the default date format saved in the database
            $date_format = $enable_time ? 'Y-m-d H:i:s' : 'Y-m-d';
        }

        return $date_format;
    }

    /**
     * Get the meta key saved in the database by the ACPT key
     * Convert field_123456789 to actual meta key considering parent fields
     *
     * @param $acpt_key
     *
     * @return mixed|string
     */
    private function get_meta_key_by_acpt_key( $acpt_key )
    {
        // Check if the meta key is already saved in the static variable
        if ( isset( self::$actual_meta_keys[ $acpt_key ] ) ) {
            return self::$actual_meta_keys[ $acpt_key ];
        }

        // @TODO nested values

//        $field = acf_maybe_get_field( $acpt_key );
//
//        if ( empty( $field ) || ! is_array( $field ) || ! isset( $field['name'] ) || ! isset( $field['parent'] ) ) {
//            // Save the meta key in the static variable
//            self::$actual_meta_keys[ $acpt_key ] = $acpt_key;
//            return $acpt_key;
//        }
//
//        $parents = [];
//
//        // Get the final key
//        while ( ! empty( $field['parent'] ) && ! in_array( $field['name'], $parents ) ) {
//            $parents[] = $field['name'];
//            $field     = acf_get_field( $field['parent'] );
//        }
//
//        $final_key = implode( '_', array_reverse( $parents ) );
//
//        // Save the meta key in the static variable
//        self::$actual_meta_keys[ $acpt_key ] = $final_key;

        return $acpt_key;
    }

    /**
     * Get the field settings from the dynamic data provider
     *
     * @param string $tag The dynamic data tag
     * @param string $key The key to retrieve from the field settings (optional)
     *
     * @return bool|mixed
     */
    public function get_field_settings_from_dd_provider( $tag, $key = '' )
    {
        if ( empty( $this->acpt_dd_tags ) ) {
            return false;
        }

        $dd_key = str_replace( [ '{','}' ], '', $tag );

        $dd_info = $this->acpt_dd_tags[ $dd_key ] ?? false;

        if ( ! $dd_info ) {
            return false;
        }

        // Return all settings or specific key
        if ( empty( $key ) ) {
            return $dd_info;
        }

        return $dd_info[ $key ] ?? false;
    }

    /**
     * Get the actual meta key from the dynamic data tag
     *
     * @param string $tag The dynamic data tag
     *
     * @return mixed|string
     */
    public function get_meta_key_by_dd_tag( $tag )
    {
        if ( empty( $this->acpt_dd_tags ) ) {
            return $tag;
        }

        $field_info = $this->get_field_settings_from_dd_provider( $tag );

        if ( ! $field_info || ! isset( $field_info['field']['key'] ) ) {
            return $tag;
        }

        return $this->get_meta_key_by_acpt_key( $field_info['field']['key'] );
    }
}
