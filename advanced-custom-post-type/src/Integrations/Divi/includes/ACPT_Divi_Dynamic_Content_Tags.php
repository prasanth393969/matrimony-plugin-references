<?php

use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\WPAttachment;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionBase;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentOptionInterface;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContentUtils;

class ACPT_Divi_Dynamic_Content_Tags extends DynamicContentOptionBase implements DynamicContentOptionInterface
{
    const STRINGS_DIVIDER = ":::::";

    /**
     * @inheritDoc
     */
    public function get_name(): string
    {
        return 'acpt_dynamic_data';
    }

    /**
     * @inheritDoc
     */
    public function get_label(): string
    {
        return esc_html__( 'ACPT dynamic data', 'et_builder' );
    }

    /**
     * @inheritDoc
     */
    public function register_option_callback( array $options, int $post_id, string $context ): array
    {
        $fields = array_merge(
            [
                'before'   => [
                    'label'   => esc_html__( 'Before', 'et_builder' ),
                    'type'    => 'text',
                    'default' => '',
                ],
                'after'    => [
                    'label'   => esc_html__( 'After', 'et_builder' ),
                    'type'    => 'text',
                    'default' => '',
                ],
                'meta_key' => [
                    'label'   => esc_html__( 'Select the field', 'et_builder' ),
                    'type'    => 'select',
                    'options' => $this->get_fields(),
                    'default' => null,
                ],
                'display_url' => [
                    'label'   => esc_html__( 'Display as (only for URL fields)', 'et_builder' ),
                    'type'    => 'select',
                    'options' => [
                        'url' => 'URL',
                        'label' => 'Label',
                    ],
                    'default' => null,
                ],
            ],
            DynamicContentUtils::get_date_format_fields()
        );

        if ( current_user_can( 'unfiltered_html' ) ) {
            $fields['enable_html'] = [
                'label'   => esc_html__( 'Enable Raw HTML', 'et_builder' ),
                'type'    => 'yes_no_button',
                'options' => [
                    'on'  => et_builder_i18n( 'Yes' ),
                    'off' => et_builder_i18n( 'No' ),
                ],
                'default' => 'off',
                'show_on' => 'text',
            ];
        }

        $options[$this->get_name()] = [
            'id' => $this->get_name(),
            'label' => $this->get_label(),
            'type' => 'any', // text, any, url, image
            'custom' => false,
            'group'  => esc_html__( 'Default', 'et_builder' ),
            'fields' => $fields
        ];

        return $options;
    }

    /**
     * @return array
     */
    private function get_fields()
    {
        $field_options = [];

       try {
           $fieldGroups = MetaRepository::get([
               'clonedFields' => true
           ]);

           foreach ($fieldGroups as $fieldGroup){
               if(count($fieldGroup->getBelongs()) > 0){
                   foreach ($fieldGroup->getBelongs() as $belong){
                       foreach ($fieldGroup->getBoxes() as $boxModel){
                           foreach ($boxModel->getFields() as $fieldModel){
                               if(in_array($fieldModel->getType(), ACPT_Divi_Helper::allowedFields())){

                                   $belongs_to = $belong->getBelongsTo();
                                   $find = $belong->getFindAsSting();

                                   $clonedField = clone $fieldModel;

                                   $clonedField->setFindLabel($find);
                                   $clonedField->setBelongsToLabel($belongs_to);

                                   $key = $clonedField->getId() . self::STRINGS_DIVIDER . $clonedField->getBelongsToLabel() . self::STRINGS_DIVIDER . $clonedField->getFindLabel();
                                   $field_options[$key] = [
                                       'label' => '['.$clonedField->getFindLabel().'] ' . $clonedField->getUiName(),
                                       'box' => $boxModel->getName(),
                                       'field' => $clonedField->getName()
                                   ];
                               }
                           }
                       }
                   }
               }
           }

           return $field_options;
       } catch (\Exception $exception){

           do_action("acpt/error", $exception);

           return [];
       }
    }

