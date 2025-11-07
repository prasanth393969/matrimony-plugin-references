<?php

namespace Bricks\Integrations\Dynamic_Data\Providers;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Currencies;
use ACPT\Core\Helper\Lengths;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Weights;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Includes\ACPT_Plugin;
use ACPT\Utils\PHP\Barcode;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\PHP\Email;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\Users;
use ACPT\Utils\Wordpress\WPAttachment;
use ACPT\Utils\Wordpress\WPUtils;
use Bricks\Query;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Provider_ACPT extends Base
{
    const IS_NESTED_IN_A_REPEATER = "is_nested_in_a_repeater";

    const RELATIONAL_FIELDS = [
            MetaFieldModel::POST_OBJECT_TYPE,
            MetaFieldModel::POST_OBJECT_MULTI_TYPE,
            MetaFieldModel::POST_TYPE,
            MetaFieldModel::TERM_OBJECT_MULTI_TYPE,
            MetaFieldModel::TERM_OBJECT_TYPE,
            MetaFieldModel::USER_MULTI_TYPE,
            MetaFieldModel::USER_TYPE,
    ];

    /**
     * @var array
     */
    private $fields;

    /**
     * Provider_ACPT constructor.
     *
     * @param $name
     */
    public function __construct( $name ) {
        try {
            parent::__construct( $name );
            $this->fields = self::get_fields();
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * @return bool
     */
    public static function load_me()
    {
        return class_exists( ACPT_Plugin::class );
    }

    /**
     * Register tags
     */
    public function register_tags()
    {
        foreach ( $this->fields as $field ) {
            if(!empty($field)){
                $this->register_tag( $field );
            }
        }
    }

    /**
     * Register a tag from a field
     * Every tag MUST be unique
     *
     * @param array $field
     * @param array $parent_field
     */
    private function register_tag($field, $parent_field = [])
    {
        $type = $field['type'];
        $contexts = self::get_fields_by_context();

        if (!isset( $contexts[ $type ])) {
            return;
        }

        $contextsByType = $contexts[ $type ];
        $belongsTo = $field['belongsTo'];

        switch ($belongsTo){
            case MetaTypes::TAXONOMY:
                $prefixName = 'acpt_tax_';
                break;

            case MetaTypes::OPTION_PAGE:
                $prefixName = 'acpt_option_';
                break;

            default:
            case MetaTypes::CUSTOM_POST_TYPE:
                $prefixName = 'acpt_';
                break;
        }

        $name = $prefixName . $field['slug'];
        $label = $field['name'];

        $tag = [
                'name'     => '{' . $name . '}',
                'label'    => $label,
                'group'    => $field['group_name'],
                'field'    => $field,
                'provider' => $this->name,
                'contexts' => $contextsByType,
        ];

        if (!empty( $parent_field )) {

            // Add the parent field attributes to the child tag so we could retrieve the value of group sub-fields
            $tag['parent'] = [
                    'slug'        => $parent_field['slug'],
                    'name'        => $parent_field['name'],
                    'type'        => $parent_field['type'],
                    'box_name'    => $parent_field['box_name'],
                    'field_name'  => ((isset($parent_field['parent_field_name'])) ? $parent_field['parent_field_name'] : $parent_field['field_name'] ),
                    'block_name'  => ((isset($parent_field['block_name'])) ? $parent_field['block_name'] : null),
            ];
        }

        // Loop fields
        if(in_array(self::CONTEXT_LOOP, $contextsByType)){

            // List/Repeater fields
            if (in_array($type, [ MetaFieldModel::REPEATER_TYPE, MetaFieldModel::LIST_TYPE ])){
                $this->loop_tags[ $name ] = $tag;

                // Check for sub-fields (including group field sub-fields)
                if (!empty( $field['children'])) {
                    foreach ( $field['children'] as $sub_field ) {
                        $this->register_tag( $sub_field, $field ); // Recursive
                    }
                }
            }

            // Flexible field blocks
            elseif($type === MetaFieldModel::FLEXIBLE_CONTENT_TYPE){
                if(isset($tag['field']) and isset($tag['field']['children']) and is_array($tag['field']['children'])){
                    foreach ($tag['field']['children'] as $block){
                        $block['label'] =  $block['name'];
                        $this->loop_tags[ $block['slug'] ] = $block;

                        if(isset($block['children']) and is_array($block['children'])){
                            foreach ( $block['children'] as $sub_field ) {
                                $this->register_tag( $sub_field, $block ); // Recursive
                            }
                        }
                    }
                }
            }

            // Relational fields
            elseif(in_array($type, self::RELATIONAL_FIELDS)){
                $this->loop_tags[ $name ] = $tag;
            }
        }

        // Regular fields
        if(in_array(self::CONTEXT_TEXT, $contextsByType)){

            // Register here loop fields as raw values (not used by container loops)
            if(in_array(self::CONTEXT_LOOP, $contextsByType)){

                // Nested repeaters are not allowed
                if($field['type'] === MetaFieldModel::REPEATER_TYPE and !empty($field['parent_field_name'])){
                    return;
                }

                $name = $name . "_raw";
                $tag[ 'name' ] = '{' . $name . '}';
                $tag[ 'label' ] = $tag[ 'label' ] . " [RAW]";
            }

            $this->tags[ $name ] = $tag;
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function get_fields()
    {
        $fields = [];
        $fieldGroups = MetaRepository::get([
                'clonedFields' => true
        ]);

        foreach ($fieldGroups as $fieldGroup){
            if(count($fieldGroup->getBelongs()) > 0){
                foreach ($fieldGroup->getBelongs() as $belong){
                    $fields = array_merge($fields, self::get_group_fields($belong, $fieldGroup->toStdObject()));
                }
            }
        }

        return $fields;
    }

    /**
     * @param BelongModel $belong
     * @param $group
     *
     * @return array
     */
    protected static function get_group_fields(BelongModel $belong, $group)
    {
        $fields = [];
        $belongs_to = $belong->getBelongsTo();
        $find = $belong->getFindAsSting();

        $excludedFields = [
                MetaFieldModel::CLONE_TYPE
        ];

        foreach ($group->boxes as $box){
            foreach ($box->fields as $acpt_meta_field) {
                $field_slug = $find . ' ' . $acpt_meta_field->boxName . ' ' . $acpt_meta_field->name;
                $field_slug = strtolower(str_replace(' ', '_', $field_slug));
                $field_type = $acpt_meta_field->type;
                $group_name = 'ACPT';
                $display_field_name = '['.Translator::translate($find) . '] - ' . $acpt_meta_field->boxName . ' ' . $acpt_meta_field->name;

                $children = [];

                // Repeater fields
                if( isset($acpt_meta_field->children) and ! empty( $acpt_meta_field->children ) ){
                    foreach ($acpt_meta_field->children as $child_field){
                        $child_field_slug = $field_slug . ' ' . $child_field->name;
                        $child_field_slug = strtolower(str_replace(' ', '_', $child_field_slug));
                        $child_field_type = $child_field->type;
                        $child_display_field_name = $display_field_name . ' ' . $child_field->name;

                        $nestedChildren = [];

                        // nested repeaters inside a repeater
                        if( isset($child_field->children) and ! empty( $child_field->children ) ){
                            foreach ($child_field->children as $nested_child_field){
                                $nested_child_field_slug = $field_slug . ' ' . $nested_child_field->name;
                                $nested_child_field_slug = strtolower(str_replace(' ', '_', $nested_child_field_slug));
                                $nested_child_field_type = $nested_child_field->type;
                                $nested_child_display_field_name = $display_field_name . ' ' . $nested_child_field->name;

                                if(!in_array($nested_child_field_type, $excludedFields)){
                                    $nestedChildren[] = [
                                            'belongsTo' => $belongs_to,
                                            'key' => $nested_child_field->db_name,
                                            'find' => $find,
                                            'slug' => $nested_child_field_slug,
                                            'type' => $nested_child_field_type,
                                            'group_name' => $group_name,
                                            'name' => $nested_child_display_field_name,
                                            'box_name' => $acpt_meta_field->boxName,
                                            'field_name' => $nested_child_field->name,
                                            'parent_field_name' => $child_field->name,
                                    ];
                                }
                            }
                        }

                        if(!in_array($child_field_type, $excludedFields)){
                            $children[] = [
                                    'belongsTo' => $belongs_to,
                                    'key' => $child_field->db_name,
                                    'find' => $find,
                                    'slug' => $child_field_slug,
                                    'type' => $child_field_type,
                                    'group_name' => $group_name,
                                    'name' => $child_display_field_name,
                                    'box_name' => $acpt_meta_field->boxName,
                                    'field_name' => $child_field->name,
                                    'parent_field_name' => $acpt_meta_field->name,
                                    'children' => $nestedChildren,
                            ];
                        }
                    }
                }

                // Flexible fields
                if( isset($acpt_meta_field->blocks) and ! empty( $acpt_meta_field->blocks ) ){
                    foreach ($acpt_meta_field->blocks as $child_block){

                        $block_slug = $field_slug . ' ' . $child_block->name;
                        $block_slug = strtolower(str_replace(' ', '_', $block_slug));
                        $block_display_name = $display_field_name . ' ' . $child_block->name;

                        $nested_fields = [];
                        $nested_blocks = [];
                        $nested_block_fields = [];

                        if(isset($child_block->fields) and is_array($child_block->fields) and !empty($child_block->fields)){
                            foreach ($child_block->fields as $nested_field){
                                $nested_field_slug = $field_slug . ' ' . $child_block->name . ' ' . $nested_field->name;
                                $nested_field_slug = strtolower(str_replace(' ', '_', $nested_field_slug));
                                $nested_field_type = $nested_field->type;
                                $nested_display_field_name = $display_field_name . ' ' . $child_block->name . ' ' . $nested_field->name;

                                // nested blocks inside a flexible
                                if( isset($nested_field->blocks) and ! empty( $nested_field->blocks ) ){
                                    foreach ($nested_field->blocks as $nested_block){

                                        $nested_block_slug = $nested_field_slug . ' ' . $nested_block->name;
                                        $nested_block_slug = strtolower(str_replace(' ', '_', $nested_block_slug));
                                        $nested_block_display_name = $nested_display_field_name . ' ' . $nested_block->name;

                                        if(
                                                isset($nested_block->fields) and
                                                isset($nested_block->name) and
                                                is_array($nested_block->fields) and
                                                !empty($nested_block->fields) and
                                                isset($nested_block_field->name)
                                        ){
                                            foreach ($nested_block->fields as $nested_block_field){
                                                $nested_block_field_slug = $field_slug . ' ' . $nested_block->name . ' ' . $nested_block_field->name;
                                                $nested_block_field_slug = strtolower(str_replace(' ', '_', $nested_block_field_slug));
                                                $nested_block_field_type = $nested_block_field->type;
                                                $nested_block_display_field_name = $display_field_name . ' ' . $nested_block->name . ' ' . $nested_block_field->name;

                                                $nested_block_fields[] =  [
                                                        'belongsTo' => $belongs_to,
                                                        'key' => $nested_block_field->db_name,
                                                        'find' => $find,
                                                        'slug' => $nested_block_field_slug,
                                                        'type' => $nested_block_field_type,
                                                        'group_name' => $group_name,
                                                        'name' => $nested_block_display_field_name,
                                                        'box_name' => $acpt_meta_field->boxName,
                                                        'field_name' => $nested_block_field->name,
                                                        'parent_field_name' => $nested_field->name,
                                                        'parent_block_name' => $nested_block_slug->name,
                                                ];
                                            }
                                        }

                                        $nested_blocks[] = [
                                                'belongsTo' => $belongs_to,
                                                'key' => $nested_block->name,
                                                'find' => $find,
                                                'slug' => $nested_block_slug,
                                                'type' => 'Block',
                                                'group_name' => $group_name,
                                                'name' => $nested_block_display_name,
                                                'box_name' => $acpt_meta_field->boxName,
                                                'parent_field_name' => $acpt_meta_field->name,
                                                'block_name' => $nested_block->name,
                                                'children' => $nested_block_fields,
                                        ];

                                    }
                                }

                                $nested_fields[] = [
                                        'belongsTo' => $belongs_to,
                                        'key' => $nested_field->db_name,
                                        'find' => $find,
                                        'slug' => $nested_field_slug,
                                        'type' => $nested_field_type,
                                        'group_name' => $group_name,
                                        'name' => $nested_display_field_name,
                                        'box_name' => $acpt_meta_field->boxName,
                                        'field_name' => $nested_field->name,
                                        'parent_field_name' => $acpt_meta_field->name,
                                        'parent_block_name' => $child_block->name,
                                        'children' => $nested_blocks,
                                ];
                            }
                        }

                        $children[] = [
                                'belongsTo' => $belongs_to,
                                'key' => $child_block->name,
                                'find' => $find,
                                'slug' => $block_slug,
                                'type' => 'Block',
                                'group_name' => $group_name,
                                'name' => $block_display_name,
                                'box_name' => $acpt_meta_field->boxName,
                                'parent_field_name' => $acpt_meta_field->name,
                                'block_name' => $child_block->name,
                                'children' => $nested_fields,
                        ];
                    }
                }

                // Add List fields children
                if($acpt_meta_field->type === MetaFieldModel::LIST_TYPE){

                    $child_field_slug = $field_slug . '_value';
                    $child_field_slug = strtolower(str_replace(' ', '_', $child_field_slug));
                    $child_field_type = MetaFieldModel::TEXT_TYPE;
                    $child_display_field_name = $display_field_name . ' value';

                    if(!in_array($child_field_type, $excludedFields)){
                        $children[] = [
                                'belongsTo' => $belongs_to,
                                'key' => $acpt_meta_field->db_name,
                                'find' => $find,
                                'slug' => $child_field_slug,
                                'type' => $child_field_type,
                                'group_name' => $group_name,
                                'name' => $child_display_field_name,
                                'box_name' => $acpt_meta_field->boxName,
                                'field_name' => $acpt_meta_field->name,
                                'parent_field_name' => $acpt_meta_field->name,
                        ];
                    }
                }

                if(!in_array($field_type, $excludedFields)){
                    $fields[] = [
                            'belongsTo' => $belongs_to,
                            'key' => $acpt_meta_field->db_name,
                            'find' => $find,
                            'slug' => $field_slug,
                            'type' => $field_type,
                            'group_name' => $group_name,
                            'name' => $display_field_name,
                            'box_name' => $acpt_meta_field->boxName,
                            'field_name' => $acpt_meta_field->name,
                            'children' => $children,
                    ];
                }
            }
        }

        return $fields;
    }

    /**
     * Get tag value main function
     *
     * @param string $tag
     * @param \WP_Post $post
     * @param array $args
     * @param string $context
     *
     * @return array|string|void
     * @throws \Exception
     */
    public function get_tag_value( $tag, $post, $args, $context )
    {
        try {
            if(!isset($this->tags[$tag])){
                return null;
            }

            if(!isset($this->tags[$tag]['field'])){
                return null;
            }

            $post_id = isset( $post->ID ) ? $post->ID : '';
            $field = $this->tags[ $tag ]['field'];
            $contexts = $this->tags[ $tag ]['contexts'];

            if( !in_array( $context, $contexts )){
                return;
            }

            // STEP: Check for filter args
            $filters = $this->get_filters_from_args( $args );

            // STEP: Get the value
            $raw_acpt_value = $this->get_raw_value( $tag, $post_id );

            $raw_value = $raw_acpt_value['value'] ?? null;
            $after = $raw_acpt_value['after'] ?? null;
            $before = $raw_acpt_value['before'] ?? null;

            // Display a nested field inside a repeater by its index ($args[1])
            if(
                    in_array(self::IS_NESTED_IN_A_REPEATER, $args) and
                    is_array($raw_value) and
                    isset($args[1]) and
                    is_numeric($args[1]) and
                    isset($raw_value[(int)$args[1]])
            ){
                $raw_value = $raw_value[(int)$args[1]];

                // reset $args
                unset($args[0]);
                unset($args[1]);
                $args = array_values($args);
            }

            if(empty($raw_value)){
                return null;
            }

            $value = null;

            // render tag depending on its type
            switch ($field['type']){

                case MetaFieldModel::ADDRESS_TYPE:

                    if(is_array($raw_value) and  isset($raw_value['address'])){
                        $value = $raw_value['address'];

                        if(!empty($args)){
                            foreach ($args as $arg){
                                switch ($arg){
                                    case "lat":
                                        if(isset($raw_value['lat'])){
                                            $value = $raw_value['lat'];
                                        }
                                        break;

                                    case "lng":
                                        if(isset($raw_value['lng'])){
                                            $value = $raw_value['lng'];
                                        }
                                        break;

                                    case "city":
                                        if(isset($raw_value['city'])){
                                            $value = $raw_value['city'];
                                        }
                                        break;

                                    case "country":
                                        if(isset($raw_value['country'])){
                                            $value = $raw_value['country'];
                                        }
                                        break;
                                }
                            }
                        }
                    }

                    $value = $before . $value . $after;

                    break;

                case MetaFieldModel::ADDRESS_MULTI_TYPE:

                    if(is_array($raw_value)){

                        $addresses = [];
                        $lat = [];
                        $lng = [];
                        $cities = [];
                        $countries = [];

                        foreach ($raw_value as $raw_val){
                            $addresses[] = $raw_val['address'];
                            $lat[] = $raw_val['lat'];
                            $lng[] = $raw_val['lng'];
                            $cities[] = $raw_val['city'];
                            $countries[] = $raw_val['country'];
                        }

                        $value = implode(", ", $addresses);

                        if(!empty($args)){
                            foreach ($args as $arg){
                                switch ($arg){
                                    case "lat":
                                        $value = implode(", ", $lat);
                                        break;

                                    case "lng":
                                        $value = implode(", ", $lng);
                                        break;

                                    case "city":
                                        $value = implode(", ", $cities);
                                        break;

                                    case "country":
                                        $value = implode(", ", $countries);
                                        break;
                                }
                            }
                        }

                        $value = $before . $value . $after;
                    }

                    break;

                case MetaFieldModel::AUDIO_TYPE:

                    // This means that the audio is used as text (return a string)
                    if($context === 'text'){
                        $value = get_the_title($raw_value);
                        // This means that the audio is used as media src (return the media ID)
                    } elseif($context === 'media'){
                        $filters['audio']       = true;
                        $filters['object_type'] = 'media';

                        if(is_array($raw_value)){
                            $value = [];

                            foreach ($raw_value as $img){
                                if(is_numeric($img)){
                                    $value[] = $img;
                                }
                            }
                        } else {
                            if(is_numeric($raw_value)){
                                $value = [$raw_value];
                            }
                        }
                    }

                    break;

                case MetaFieldModel::COUNTRY_TYPE:

                    if(is_array($raw_value) and isset($raw_value['value'])){
                        $value = $before . $raw_value['value'] . $after;
                    }

                    break;

                case MetaFieldModel::RATING_TYPE:

                    if(!empty($raw_value) and is_numeric($raw_value)){
                        $value = $before . ($raw_value/2) . $after;
                    }

                    break;

                case MetaFieldModel::CURRENCY_TYPE:

                    if(
                            is_array($raw_value) and
                            isset($raw_value['amount']) and
                            isset($raw_value['unit'])
                    ){
                        $value = $before . $this->render_amount_field($raw_value['amount'], $raw_value['unit'],'currency', $args). $after;
                    }

                    break;

                case MetaFieldModel::DATE_RANGE_TYPE:

                    if(!isset($raw_value['value'])){
                        return null;
                    }

                    if(!isset($raw_value['object'])){
                        return null;
                    }

                    $dateTimeObjects = $raw_value['object'];
                    $values = $raw_value['value'];
                    $saved_format = $raw_value['format'] ?? "Y-m-d";

                    if(is_array($values) and !empty($values) and count($values) === 2){

                        $format = $saved_format;
                        $from = $values[0];
                        $to = $values[1];

                        if(!empty($args)){
                            foreach ($args as $arg){
                                if(Date::isDateFormatValid($arg)){
                                    $format = $arg;
                                    break;
                                }
                            }
                        }

                        if($format !== null and $dateTimeObjects[0] instanceof \DateTime and $dateTimeObjects[1] instanceof \DateTime){
                            $from = Date::format($format, $dateTimeObjects[0]);
                            $to = Date::format($format, $dateTimeObjects[1]);
                        }

                        $value  = $before;
                        $value .= $from;
                        $value .= ' - ';
                        $value .= $to;
                        $value .= $after;
                    }

                    break;

                case MetaFieldModel::TIME_TYPE:
                case MetaFieldModel::DATE_TYPE:

                    if(!isset($raw_value['value'])){
                        return null;
                    }

                    if(!isset($raw_value['object'])){
                        return null;
                    }

                    /** @var \DateTime $dateTimeObject */
                    $dateTimeObject = $raw_value['object'];
                    $value = $raw_value['value'];
                    $saved_format = ($field['type'] === MetaFieldModel::DATE_TYPE) ? "Y-m-d" : "H:i:s";
                    $saved_format = $raw_value['format'] ? $raw_value['format'] : $saved_format;

                    if(!is_string($value)){
                        return null;
                    }

                    if($value !== null and is_string($value) and $value !== ''){

                        $format = $saved_format;

                        if(!empty($args)){
                            $rawFormat = implode(":", $args); // to allow H:i:s synthax

                            if(Date::isDateFormatValid($rawFormat)){
                                $format = $rawFormat;
                            }
                        }

                        $value = $before . Date::format($format, $dateTimeObject) . $after;
                    }

                    break;

                case MetaFieldModel::DATE_TIME_TYPE:

                    if(!isset($raw_value['value'])){
                        return null;
                    }

                    if(!isset($raw_value['object'])){
                        return null;
                    }

                    /** @var \DateTime $dateTimeObject */
                    $dateTimeObject = $raw_value['object'];
                    $value = $raw_value['value'];
                    $saved_format = $raw_value['format'] ?? "Y-m-d H:i:s";

                    if(!is_string($value)){
                        return null;
                    }

                    if($value !== null and is_string($value) and $value !== ''){

                        $format = $saved_format;

                        if(!empty($args)){

                            $dateFormat = $args[0];
                            $dateTimeFormat = $dateFormat;
                            unset($args[0]);

                            if(!empty($args)){
                                $timeFormat = implode(":", $args);
                                $dateTimeFormat .= " " . $timeFormat;
                            }

                            if(!empty($dateTimeFormat) and Date::isDateFormatValid($dateTimeFormat)){
                                $format = $dateTimeFormat;
                            }
                        }

                        $value = $before . Date::format($format, $dateTimeObject) . $after;
                    }

                    break;

                case MetaFieldModel::EMAIL_TYPE:

                    if(!is_string($raw_value)){
                        return null;
                    }

                    if($context === 'link' or $context === 'media'){
                        $filters['link'] = true;
                        $value = "mailto:". Email::sanitize($raw_value);
                    } else {
                        $value = $before.$raw_value.$after;
                    }

                    break;

                case MetaFieldModel::NUMBER_TYPE:

                    if(is_numeric($raw_value)){
                        $value = $raw_value;
                        $decimals = (isset($args[0]) ? (int)$args[0] : null);
                        $decimal_point = (isset($args[1]) ? $args[1] : null);
                        $separator = (isset($args[2]) ? $args[2] : null);

                        if($decimals !== null and $decimal_point === null and $separator === null){
                            $value = number_format($raw_value, $decimals, ".", "");
                        } elseif($decimals !== null and $decimal_point and $separator === null){
                            $value = number_format($raw_value, $decimals, $decimal_point, "");
                        } elseif($decimals !== null and $decimal_point and $separator){
                            $value = number_format($raw_value, $decimals, $decimal_point, $separator);
                        }

                        $value = $before . $value . $after;
                    }

                    break;

                case MetaFieldModel::LENGTH_TYPE:

                    if(
                            is_array($raw_value) and
                            isset($raw_value['length']) and
                            isset($raw_value['unit'])
                    ){
                        $value = $before . $this->render_amount_field($raw_value['length'], $raw_value['unit'],'length', $args) . $after;
                    }

                    break;

                case MetaFieldModel::LIST_TYPE:
                case MetaFieldModel::SELECT_MULTI_TYPE:
                case MetaFieldModel::CHECKBOX_TYPE:

                    $value = $this->render_list_item($raw_value, $args);
                    break;

                case MetaFieldModel::REPEATER_TYPE:

                    if(is_array($raw_value) and
                            count($args) >= 2 and
                            is_string($args[0]) and
                            is_numeric($args[1])
                    ){
                        $fieldIndex = null;
                        $fieldName = $args[0];
                        $args[0] = self::IS_NESTED_IN_A_REPEATER;

                        $filterChildren = array_filter($field['children'], function($child) use($fieldName){
                            return $child['field_name'] === $fieldName;
                        });

                        if(count($filterChildren) === 1){
                            $fieldIndex = array_keys($filterChildren)[0];
                        }

                        if($fieldIndex !== null){

                            // prefix
                            $prefix = explode("_", $tag);
                            $prefix = $prefix[0];

                            // tag
                            $nestedTag = $prefix."_".$field['children'][$fieldIndex]['slug'];
                            $nestedField = array_values($filterChildren)[0];

                            if($nestedField['type'] === MetaFieldModel::LIST_TYPE){
                                $nestedTag = $nestedTag."_raw";
                            }

                            $value = $this->get_tag_value($nestedTag, $post, $args, $context);
                        }
                    }

                    break;

                case MetaFieldModel::FILE_TYPE:

                    if(is_array($raw_value)){

                        // File field can belong to 4 different contexts: text, link, media or video
                        switch ($context){
                            case "text":
                                if(isset($raw_value['file']) and is_numeric($raw_value['file'])){
                                    if(isset($raw_value['label'])){
                                        $value = $raw_value['label'];
                                    } else {
                                        $file = WPAttachment::fromId($raw_value['file']);
                                        $value = $file->getTitle();
                                    }

                                    $value = $before . $value . $after;
                                }

                                break;

                            case "link":
                                $filters['link'] = true;
                                $value = "url";

                                if(isset($raw_value['file']) and is_numeric($raw_value['file'])){
                                    $file = WPAttachment::fromId($raw_value['file']);
                                    $value = $file->getSrc();
                                }

                                break;

                            case "video":
                            case "media":

                                if($context === 'video'){
                                    $filters['video']   = true;
                                }

                                if($context === 'media'){
                                    $filters['link']   = true;
                                }

                                $filters['link'] = true;
                                $filters['object_type'] = 'media';

                                if(isset($raw_value['file']) and is_numeric($raw_value['file'])){
                                    $value = [$raw_value['file']];
                                } elseif(isset($raw_value['file']) and !empty($raw_value['file']) and is_numeric($raw_value['file'])){
                                    $value = [$raw_value['file']];
                                } else {
                                    $value = [];
                                }

                                break;
                        }
                    }

                    break;

                case MetaFieldModel::VIDEO_TYPE:

                    $filters['video']   = true;
                    $filters['object_type'] = 'media';

                    if(is_array($raw_value)){
                        $value = [];

                        foreach ($raw_value as $img){
                            if(is_numeric($img)){
                                $value[] = $img;
                            }
                        }
                    } else {
                        if(is_numeric($raw_value)){
                            $value = [$raw_value];
                        }
                    }

                    break;

                case MetaFieldModel::ICON_TYPE:

                    if(is_string($raw_value)){
                        $filters['object_type'] = 'media';
                        $filters['image']   = true;
                        $value = $raw_value;
                    }

                    break;

                case MetaFieldModel::GALLERY_TYPE:
                case MetaFieldModel::IMAGE_SLIDER_TYPE:
                case MetaFieldModel::IMAGE_TYPE:

                    $filters['object_type'] = 'media';
                    $filters['image']   = true;
                    $filters['separator']   = '';

                    $index = isset($args[0]) ? $args[0] : null;

                    // check is a single WPAttachment or not
                    if(is_numeric($raw_value)){
                        $value = [$raw_value];
                    } else {
                        $value = [];
                        if(is_array($raw_value)){
                            foreach ($raw_value as $img){
                                if(is_numeric($img)){
                                    $value[] = $img;
                                } else {
                                    if(is_array($img)){
                                        foreach ($img as $nested_img){
                                            if(is_numeric($nested_img)){
                                                $value[] = $nested_img;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // sorting
                        if($index === 'desc'){
                            $value = array_reverse($value);
                        }

                        if($index === 'rand'){
                            shuffle($value);
                        }

                        // get an single element of the gallery
                        if($index !== null and is_numeric($index) and isset($value[$index])){
                            $value = [$value[$index]];
                        }
                    }

                    break;

                case MetaFieldModel::PHONE_TYPE:

                    if(!is_array($raw_value)){
                        return null;
                    }

                    if(!isset($raw_value['value'])){
                        return null;
                    }

                    $dial = $raw_value['dial'] ?? null;
                    $val = $raw_value['value'] ?? null;

                    if($context === 'link' or $context === 'media'){
                        $filters['link'] = true;
                        $value = Phone::format($val, $dial, Phone::FORMAT_RFC3966);
                    } else {
                        $allowed = [
                                Phone::FORMAT_ORIGINAL,
                                Phone::FORMAT_E164,
                                Phone::FORMAT_INTERNATIONAL,
                                Phone::FORMAT_NATIONAL,
                        ];

                        $format = (isset($args[0]) and in_array($args[0], $allowed)) ? $args[0] : Phone::FORMAT_E164;
                        $value = Phone::format($val, $dial, $format);
                        $value = $before.$value.$after;
                    }

                    break;

                case MetaFieldModel::EDITOR_TYPE:

                    if(is_string($raw_value)){
                        $value = WPUtils::removeEmptyParagraphs($raw_value);
                        $value = WPUtils::renderShortCode($value);
                        $value = $before . $value . $after;
                    }

                    break;

                case MetaFieldModel::TEXTAREA_TYPE:

                    if(is_string($raw_value)){
                        $value = WPUtils::renderShortCode($raw_value, true);
                        $value = $before . $value . $after;
                    }

                    break;

                case MetaFieldModel::TOGGLE_TYPE:
                    $value = ($raw_value == 1) ? esc_html__( 'True', 'bricks' ) : esc_html__( 'False', 'bricks' );
                    break;

                case MetaFieldModel::WEIGHT_TYPE:

                    if(
                            is_array($raw_value) and
                            isset($raw_value['weight']) and
                            isset($raw_value['unit'])
                    ){
                        $value = $before . $this->render_amount_field($raw_value['weight'], $raw_value['unit'], 'weight', $args) . $after;
                    }

                    break;

                case MetaFieldModel::POST_TYPE:

                    $filters['link'] = true;
                    $value = null;

                    if(is_array($raw_value)){
                        $value = [];

                        foreach ($raw_value as $obj){
                            if(isset($obj['type']) and isset($obj['id'])){
                                if($obj['type'] === MetaTypes::CUSTOM_POST_TYPE){
                                    $filters['object_type'] = 'post';
                                    $value[] = $obj['id'];
                                }

                                if($obj['type'] === MetaTypes::TAXONOMY){
                                    $filters['object_type'] = 'term';
                                    $value[] =  $obj['id'];
                                }

                                if($obj['type'] === MetaTypes::USER){
                                    $filters['object_type'] = 'user';
                                    $value[] =  $obj['id'];
                                }
                            }
                        }

                    } else {
                        if(isset($raw_value['type']) and isset($raw_value['id'])){
                            if($raw_value['type'] === MetaTypes::CUSTOM_POST_TYPE){
                                $filters['object_type'] = 'post';
                                $value = $raw_value['id'];
                            }

                            if($raw_value['type'] === MetaTypes::TAXONOMY){
                                $filters['object_type'] = 'term';
                                $value = $raw_value['id'];
                            }

                            if($raw_value['type'] === MetaTypes::USER){
                                $filters['object_type'] = 'user';
                                $value = $raw_value['id'];
                            }
                        }

                        // @TODO OP object?
                    }

                    break;

                case MetaFieldModel::POST_OBJECT_TYPE:

                    // display the post as string (title)
                    if($context === 'text'){
                        $value = (get_post_status($raw_value)) ? get_the_title($raw_value) : null;
                    }

                    // display the post as link
                    elseif($context === 'link'){
                        $filters['object_type'] = 'post';
                        $show_as_link = apply_filters( 'bricks/acf/taxonomy/show_as_link', true, $raw_value, $field );

                        if ( $show_as_link ) {
                            $filters['link'] = true;
                        }

                        if(is_numeric($raw_value)){
                            $value = $raw_value;
                        }
                    }

                    break;

                case MetaFieldModel::POST_OBJECT_MULTI_TYPE:

                    $value = [];

                    // display the post as string (title)
                    if($context === 'text'){

                        if(is_array($raw_value)){
                            foreach ($raw_value as $post_id){
                                if(get_post_status($post_id)){
                                    $value[] = get_the_title($post_id);
                                }
                            }
                        }

                        $value = implode(", ", $value);
                    }

                    // display the post as link
                    elseif($context === 'link'){
                        $filters['object_type'] = 'post';
                        $show_as_link = apply_filters( 'bricks/acf/taxonomy/show_as_link', true, $raw_value, $field );

                        if ( $show_as_link ) {
                            $filters['link'] = true;
                        }

                        if(is_array($raw_value)){
                            foreach ($raw_value as $post_id){
                                if(is_numeric($post_id)){
                                    $value[] = $post_id;
                                }
                            }
                        }
                    }

                    break;

                case MetaFieldModel::TABLE_TYPE:

                    if(is_string($raw_value) and Strings::isJson($raw_value)){
                        $generator = new TableFieldGenerator($raw_value);
                        $value = $generator->generate();
                        $value = $before . $value . $after;
                    }

                    break;

                case MetaFieldModel::TERM_OBJECT_TYPE:

                    // display the term as string (name)
                    if($context === 'text'){
                        $term = get_term( $raw_value );

                        if($term instanceof \WP_Term){
                            $value = $term->name;
                        }
                    }

                    // display the term as link
                    elseif($context === 'link'){
                        $filters['object_type'] = 'term';

                        // NOTE: Undocumented
                        $show_as_link = apply_filters( 'bricks/acf/taxonomy/show_as_link', true, $raw_value, $field );

                        if ( $show_as_link ) {
                            $filters['link'] = true;
                        }

                        if(is_numeric($raw_value)){
                            $term = get_term( $raw_value );

                            if($term instanceof \WP_Term){
                                $filters['taxonomy'] = $term->taxonomy;
                            }

                            $value = $raw_value;
                        }
                    }

                    break;

                case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:

                    $value = [];

                    // display the term as string (name)
                    if($context === 'text'){

                        if(is_array($raw_value)){
                            foreach ($raw_value as $term_id){
                                $term = get_term( $term_id );

                                if($term instanceof \WP_Term){
                                    $value[] = $term->name;
                                }
                            }
                        }

                        $value = implode(", ", $value);
                    }

                    // display the term as link
                    elseif($context === 'link'){
                        $filters['object_type'] = 'term';

                        // NOTE: Undocumented
                        $show_as_link = apply_filters( 'bricks/acf/taxonomy/show_as_link', true, $raw_value, $field );

                        if($show_as_link) {
                            $filters['link'] = true;
                        }

                        if(is_array($raw_value)){
                            foreach ($raw_value as $term_id){
                                if(is_numeric($term_id)){
                                    $term = get_term( $term_id );

                                    if($term instanceof \WP_Term){
                                        $filters['taxonomy'] = $term->taxonomy;
                                    }

                                    $value[] = $term_id;
                                }
                            }
                        }
                    }

                    break;

                case MetaFieldModel::USER_TYPE:

                    // display the user as string (user label)
                    if($context === 'text'){
                        $user = get_user( $raw_value );

                        if($user instanceof \WP_User){
                            $value = Users::getUserLabel($user);
                        }
                    }

                    // display the user as link
                    elseif($context === 'link'){
                        $filters['object_type'] = 'user';

                        // NOTE: Undocumented
                        $show_as_link = apply_filters( 'bricks/acf/taxonomy/show_as_link', true, $raw_value, $field );

                        if($show_as_link) {
                            $filters['link'] = true;
                        }

                        if(is_numeric($raw_value)){
                            $value = $raw_value;
                        }
                    }

                    break;

                case MetaFieldModel::USER_MULTI_TYPE:

                    $value = [];

                    // display the term as string (name)
                    if($context === 'text'){

                        if(is_array($raw_value)){
                            foreach ($raw_value as $user_id){
                                $user = get_user( $user_id );

                                if($user instanceof \WP_User){
                                    $value[] = Users::getUserLabel($user);
                                }
                            }
                        }

                        $value = implode(", ", $value);
                    }

                    // display the term as link
                    elseif($context === 'link'){
                        $filters['object_type'] = 'user';

                        // NOTE: Undocumented
                        $show_as_link = apply_filters( 'bricks/acf/taxonomy/show_as_link', true, $raw_value, $field );

                        if($show_as_link) {
                            $filters['link'] = true;
                        }

                        if(is_array($raw_value)){
                            foreach ($raw_value as $user_id){
                                if(is_numeric($user_id)){
                                    $value[] = $user_id;
                                }
                            }
                        }
                    }

                    break;

                case MetaFieldModel::BARCODE_TYPE:

                    if(!is_array($raw_value)){
                        return null;
                    }

                    if(!isset($raw_value['text'])){
                        return null;
                    }

                    if(!isset($raw_value['value'])){
                        return null;
                    }

                    if($context === 'link' or $context === 'media'){
                        $filters['link'] = true;
                        $value = $raw_value['value'];
                    } else {
                        $value = Barcode::render($raw_value);
                        $value = $before . $value . $after;
                    }

                    break;

                case MetaFieldModel::QR_CODE_TYPE:

                    if(!is_array($raw_value)){
                        return null;
                    }

                    if(!isset($raw_value['url'])){
                        return null;
                    }

                    if(!isset($raw_value['value'])){
                        return null;
                    }

                    if(!isset($raw_value['value']['img'])){
                        return null;
                    }

                    if($context === 'link' or $context === 'media'){
                        $filters['link'] = true;
                        $value = $raw_value['url'];
                    } else {
                        $value = QRCode::render($raw_value);
                        $value = $before . $value . $after;
                    }

                    break;

                case MetaFieldModel::URL_TYPE:

                    if(!is_array($raw_value)){
                        return null;
                    }

                    if(!isset($raw_value['url'])){
                        return null;
                    }

                    if($context === 'link' or $context === 'media'){
                        $filters['link'] = true;
                        $value = $raw_value['url'];
                    } else {
                        if(!empty($args) and $args[0] === 'url'){
                            $value = $raw_value['url'];
                        } else {
                            $value = (!empty($raw_value['label'])) ? $before.$raw_value['label'].$after : $raw_value['url'];
                        }
                    }

                    break;

                default:
                    $value = $raw_value;

                    // array index
                    if(is_array($value) and !empty($args)){
                        if(is_numeric($args[0]) and isset($value[$args[0]])){
                            return $value[$args[0]];
                        }
                    }

                    if(is_string($value) and !empty($args)){

                        // truncate string text
                        if(is_numeric($args[0])){
                            $value = substr($value,0, (int)$args[0]);
                        }

                        // skip text sanitizing
                        if($args[0] === "skip_sanitize"){
                            $filters['skip_sanitize'] = true;
                        }
                    }

                    $value = $before . $value . $after;
            }

            // format value before rendering
            // exclude unsafe fields from formatting
            if(!in_array($field['type'], self::unsafeFields())){
                $value = $this->format_value_for_context( $value, $tag, $post_id, $filters, $context );
            }

            return $value;
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return null;
        }
    }

    /**
     * @param string $imgUrl
     *
     * @return int
     */
    private function attachment_url_to_postID($imgUrl)
    {
        $postId = attachment_url_to_postid($imgUrl);

        // try to find a scaled version
        if($postId === 0){
            $path = pathinfo($imgUrl);

            if(!is_array($path)){
                return 0;
            }

            $newFilename = $path['filename'] . '-scaled';
            $postId = attachment_url_to_postid($path['dirname'] . '/' . $newFilename . '.' . $path['extension']);
        }

        return $postId;
    }

    /**
     * @param $amount
     * @param $unit
     * @param $context
     * @param array $args
     *
     * @return string|null
     */
    private function render_amount_field($amount, $unit, $context, $args = [])
    {
        if(!is_numeric($amount)){
            return null;
        }

        $format = (!empty($args) and isset($args[0])) ? $args[0] : 'full';
        $decimalSeparator = (!empty($args) and isset($args[1])) ? $args[1] : ".";
        $thousandsSeparator = (!empty($args) and isset($args[2])) ? $args[2] : ",";
        $position = (!empty($args) and isset($args[3])) ? $args[3] : "after";
        $amount = number_format($amount, 2, $decimalSeparator, $thousandsSeparator);

        if($format === 'value'){
            return $amount;
        }

        if($format === 'symbol'){
            switch ($context){
                case "currency":
                    $unit = Currencies::getSymbol($unit);
                    break;

                case "length":
                    $unit = Lengths::getSymbol($unit);
                    break;

                case "weight":
                    $unit = Weights::getSymbol($unit);
                    break;
            }
        }

        if($position === 'before'){
            return $unit .' '. $amount;
        }

        return $amount . ' ' . $unit;
    }

    /**
     * @param $raw_value
     * @param array $args
     *
     * @return string|null
     */
    private function render_list_item( $raw_value, $args = [])
    {
        if(!is_array($raw_value)){
            return null;
        }

        if(isset($args[0]) and is_numeric($args[0]) and isset($raw_value[(int)$args[0]])){
            return $raw_value[(int)$args[0]];
        }

        if(isset($args[0]) and $args[0] === 'string'){
            $separator = (isset($args[1])) ? $args[1] : ",";

            return implode($separator, $raw_value);
        }

        if(isset($args[0]) and $args[0] === 'ol'){
            $classes = (isset($args[1])) ? $args[1] : null;
            $value = '<ol>';

            foreach ( $raw_value as $item){
                $value .= '<li class="'.$classes.'">' . $item . '</li>';
            }

            $value .= '</ol>';

            return $value;
        }

        $classes = (isset($args[0]) and $args[0] === 'li' and isset($args[1])) ? $args[1] : null;
        $value = '<ul>';

        foreach ( $raw_value as $item){
            $value .= '<li class="'.$classes.'">' . $item . '</li>';
        }

        $value .= '</ul>';

        return $value;
    }

    /**
     * @param $tag
     * @param $post_id
     *
     * @return array|mixed|string|null
     * @throws \Exception
     */
    private function get_raw_value( $tag, $post_id )
    {
        $tag_object = $this->tags[ $tag ];

        if(!isset($tag_object['field'])){
            return '';
        }

        $field = $tag_object['field'];

        if ( \Bricks\Query::is_looping() ) {

            // Check if this loop belongs to this provider
            $query_type = \Bricks\Query::get_query_object_type(); // post or term

            // Flexible/Repeater/List fields
            if ( array_key_exists( $query_type, $this->loop_tags ) ) {

                $parent_tag = $this->loop_tags[ $query_type ];
                $query_loop_object = \Bricks\Query::get_loop_object();

                // Render a field nested in List or Repeater field
                if (
                        isset( $parent_tag['field']['slug'] ) &&
                        isset( $tag_object['parent']['slug'] ) &&
                        $parent_tag['field']['slug'] == $tag_object['parent']['slug']
                ) {

                    // For List field
                    if($parent_tag['field']['type'] === MetaFieldModel::LIST_TYPE){
                        return $query_loop_object;
                    }

                    // For Repeater field: sub-field not found in the loop object (array)
                    if($parent_tag['field']['type'] === MetaFieldModel::REPEATER_TYPE){
                        if ( ! is_array( $query_loop_object ) || ! array_key_exists( $field['field_name'], $query_loop_object ) ) {
                            return '';
                        }
                    }

                    return $query_loop_object[ $field['field_name'] ];
                }

                // Render a field nested in a block
                if(isset($parent_tag['type']) and $parent_tag['type'] === 'Block'){

                    // calculate the numeric index of $query_loop_object
                    $nested_child_index = 0;

                    if(isset($parent_tag['children']) and is_array($parent_tag['children'])){
                        foreach ($parent_tag['children'] as $nested_field_index => $nested_field){
                            if($nested_field['slug'] === $field['slug']){
                                $nested_child_index = $nested_field['field_name'];
                            }
                        }
                    }

                    if(isset($query_loop_object[$nested_child_index])){
                        return $query_loop_object[$nested_child_index];
                    }

                    return null;
                }

            } elseif($query_type === 'term' and ($field['belongsTo'] === MetaTypes::TAXONOMY or $field['belongsTo'] === BelongsTo::TERM_ID)){ // Loop of taxonomies

                $query_loop_object = \Bricks\Query::get_loop_object();
                $field['term_id'] = $query_loop_object->term_id;

                return $this->get_acpt_value($field);
            } elseif($query_type === 'user' and ($field['belongsTo'] === MetaTypes::USER or $field['belongsTo'] === BelongsTo::USER_ID)){ // Loop of users

                $query_loop_object = \Bricks\Query::get_loop_object();
                $field['user_id'] = $query_loop_object->ID;

                return $this->get_acpt_value($field);
            }
        }

        // Display taxonomy meta
        if($field['belongsTo'] === MetaTypes::TAXONOMY and isset($field['find'])) {

            // We are in term archive page
            $queried_object = get_queried_object();

            if($queried_object instanceof \WP_Term){
                $field['term_id'] = $queried_object->term_id;

                return $this->get_acpt_value($field);
            }

            // Display all taxonomy meta for the current post
            global $post;
            $terms = get_the_terms($post->ID, $field['find']);

            $acpt_values = [];

            if(!empty($terms)){
                foreach ($terms as $term){
                    $field['term_id'] = $term->term_id;
                    $acpt_values[] = $this->get_acpt_value($field);
                }
            }

            return $acpt_values;
        }

        // STEP: Still here, get the regular value for this field
        $field['post_id'] = $post_id;

        return $this->get_acpt_value($field);
    }

    /**
     * @param $field
     *
     * @return mixed|null
     * @throws \Exception
     */
    private function get_acpt_value($field)
    {
        switch ($field['belongsTo']){
            case MetaTypes::OPTION_PAGE:
                return $this->get_option_page_meta_value($field);

            case BelongsTo::TERM_ID:
            case MetaTypes::TAXONOMY:
                return $this->get_tax_meta_value($field);

            case BelongsTo::USER_ID:
            case MetaTypes::USER:
                return $this->get_user_meta_value($field);

            default:
            case BelongsTo::POST_ID:
            case BelongsTo::PARENT_POST_ID:
            case BelongsTo::POST_TEMPLATE:
            case BelongsTo::POST_TAX:
            case MetaTypes::CUSTOM_POST_TYPE:
                return $this->get_post_meta_value($field);
        }
    }

    /**
     * @param $post_id
     * @param $field
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    private function get_post_meta_value($field)
    {
        if(!isset($field['post_id']) or !isset($field['box_name']) or !isset($field['field_name'])){
            return null;
        }

        // flexible field block nested element
        if(isset($field['parent_field_name'])){
            $acpt_value = get_acpt_field([
                    'post_id' => $field['post_id'],
                    'box_name' => $field['box_name'],
                    'field_name' => $field['parent_field_name'],
                    'with_context' => true,
                    'return' => 'raw',
            ]);

            $nested_values = [];

            if(is_array($acpt_value)){
                foreach ($acpt_value as $index => $nested_value){
                    $nested_values[] = get_acpt_child_field([
                            'post_id' => $field['post_id'],
                            'box_name' => $field['box_name'],
                            'parent_field_name' => $field['parent_field_name'],
                            'field_name' => $field['field_name'],
                            'index' => $index,
                            'with_context' => true,
                            'return' => 'raw',
                    ]);
                }
            }

            return $nested_values;
        }

        // repeater field nested element
        if(isset($field['children']) and !empty($field['children'])){

            // Example:
            //
            // 0 => [fancy => ciao]
            // 1 => [fancy => dsgffds fdsfddsf]
            // 2 => [fancy => dfsdfs]
            //
            return get_acpt_field([
                    'post_id' => $field['post_id'],
                    'box_name' => $field['box_name'],
                    'field_name' => $field['field_name'],
                    'with_context' => true,
                    'return' => 'raw',
            ]);
        }

        return get_acpt_field([
                'post_id' => $field['post_id'],
                'box_name' => $field['box_name'],
                'field_name' => $field['field_name'],
                'with_context' => true,
                'return' => 'raw',
        ]);
    }

    /**
     * @param $field
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    private function get_tax_meta_value($field)
    {
        if(!isset($field['term_id']) or !isset($field['box_name']) or !isset($field['field_name'])){
            return null;
        }

        // if child element
        if(isset($field['parent_field_name'])){
            $acpt_value = get_acpt_field([
                    'term_id' => $field['term_id'],
                    'box_name' => $field['box_name'],
                    'field_name' => $field['parent_field_name'],
                    'with_context' => true,
                    'return' => 'raw',
            ]);

            $nested_values = [];

            if(is_array($acpt_value)){
                foreach ($acpt_value as $index => $nested_value){
                    $nested_values[] = get_acpt_child_field([
                            'term_id' => $field['term_id'],
                            'box_name' => $field['box_name'],
                            'parent_field_name' => $field['parent_field_name'],
                            'field_name' => $field['field_name'],
                            'index' => $index,
                            'with_context' => true,
                            'return' => 'raw',
                    ]);
                }
            }

            return $nested_values;
        }

        return get_acpt_field([
                'term_id' => $field['term_id'],
                'box_name' => $field['box_name'],
                'field_name' => $field['field_name'],
                'with_context' => true,
                'return' => 'raw',
        ]);
    }

    /**
     * @param $field
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    private function get_user_meta_value($field)
    {
        if(!isset($field['user_id']) or !isset($field['box_name']) or !isset($field['field_name'])){
            return null;
        }

        // if child element
        if(isset($field['parent_field_name'])){
            $acpt_value = get_acpt_field([
                    'user_id' => $field['user_id'],
                    'box_name' => $field['box_name'],
                    'field_name' => $field['parent_field_name'],
                    'with_context' => true,
                    'return' => 'raw',
            ]);

            $nested_values = [];

            if(is_array($acpt_value)){
                foreach ($acpt_value as $index => $nested_value){
                    $nested_values[] = get_acpt_child_field([
                            'user_id' => $field['user_id'],
                            'box_name' => $field['box_name'],
                            'parent_field_name' => $field['parent_field_name'],
                            'field_name' => $field['field_name'],
                            'index' => $index,
                            'with_context' => true,
                            'return' => 'raw',
                    ]);
                }
            }

            return $nested_values;
        }

        return get_acpt_field([
                'user_id' => $field['user_id'],
                'box_name' => $field['box_name'],
                'field_name' => $field['field_name'],
                'with_context' => true,
                'return' => 'raw',
        ]);
    }

    /**
     * @param $field
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    private function get_option_page_meta_value($field)
    {
        if(!isset($field['find']) or !isset($field['box_name']) or !isset($field['field_name'])){
            return null;
        }

        // flexible field block nested element
        if(isset($field['parent_field_name'])){
            $acpt_value = get_acpt_field([
                    'option_page' => $field['find'],
                    'box_name' => $field['box_name'],
                    'field_name' => $field['parent_field_name'],
                    'with_context' => true,
                    'return' => 'raw',
            ]);

            $nested_values = [];

            if(is_array($acpt_value)){
                foreach ($acpt_value as $index => $nested_value){
                    $nested_values[] = get_acpt_child_field([
                            'option_page' => $field['find'],
                            'box_name' => $field['box_name'],
                            'parent_field_name' => $field['parent_field_name'],
                            'field_name' => $field['field_name'],
                            'index' => $index,
                            'with_context' => true,
                            'return' => 'raw',
                    ]);
                }
            }

            return $nested_values;
        }

        // repeater field nested element
        if(isset($field['children']) and !empty($field['children'])){

            // Example:
            //
            // 0 => [fancy => ciao]
            // 1 => [fancy => dsgffds fdsfddsf]
            // 2 => [fancy => dfsdfs]
            //
            return get_acpt_field([
                    'option_page' => $field['find'],
                    'box_name' => $field['box_name'],
                    'field_name' => $field['field_name'],
                    'with_context' => true,
                    'return' => 'raw',
            ]);
        }

        return get_acpt_field([
                'option_page' => $field['find'],
                'box_name' => $field['box_name'],
                'field_name' => $field['field_name'],
                'with_context' => true,
                'return' => 'raw',
        ]);
    }

    /**
     * @param $block
     *
     * @return array
     * @throws \Exception
     */
    private function get_acpt_block($block)
    {
        switch ($block['belongsTo']){
            case MetaTypes::OPTION_PAGE:
                return $this->get_option_page_meta_block_values($block);

            default:
            case MetaTypes::CUSTOM_POST_TYPE:
                return $this->get_post_meta_block_values($block);
        }
    }

    /**
     * @param $block
     *
     * @return array
     * @throws \Exception
     */
    private function get_post_meta_block_values($block)
    {
        if(!isset($block['post_id']) and !isset($block['box_name']) and !isset($block['parent_field_name'])){
            return null;
        }

        $acpt_parent_field_value = get_acpt_field([
                'post_id' => $block['post_id'],
                'box_name' => $block['box_name'],
                'field_name' => $block['parent_field_name'],
                'with_context' => true,
                'return' => 'raw',
        ]);

        $nested_values = [];

        if(is_array($acpt_parent_field_value) and isset($acpt_parent_field_value['blocks'])){
            foreach ($acpt_parent_field_value['blocks'] as $block_index => $block_values){
                foreach ($block_values as $block_name => $block_value){

                    if($block_name === $block['block_name']){
                        if(is_array($block_value)){
                            foreach ($block_value as $nested_child_field_name => $nested_child_field_values){
                                foreach ($nested_child_field_values as $nested_child_field_index => $nested_child_field_value){

                                    // This is a map like this:
                                    //
                                    // 0_0 => [
                                    //     'email_testo' => 'mauro@email.com',
                                    //     'testo' => 'value2',
                                    // ]
                                    //
                                    // An aggregate index is needed to aggregate data from blocks with same name
                                    // avoiding data override
                                    //
                                    $aggregate_index = $block_index . '_' . $nested_child_field_index;
                                    $nested_values[$aggregate_index][$nested_child_field_name] = get_acpt_block_child_field([
                                            'post_id' => $block['post_id'],
                                            'box_name' => $block['box_name'],
                                            'field_name' => $nested_child_field_name,
                                            'parent_field_name' => $block['parent_field_name'],
                                            'block_name' => $block_name,
                                            'block_index' => $block_index,
                                            'index' => $nested_child_field_index,
                                            'with_context' => true,
                                            'return' => 'raw',
                                    ]);
                                }
                            }
                        }

                    } elseif(isset($block['children']) and is_array($block['children']) and !empty($block['children'])){ // nested flexible fields
                        if(is_array($block_value)){
                            foreach ($block_value as $nested_block_value){
                                if(is_array($nested_block_value) and isset($nested_block_value['blocks'])) {
                                    foreach ( $nested_block_value['blocks'] as $nested_block_index => $nested_block_values ) {
                                        if(isset($nested_block_values[ $block['block_name'] ]) and is_array($nested_block_values[ $block['block_name'] ])){
                                            foreach ($nested_block_values[ $block['block_name'] ] as $deep_nested_blocks){
                                                if(isset($deep_nested_blocks['blocks']) and is_array($deep_nested_blocks['blocks'])){
                                                    foreach ($deep_nested_blocks['blocks'] as $deep_nested_block_index => $deep_nested_block){
                                                        if(isset($deep_nested_block[ $block['block_name'] ]) and is_array($deep_nested_block[ $block['block_name'] ])){
                                                            foreach ($deep_nested_block[ $block['block_name'] ] as $deep_nested_block_name_value => $deep_nested_block_value){
                                                                foreach ($deep_nested_block_value as $deep_nested_block_field_value_index => $deep_nested_block_field_value){
                                                                    $aggregate_index = $deep_nested_block_index . '_' . $deep_nested_block_field_value_index;
                                                                    $nested_values[$aggregate_index][$deep_nested_block_name_value] = $deep_nested_block_field_value;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $nested_values;
    }

    /**
     * @param $block
     *
     * @return array|null
     * @throws \Exception
     */
    private function get_option_page_meta_block_values($block)
    {
        if(!isset($block['find']) or !isset($block['box_name']) or !isset($block['parent_field_name'])){
            return null;
        }

        $acpt_parent_field_value = get_acpt_field([
                'option_page' => $block['find'],
                'box_name' => $block['box_name'],
                'field_name' => $block['parent_field_name'],
                'with_context' => true,
                'return' => 'raw',
        ]);

        $nested_values = [];

        if(is_array($acpt_parent_field_value) and isset($acpt_parent_field_value['blocks'])){
            foreach ($acpt_parent_field_value['blocks'] as $block_index => $block_values){
                foreach ($block_values as $block_name => $block_value){
                    if($block_name === $block['block_name']){
                        if(is_array($block_value)){
                            foreach ($block_value as $nested_child_field_name => $nested_child_field_values){
                                foreach ($nested_child_field_values as $nested_child_field_index => $nested_child_field_value){

                                    // This is a map like this:
                                    //
                                    // 0_0 => [
                                    //     'email_testo' => 'mauro@email.com',
                                    //     'testo' => 'value2',
                                    // ]
                                    //
                                    // An aggregate index is needed to aggregate data from blocks with same name
                                    // avoiding data override
                                    //
                                    $aggregate_index = $block_index . '_' . $nested_child_field_index;
                                    $nested_values[$aggregate_index][$nested_child_field_name] = get_acpt_block_child_field([
                                            'option_page' => $block['find'],
                                            'box_name' => $block['box_name'],
                                            'field_name' => $nested_child_field_name,
                                            'parent_field_name' => $block['parent_field_name'],
                                            'block_name' => $block_name,
                                            'block_index' => $block_index,
                                            'index' => $nested_child_field_index,
                                            'with_context' => true,
                                            'return' => 'raw',
                                    ]);
                                }
                            }
                        }
                    } elseif(isset($block['children']) and is_array($block['children']) and !empty($block['children'])){ // nested flexible fields
                        if(is_array($block_value)){
                            foreach ($block_value as $nested_block_value){
                                if(is_array($nested_block_value) and isset($nested_block_value['blocks'])) {
                                    foreach ( $nested_block_value['blocks'] as $nested_block_index => $nested_block_values ) {
                                        if(isset($nested_block_values[ $block['block_name'] ]) and is_array($nested_block_values[ $block['block_name'] ])){
                                            foreach ($nested_block_values[ $block['block_name'] ] as $deep_nested_blocks){
                                                if(isset($deep_nested_blocks['blocks']) and is_array($deep_nested_blocks['blocks'])){
                                                    foreach ($deep_nested_blocks['blocks'] as $deep_nested_block_index => $deep_nested_block){
                                                        if(isset($deep_nested_block[ $block['block_name'] ]) and is_array($deep_nested_block[ $block['block_name'] ])){
                                                            foreach ($deep_nested_block[ $block['block_name'] ] as $deep_nested_block_name_value => $deep_nested_block_value){
                                                                foreach ($deep_nested_block_value as $deep_nested_block_field_value_index => $deep_nested_block_field_value){
                                                                    $aggregate_index = $deep_nested_block_index . '_' . $deep_nested_block_field_value_index;
                                                                    $nested_values[$aggregate_index][$deep_nested_block_name_value] = $deep_nested_block_field_value;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $nested_values;
    }

    /**
     * @return array
     */
    private static function unsafeFields()
    {
        return [
                MetaFieldModel::HTML_TYPE,
                MetaFieldModel::REPEATER_TYPE,
                MetaFieldModel::BARCODE_TYPE,
                MetaFieldModel::ICON_TYPE
        ];
    }

    /**
     * Get all fields supported and their contexts
     *
     * @return array
     */
    private static function get_fields_by_context()
    {
        return [

            // Basic
                MetaFieldModel::ID_TYPE                => [ self::CONTEXT_TEXT ],
                MetaFieldModel::TEXT_TYPE              => [ self::CONTEXT_TEXT ],
                MetaFieldModel::TEXTAREA_TYPE          => [ self::CONTEXT_TEXT ],
                MetaFieldModel::NUMBER_TYPE            => [ self::CONTEXT_TEXT ],
                MetaFieldModel::RANGE_TYPE             => [ self::CONTEXT_TEXT ],
                MetaFieldModel::PASSWORD_TYPE          => [ self::CONTEXT_TEXT ],
                MetaFieldModel::ICON_TYPE              => [ self::CONTEXT_TEXT ],
                MetaFieldModel::BARCODE_TYPE           => [ self::CONTEXT_TEXT ],
                MetaFieldModel::EMAIL_TYPE             => [ self::CONTEXT_TEXT, self::CONTEXT_LINK ],
                MetaFieldModel::PHONE_TYPE             => [ self::CONTEXT_TEXT, self::CONTEXT_LINK ],
                MetaFieldModel::URL_TYPE               => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_MEDIA ],
                MetaFieldModel::QR_CODE_TYPE           => [ self::CONTEXT_TEXT, self::CONTEXT_LINK ],

            // Content
//			MetaFieldModel::AUDIO_MULTI_TYPE       => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_MEDIA ],
                MetaFieldModel::AUDIO_TYPE             => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_MEDIA ],
                MetaFieldModel::IMAGE_TYPE             => [ self::CONTEXT_TEXT, self::CONTEXT_IMAGE ],
                MetaFieldModel::IMAGE_SLIDER_TYPE      => [ self::CONTEXT_TEXT, self::CONTEXT_IMAGE ],
                MetaFieldModel::GALLERY_TYPE           => [ self::CONTEXT_TEXT, self::CONTEXT_IMAGE ],
                MetaFieldModel::FILE_TYPE              => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_VIDEO, self::CONTEXT_MEDIA ],
                MetaFieldModel::EDITOR_TYPE            => [ self::CONTEXT_TEXT ],
                MetaFieldModel::HTML_TYPE              => [ self::CONTEXT_TEXT ],
                MetaFieldModel::EMBED_TYPE             => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_VIDEO, self::CONTEXT_MEDIA ],
                MetaFieldModel::VIDEO_TYPE             => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_VIDEO, self::CONTEXT_MEDIA ],

            // Specialized fields
                MetaFieldModel::ADDRESS_TYPE           => [ self::CONTEXT_TEXT ],
                MetaFieldModel::ADDRESS_MULTI_TYPE     => [ self::CONTEXT_TEXT ],
                MetaFieldModel::COUNTRY_TYPE           => [ self::CONTEXT_TEXT ],
                MetaFieldModel::WEIGHT_TYPE            => [ self::CONTEXT_TEXT ],
                MetaFieldModel::LENGTH_TYPE            => [ self::CONTEXT_TEXT ],
                MetaFieldModel::DATE_TYPE              => [ self::CONTEXT_TEXT ],
                MetaFieldModel::DATE_TIME_TYPE         => [ self::CONTEXT_TEXT ],
                MetaFieldModel::DATE_RANGE_TYPE        => [ self::CONTEXT_TEXT ],
                MetaFieldModel::TIME_TYPE              => [ self::CONTEXT_TEXT ],
                MetaFieldModel::CURRENCY_TYPE          => [ self::CONTEXT_TEXT ],
                MetaFieldModel::COLOR_TYPE             => [ self::CONTEXT_TEXT ],
                MetaFieldModel::RATING_TYPE            => [ self::CONTEXT_TEXT ],
                MetaFieldModel::TABLE_TYPE             => [ self::CONTEXT_TEXT ],

            // Choice
                MetaFieldModel::SELECT_TYPE            => [ self::CONTEXT_TEXT ],
                MetaFieldModel::SELECT_MULTI_TYPE      => [ self::CONTEXT_TEXT ],
                MetaFieldModel::CHECKBOX_TYPE          => [ self::CONTEXT_TEXT ],
                MetaFieldModel::RADIO_TYPE             => [ self::CONTEXT_TEXT ],
                MetaFieldModel::TOGGLE_TYPE            => [ self::CONTEXT_TEXT ],

            // Loop
                MetaFieldModel::FLEXIBLE_CONTENT_TYPE  => [ self::CONTEXT_LOOP ],
                MetaFieldModel::LIST_TYPE              => [ self::CONTEXT_TEXT, self::CONTEXT_LOOP ],
                MetaFieldModel::REPEATER_TYPE          => [ self::CONTEXT_TEXT, self::CONTEXT_LOOP ],

            // Relational
                MetaFieldModel::POST_TYPE              => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
                MetaFieldModel::POST_OBJECT_TYPE       => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
                MetaFieldModel::POST_OBJECT_MULTI_TYPE => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
                MetaFieldModel::TERM_OBJECT_TYPE       => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
                MetaFieldModel::TERM_OBJECT_MULTI_TYPE => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
                MetaFieldModel::USER_TYPE              => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
                MetaFieldModel::USER_MULTI_TYPE        => [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
        ];
    }

    /**
     * Set the loop query if exists
     * This function is triggered on frontend when a loop tag (like a List, Flexible or Repeater field) is rendered
     *
     * @param array $results
     * @param \Bricks\Query $query
     *
     * @return array
     * @throws \Exception
     */
    public function set_loop_query( $results, $query )
    {
        if ( ! array_key_exists( $query->object_type, $this->loop_tags ) ) {
            return $results;
        }

        $tag_object = $this->loop_tags[ $query->object_type ];
        $looping_query_id = \Bricks\Query::is_any_looping();
        $field = isset($tag_object['field']) ? $tag_object['field'] : null;

        if ( $looping_query_id ) {

            $loop_query_object_type = \Bricks\Query::get_query_object_type( $looping_query_id );

            // Maybe it is a nested repeater
            if ( array_key_exists( $loop_query_object_type, $this->loop_tags ) ) {

                $loop_object = \Bricks\Query::get_loop_object( $looping_query_id );

                if ( is_array( $loop_object ) && array_key_exists( $field['name'], $loop_object ) ) {
                    return $loop_object[ $field['name'] ];
                }

                if ( is_object( $loop_object ) && is_a( $loop_object, 'WP_Post' ) ) {
                    $acpt_object_id = get_the_ID();
                }

                // @TODO term and users?
            }

            // Or maybe it is a post loop
            elseif ( $loop_query_object_type === 'post' ) {
                $acpt_object_id = get_the_ID();
            }

            // Or maybe it is a term loop
            elseif ( $loop_query_object_type === 'term' ) {

                $loop_object = \Bricks\Query::get_loop_object( $looping_query_id );

                if($loop_object instanceof \WP_Term){
                    $acpt_object_id = $loop_object->term_id;
                }
            }
        }

        // Get the $post_id or the template preview ID
        if ( ! isset( $acpt_object_id ) and $field !== null ) {
            $post_id = \Bricks\Database::$page_data['preview_or_post_id'];
            $acpt_object_id = $this->get_object_id( $field, $post_id );
        }

        // Render blocks
        if(isset($tag_object['type']) and $tag_object['type'] === 'Block'){

            $post_id = isset( $loop_query_object_type ) && $loop_query_object_type === 'post' ? get_the_ID() : \Bricks\Database::$page_data['preview_or_post_id'];
            $tag_object['post_id'] = $post_id;

            return $this->get_acpt_block($tag_object);
        }

        // Check if it is a subfield of a group field (Repeater inside of a Group)
        if ( isset( $tag_object['parent']['type'] ) and $tag_object['parent']['type'] === MetaFieldModel::REPEATER_TYPE ) {

            // nested repeaters
            if(
                    isset($tag_object['field']) and
                    isset($tag_object['field']['type']) and
                    $tag_object['field']['type'] === MetaFieldModel::REPEATER_TYPE
            ){
                $results = [];

                if(isset($loop_object[ $tag_object['field']['field_name'] ]) and is_array($loop_object[ $tag_object['field']['field_name'] ])){
                    foreach ($loop_object[ $tag_object['field']['field_name'] ] as $nested_block){

                        // remove unnecessary info
                        foreach ($nested_block as $nested_block_item_index => $nested_block_item){
                            unset($nested_block[$nested_block_item_index]['original_name']);
                            unset($nested_block[$nested_block_item_index]['type']);
                        }

                        $results = array_merge($results, $nested_block);
                    }
                }

                return $results;
            }

            // one-level repeaters
            return isset( $loop_object[ $tag_object['field']['field_name'] ] ) ? $loop_object[ $tag_object['field']['field_name'] ] : [];

        } else {
            // regular fields, check if is the field belongs to a term or a post type
            if(isset($loop_query_object_type) and $loop_query_object_type === 'term'){
                $field['term_id'] = $acpt_object_id;
            } else {
                $field['post_id'] = $acpt_object_id;
            }

            $results = $this->get_acpt_value($field);

            // converting relational fields
            if($field['type'] === MetaFieldModel::POST_TYPE and is_array($results)){
                $res = [];

                if(isset($results['value']) and is_array($results['value'])){
                    foreach ($results['value'] as $r){
                        if($r['type'] === MetaTypes::CUSTOM_POST_TYPE){
                            $res[] = (string)$r['id'];
                        }
                    }
                }

                return $res;
            }
        }

        return ! empty( $results ) ? $results : [];
    }

    /**
     * Calculate the object ID to be used when fetching the field value
     *
     * @param $field
     * @param $post_id
     *
     * @return mixed
     */
    public function get_object_id( $field, $post_id )
    {
        $locations = isset( $field['_bricks_locations'] ) ? $field['_bricks_locations'] : [];

        if ( isset($field['object_type']) and \Bricks\Query::is_looping() ) {
            $object_type = $field['object_type'];
            $loop_type = \Bricks\Query::get_loop_object_type();
            $object_id = \Bricks\Query::get_loop_object_id();

//			// Terms loop
//			if ( $object_type == 'term' && in_array( $object_type, $locations ) ) {
//				$object = \Bricks\Query::get_loop_object();
//
//				return isset( $object->taxonomy ) ? $object->taxonomy . '_' . $object_id : $post_id;
//			}
//
//			// Users loop
//			if ( $object_type == 'user' && in_array( $object_type, $locations ) ) {
//				return 'user_' . $object_id;
//			}

            // loop type is the same as the field object type (term, user, post)
            if ( $loop_type == $object_type ) {
                return $object_id;
            }
        }

        return $post_id;
    }

    /**
     * Manipulate the loop object
     *
     * @param array  $loop_object
     * @param string $loop_key
     * @param Query  $query
     * @return mixed
     */
    public function set_loop_object( $loop_object, $loop_key, $query )
    {
        if(!array_key_exists($query->object_type, $this->loop_tags)){
            return $loop_object;
        }

        if(!isset($this->loop_tags[ $query->object_type ]['field'])){
            return $loop_object;
        }

        $field = $this->loop_tags[ $query->object_type ]['field'];

        if ( in_array( $field['type'], self::RELATIONAL_FIELDS ) ) {

            switch ($field['type']){

                // Relational field
                case MetaFieldModel::POST_TYPE:

                    $fieldSettings = get_acpt_meta_field_object($field['box_name'], $field['field_name']);

                    if(isset($fieldSettings->relations)){
                        $target = $fieldSettings->relations[0]->to->type;

                        // The relation target is a:
                        switch ($target){

                            // post
                            case MetaTypes::CUSTOM_POST_TYPE:
                                global $post;
                                $post = get_post((int)$loop_object);
                                setup_postdata( $post );

                                return $post;

                            // term
                            case MetaTypes::TAXONOMY:
                                return get_term((int)$loop_object);

                            // user
                            case MetaTypes::USER:
                                return get_user((int)$loop_object);
                        }

                        return null;
                    }

                // Posts
                case MetaFieldModel::POST_OBJECT_TYPE:
                case MetaFieldModel::POST_OBJECT_MULTI_TYPE:

                    global $post;
                    $post = get_post((int)$loop_object);
                    setup_postdata( $post );

                    return $post;

                // Terms
                case MetaFieldModel::TERM_OBJECT_TYPE:
                case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
                    return get_term((int)$loop_object);

                // Users
                case MetaFieldModel::USER_TYPE:
                case MetaFieldModel::USER_MULTI_TYPE:
                    return get_user((int)$loop_object);
            }
        }

        return $loop_object;
    }
}