    /**
     * @inheritDoc
     */
    public function render_callback( $value, array $args = [] ): string
    {
        try {
            $name               = $args['name'] ?? '';
            $settings           = $args['settings'] ?? [];
            $post_id            = $args['post_id'] ?? null;
            $overrides          = $args['overrides'] ?? [];
            $date_format        = $args['date_format'] ?? null;
            $custom_date_format = $settings['custom_date_format'] ?? null;
            $display_url        = $settings['display_url'] ?? 'url';

            if(!empty($args['loop_object']) and $args['loop_object'] instanceof \WP_Post){
                $post_id = $args['loop_object']->ID;
            }

            if ( $this->get_name() !== $name ) {
                return $value;
            }

            $before = $settings['before'] ?? null;
            $after = $settings['after'] ?? null;
            $meta_key = $settings['meta_key'] ?? null;

            $meta_key_array = explode(self::STRINGS_DIVIDER, $meta_key);

            if(count($meta_key_array) !== 3){
                return $value;
            }

            $metaFieldId = $meta_key_array[0];
            $belongsTo   = $meta_key_array[1];
            $find        = $meta_key_array[2];

            $metaField   = MetaRepository::getMetaFieldById($metaFieldId);

            if($metaField === null){
                return $value;
            }

            switch ($belongsTo){

                default:
                case MetaTypes::CUSTOM_POST_TYPE:
                    $rawValue = get_acpt_field([
                        'post_id' => (int)$post_id,
                        'box_name' => $metaField->getBox()->getName(),
                        'field_name' => $metaField->getName(),
                        'with_context' => true,
                    ]);
                    break;

                case MetaTypes::OPTION_PAGE:
                    $rawValue = get_acpt_field([
                        'option_page' => $find,
                        'box_name' => $metaField->getBox()->getName(),
                        'field_name' => $metaField->getName(),
                        'with_context' => true,
                    ]);
                    break;
            }

            if(empty($rawValue)){
                return $value;
            }

            $val = $rawValue['value'];
            $bef = $rawValue['before'] ?? null;
            $aft = $rawValue['after'] ?? null;

            switch ($metaField->getType()){

                // CHECKBOX_TYPE
                // LIST_TYPE
                // SELECT_MULTI_TYPE
                case MetaFieldModel::CHECKBOX_TYPE:
                case MetaFieldModel::LIST_TYPE:
                case MetaFieldModel::SELECT_MULTI_TYPE:

                    if(is_array($val)){
                        array_walk($val, function(&$value, $key) use ($aft, $after, $bef, $before) {
                            $value = $after . $aft . $value . $bef . $before;
                        } );

                        return implode(", ",  $val);
                    }

                    return '';

                // ADDRESS_TYPE
                case MetaFieldModel::ADDRESS_TYPE:

                    if(
                        is_array($val) and
                        isset($val['address'])
                    ){
                        return $before . $bef . $val['address'] . $aft . $after;
                    }

                    return '';

                // DATE_RANGE_TYPE
                case MetaFieldModel::DATE_RANGE_TYPE:

                    if(!isset($val['value'])){
                        return '';
                    }

                    if(!isset($val['object'])){
                        return '';
                    }

                    $dateTimeObjects = $val['object'];
                    $values = $val['value'];
                    $saved_format = $val['format'] ?? "Y-m-d";

                    if(is_array($values) and !empty($values) and count($values) === 2){

                        $format = $saved_format;
                        $from = $values[0];
                        $to = $values[1];

                        if(Date::isDateFormatValid($date_format)){
                            $format = $date_format;
                        }

                        if(Date::isDateFormatValid($custom_date_format)){
                            $format = $custom_date_format;
                        }

                        if($format !== null and $dateTimeObjects[0] instanceof \DateTime and $dateTimeObjects[1] instanceof \DateTime){
                            $from = Date::format($format, $dateTimeObjects[0]);
                            $to = Date::format($format, $dateTimeObjects[1]);
                        }

                        $value  = $before;
                        $value  .= $bef;
                        $value .= $from;
                        $value .= ' - ';
                        $value .= $to;
                        $value .= $aft;
                        $value .= $after;

                        return $value;
                    }

                    break;

                // TIME_TYPE
                // DATE_TYPE
                case MetaFieldModel::TIME_TYPE:
                case MetaFieldModel::DATE_TYPE:

                    if(!isset($val['value'])){
                        return '';
                    }

                    if(!isset($val['object'])){
                        return '';
                    }

                    /** @var \DateTime $dateTimeObject */
                    $dateTimeObject = $val['object'];
                    $value = $val['value'];
                    $saved_format = ($metaField->getType() === MetaFieldModel::DATE_TYPE) ? "Y-m-d" : "H:i:s";
                    $saved_format = $val['format'] ? $val['format'] : $saved_format;

                    if(!is_string($value)){
                        return '';
                    }

                    if($value !== null and is_string($value) and $value !== ''){

                        $format = $saved_format;

                        if(Date::isDateFormatValid($date_format)){
                            $format = $date_format;
                        }

                        if(Date::isDateFormatValid($custom_date_format)){
                            $format = $custom_date_format;
                        }

                        return $before . $bef . Date::format($format, $dateTimeObject) . $aft . $after;
                    }

                    return '';

                // DATE_TIME_TYPE
                case MetaFieldModel::DATE_TIME_TYPE:

                    if(!isset($val['value'])){
                        return null;
                    }

                    if(!isset($val['object'])){
                        return null;
                    }

                    /** @var \DateTime $dateTimeObject */
                    $dateTimeObject = $val['object'];
                    $value = $val['value'];
                    $saved_format = $val['format'] ?? "Y-m-d H:i:s";

                    if(!is_string($value)){
                        return '';
                    }

                    if($value !== null and is_string($value) and $value !== ''){

                        $format = $saved_format;

                        if(Date::isDateFormatValid($date_format)){
                            $format = $date_format;
                        }

                        if(Date::isDateFormatValid($custom_date_format)){
                            $format = $custom_date_format;
                        }

                        return $before . $bef . Date::format($format, $dateTimeObject) . $aft . $after;
                    }

                    return '';

                // BARCODE_TYPE
                case MetaFieldModel::BARCODE_TYPE:

                    if(!is_array($val)){
                        return '';
                    }

                    if(!isset($val['text'])){
                        return '';
                    }

                    if(!isset($val['value'])){
                        return '';
                    }

                    $value = $val['value'];

                    return $before . $bef . $value . $aft . $after;

                // QR_CODE_TYPE
                case MetaFieldModel::QR_CODE_TYPE:

                    if(!is_array($val)){
                        return '';
                    }

                    if(!isset($val['url'])){
                        return '';
                    }

                    if(!isset($val['value'])){
                        return '';
                    }

                    if(!isset($val['value']['img'])){
                        return '';
                    }

                    $value = $val['url'];

                    return $before . $bef . $value . $aft . $after;

                // RATING_TYPE
                case MetaFieldModel::RATING_TYPE:

                    if(!empty($val) and is_numeric($val)){
                        return $before . $bef . ($val/2) . "/5" . $aft . $after;
                    }

                    return '';

                // CURRENCY_TYPE
                case MetaFieldModel::CURRENCY_TYPE:

                    if(
                        is_array($val) and
                        isset($val['amount']) and
                        isset($val['unit'])
                    ){
                        return $before . $bef . $val['amount'] . $val['unit'] . $aft . $after;
                    }

                    return '';

                // LENGTH_TYPE
                case MetaFieldModel::LENGTH_TYPE:

                    if(
                            is_array($val) and
                            isset($val['length']) and
                            isset($val['unit'])
                    ){
                        return $before . $bef . $val['length'] . $val['unit'] . $aft . $after;
                    }

                    return '';

                // WEIGHT_TYPE
                case MetaFieldModel::WEIGHT_TYPE:

                    if(
                        is_array($val) and
                        isset($val['weight']) and
                        isset($val['unit'])
                    ){
                        return $before . $bef . $val['weight'] . $val['unit'] . $aft . $after;
                    }

                    return '';

                case MetaFieldModel::TABLE_TYPE:

                    if(is_string($val) and Strings::isJson($val)){
                        $generator = new TableFieldGenerator($val);
                        $value = $generator->generate();

                        return $before . $bef . $value . $aft . $after;
                    }

                    return '';

                // URL_TYPE
                case MetaFieldModel::URL_TYPE:

                    if(
                        is_array($val) and
                        isset($val['url'])
                    ){
                        if($display_url === "label" and isset($val['label'])){
                            return $before . $bef . $val['label'] . $aft . $after;
                        }
                        
                        return $before . $bef . $val['url'] . $aft . $after;
                    }

                    return '';

                // FILE_TYPE
                case MetaFieldModel::FILE_TYPE:

                    if(
                        is_array($val) and
                        isset($val['file']) and
                        $val['file'] instanceof WPAttachment
                    ){
                        return $val['file'] ->getSrc();
                    }

                    return '';

                // IMAGE_TYPE
                // VIDEO_TYPE
                case MetaFieldModel::IMAGE_TYPE:
                case MetaFieldModel::VIDEO_TYPE:

                    if($val instanceof WPAttachment){
                        return $val->getSrc();
                    }

                    return '';

                default:

                    if(is_scalar($val)){
                        return $before . $bef . $val . $aft . $after;
                    }

                    return '';
            }

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return '';
        }
    }
}