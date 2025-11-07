<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Constants\MetaTypes;
use ACPT\Core\CQRS\Command\AbstractMetaFieldValueCommand;
use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Utils\PHP\Address;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\WPAttachment;
use ACPT\Utils\Wordpress\WPUtils;

class FetchMetaFieldValueQuery extends AbstractMetaFieldValueCommand implements QueryInterface
{
    /**
     * @return mixed
     * @throws \Exception
     */
    public function execute()
    {
        // Prevent any error if one of those functions is undefined:
        if( !function_exists('get_user_by') ) {
            include_once( ABSPATH . 'wp-includes/pluggable.php' );
        }

        if( !function_exists('get_term') ) {
            include_once( ABSPATH . 'wp-includes/taxonomy.php' );
        }

        $saved_field_type = $this->getData('_type') ?? $this->fieldModel->getType();
        $saved_field_value = $this->getData();

        if(empty($saved_field_value)){
            return null;
        }

        $before = null;
        $after  = null;

        $advanced_options = $this->fieldModel->getAdvancedOptions();

        if(is_array($advanced_options)){
            foreach ($advanced_options as $advanced_option){
                if($advanced_option->getKey() === 'after'){
                    $after = $advanced_option->getValue();
                }

                if($advanced_option->getKey() === 'before'){
                    $before = $advanced_option->getValue();
                }
            }
        }

        switch ($saved_field_type){

            // ADDRESS_TYPE
            case MetaFieldModel::ADDRESS_TYPE:

                $lat = $this->getData('_lat');
                $lng = $this->getData('_lng');
                $city = $this->getData('_city');
                $country = $this->getData('_country');

                return $this->formatRawValue([
                    'address' => $this->formatNestedRawValue($saved_field_value, $after, $before),
                    'city' => $city,
                    'country' => $country,
                    'lat'  => $lat,
                    'lng'  => $lng,
                ], $after, $before);

            // ADDRESS_MULTI_TYPE
            case MetaFieldModel::ADDRESS_MULTI_TYPE:

                $addresses = Address::fetchMulti($saved_field_value);
                $lat = Address::fetchMulti($this->getData('_lat'));
                $lng = Address::fetchMulti($this->getData('_lng'));
                $city = Address::fetchMulti($this->getData('_city'));
                $country = Address::fetchMulti($this->getData('_country'));

                $values = [];

                foreach ($addresses as $index => $address){
                    $values[] = [
                        'address' => $this->formatNestedRawValue($address, $after, $before),
                        'city' => $city[$index] ?? null,
                        'country' => $country[$index] ?? null,
                        'lat'  => $lat[$index] ?? null,
                        'lng'  => $lng[$index] ?? null,
                    ];
                }

                return $this->formatRawValue($values, $after, $before);

            // CURRENCY_TYPE
            case MetaFieldModel::CURRENCY_TYPE:

                $unit = $this->getData('_currency');

                return $this->formatRawValue([
                    'amount' => $this->formatNestedRawValue($saved_field_value, $after, $before),
                    'unit' => $unit
                ], $after, $before);

            // DATE_TYPE
            case MetaFieldModel::DATE_TYPE:

                $defaultFormat = 'Y-m-d';
                $dateFormat = $this->fieldModel->getAdvancedOption('date_format') ?? get_option('date_format');
                $savedFormat =  $this->getData('_format') ?? $defaultFormat;
                $saved_date = \DateTime::createFromFormat($savedFormat, $saved_field_value);

                if(!$saved_date instanceof \DateTime){
                    $saved_date = \DateTime::createFromFormat($dateFormat, $saved_field_value);
                }

                if(!$saved_date instanceof \DateTime){
                    $saved_date = \DateTime::createFromFormat($defaultFormat, $saved_field_value);
                }

                if(!$saved_date instanceof \DateTime){
                    return null;
                }

                $date = Date::format($dateFormat, $saved_date);

                return $this->formatRawValue([
                    'format' => $dateFormat,
                    'object' => $saved_date,
                    'value'  => $this->formatNestedRawValue($date, $after, $before),
                ], $after, $before);

            // DATE_TIME_TYPE
            case MetaFieldModel::DATE_TIME_TYPE:

                $defaultFormat = 'Y-m-d H:i:s';
                $dateFormat = $this->fieldModel->getAdvancedOption('date_format') ?? get_option('date_format');
                $timeFormat = $this->fieldModel->getAdvancedOption('time_format') ?? get_option('time_format');
                $savedFormat = $this->getData('_format') ?? $defaultFormat;
                $saved_datetime = \DateTime::createFromFormat($savedFormat, $saved_field_value);

                if(!$saved_datetime instanceof \DateTime){
                    $saved_datetime = \DateTime::createFromFormat($dateFormat, $saved_field_value);
                }

                if(!$saved_datetime instanceof \DateTime){
                    $saved_datetime = \DateTime::createFromFormat($defaultFormat, $saved_field_value);
                }

                if(!$saved_datetime instanceof \DateTime){
                    return null;
                }

                $datetime = Date::format($dateFormat . ' ' . $timeFormat, $saved_datetime);

                return $this->formatRawValue([
                    'format' => $dateFormat . ' ' . $timeFormat,
                    'object' => $saved_datetime,
                    'value'  => $this->formatNestedRawValue($datetime),
                ], $after, $before);

            // TIME_TYPE
            case MetaFieldModel::TIME_TYPE:

                $defaultFormat = 'H:i:s';
                $timeFormat = $this->fieldModel->getAdvancedOption('time_format') ?? get_option('time_format');
                $savedFormat =  $this->getData('_format') ?? $defaultFormat;
                $saved_time = \DateTime::createFromFormat($savedFormat, $saved_field_value);

                if(!$saved_time instanceof \DateTime){
                    $saved_time = \DateTime::createFromFormat($timeFormat, $saved_field_value);
                }

                if(!$saved_time instanceof \DateTime){
                    $saved_time = \DateTime::createFromFormat($defaultFormat, $saved_field_value);
                }

                if(!$saved_time instanceof \DateTime){
                    return null;
                }

                $time = Date::format($timeFormat, $saved_time);

                return $this->formatRawValue([
                    'format' => $timeFormat,
                    'object' => $saved_time,
                    'value'  => $this->formatNestedRawValue($time),
                ], $after, $before);

            // DATE_RANGE_TYPE
            case MetaFieldModel::DATE_RANGE_TYPE:

                if(!is_string($saved_field_value)){
                    return [];
                }

                $saved_field_value = explode(" - ", $saved_field_value);

                if(count($saved_field_value) !== 2){
                    return [];
                }

                $defaultFormat = 'Y-m-d';
                $dateFormat = $this->fieldModel->getAdvancedOption('date_format') ?? get_option('date_format');
                $savedFormat = $this->getData('_format') ?? $defaultFormat;
                $saved_from = \DateTime::createFromFormat($savedFormat, $saved_field_value[0]);
                $saved_to = \DateTime::createFromFormat($savedFormat, $saved_field_value[1]);

                if(!$saved_from instanceof \DateTime){
                    $saved_from = \DateTime::createFromFormat($dateFormat, $saved_field_value[0]);
                }

                if(!$saved_to instanceof \DateTime){
                    $saved_to = \DateTime::createFromFormat($dateFormat, $saved_field_value[1]);
                }

                if(!$saved_from instanceof \DateTime){
                    $saved_from = \DateTime::createFromFormat($defaultFormat, $saved_field_value[0]);
                }

                if(!$saved_to instanceof \DateTime){
                    $saved_to = \DateTime::createFromFormat($defaultFormat, $saved_field_value[1]);
                }

                if(!$saved_from instanceof \DateTime){
                    return null;
                }

                if(!$saved_to instanceof \DateTime){
                    return null;
                }

                $from = Date::format($dateFormat, $saved_from);
                $to = Date::format($dateFormat, $saved_to);

                return $this->formatRawValue([
                    'format' => $dateFormat,
                    'object' => [
                        $saved_from,
                        $saved_to,
                    ],
                    'value' => [
                        $this->formatNestedRawValue($from, $after, $before),
                        $this->formatNestedRawValue($to, $after, $before),
                    ],
                ], $after, $before);

            // AUDIO_MULTI_TYPE
            // GALLERY_TYPE
            case MetaFieldModel::AUDIO_MULTI_TYPE:
            case MetaFieldModel::IMAGE_SLIDER_TYPE:
            case MetaFieldModel::GALLERY_TYPE:

                $id = $this->getAttachmentId();

                if(!empty($id)){
                    $ids = explode(",", $id);
                    $gallery = [];

                    foreach ($ids as $_id){
                        $wpAttachment = $this->getAttachment($_id, $this->return);
                        $gallery[] = $wpAttachment;
                    }

                    return $this->formatRawValue($gallery, $after, $before);
                }

                if(is_array($saved_field_value)){

                    $gallery = [];

                    foreach ($saved_field_value as $image){
                        $wpAttachment = $this->getAttachment($image, $this->return);
                        $gallery[] = $wpAttachment;
                    }

                    return $this->formatRawValue($gallery, $after, $before);
                }

                $wpAttachment = $this->getAttachment($saved_field_value, $this->return);

                return $this->formatRawValue($wpAttachment, $after, $before);

            // FILE_TYPE
            case MetaFieldModel::FILE_TYPE:

                $label = $this->getData('_label');
                $id = $this->getAttachmentId();

                if(!empty($id)){
                    $wpAttachment = $this->getAttachment($id, $this->return);
                } else {
                    $wpAttachment = $this->getAttachment($saved_field_value, $this->return);
                }

                return $this->formatRawValue([
                    'file' => $wpAttachment,
                    'label' => $this->formatNestedRawValue($label, $after, $before),
                ], $after, $before);

            // AUDIO_TYPE
            // IMAGE_TYPE
            // VIDEO_TYPE
            case MetaFieldModel::AUDIO_TYPE:
            case MetaFieldModel::IMAGE_TYPE:
            case MetaFieldModel::VIDEO_TYPE:

                $id = $this->getAttachmentId();
                if(!empty($id)){
                    return $this->formatRawValue($this->getAttachment($id, $this->return), $after, $before);
                }

                return $this->formatRawValue($this->getAttachment($saved_field_value, $this->return), $after, $before);

            // LENGTH_TYPE
            case MetaFieldModel::LENGTH_TYPE:

                $unit = $this->getData('_length');

                return $this->formatRawValue([
                    'length' => $this->formatNestedRawValue($saved_field_value, $after, $before),
                    'unit' => $unit
                ], $after, $before);

            // EDITOR_TYPE
            case MetaFieldModel::EDITOR_TYPE:
                return ($this->formatRawValue(wpautop($saved_field_value), $after, $before));

            // LIST_TYPE
            case MetaFieldModel::CHECKBOX_TYPE:
            case MetaFieldModel::SELECT_MULTI_TYPE:
            case MetaFieldModel::LIST_TYPE:

                $return = [];
                if(is_array($saved_field_value)){
                    foreach ($saved_field_value as $value){
                        $return[] = $this->formatNestedRawValue($value, $after, $before);
                    }
                }

                return $this->formatRawValue($return, $after, $before);

            // POST_TYPE
            case MetaFieldModel::POST_TYPE:

                if(empty($this->fieldModel->getRelations())){
                    return [];
                }

                $relation = $this->fieldModel->getRelations()[0];
                $values = (is_string($saved_field_value)) ? explode(",", $saved_field_value) : $saved_field_value;
                $return_obj = [];

                switch ($relation->to()->getType()){

                    case MetaTypes::CUSTOM_POST_TYPE:

                        if(is_array($values)){
                            foreach ($values as $postId){
                                if($this->return === 'raw'){
                                    $obj = [
                                        'type' => MetaTypes::CUSTOM_POST_TYPE,
                                        'id' => $postId,
                                    ];
                                } else {
                                    $obj = $this->getPost($postId, $this->return);
                                }

                                $return_obj[] = $obj;
                            }
                        }

                        break;

                    case MetaTypes::TAXONOMY:

                        if(is_array($values)){
                            foreach ($values as $termId){
                                if($this->return === 'raw'){
                                    $obj = [
                                        'type' => MetaTypes::TAXONOMY,
                                        'id' => $termId,
                                    ];
                                } else {
                                    $obj = $this->getTerm($termId, $this->return);
                                }

                                $return_obj[] = $obj;
                            }
                        }

                        break;

                    case MetaTypes::OPTION_PAGE:

                        if(is_array($values)){
                            foreach ($values as $menuSlug){
                                if($this->return === 'raw'){
                                    $obj = [
                                        'type' => MetaTypes::OPTION_PAGE,
                                        'id' => $menuSlug,
                                    ];
                                } else {
                                    $obj = $this->getOptionPage($menuSlug, $this->return);
                                }

                                $return_obj[] = $obj;
                            }
                        }

                        break;

                    case MetaTypes::USER:

                        if(is_array($values)){
                            foreach ($values as $userId){
                                if($this->return === 'raw'){
                                    $obj = [
                                        'type' => MetaTypes::USER,
                                        'id' => $userId,
                                    ];
                                } else {
                                    $obj = $this->getUser($userId, $this->return);
                                }

                                $return_obj[] = $obj;
                            }
                        }

                        break;
                }

                return $this->formatRawValue($return_obj, $after, $before);

            // POST_OBJECT
            case MetaFieldModel::POST_OBJECT_TYPE:
                $value = $this->getPost($saved_field_value, $this->return);

                return $this->formatRawValue($value, $after, $before);

            // POST_OBJECT_MULTI
            case MetaFieldModel::POST_OBJECT_MULTI_TYPE:

                $posts_obj = [];

                if(is_string($saved_field_value)){
                    $saved_field_value = explode(",", $saved_field_value);
                }

                foreach ($saved_field_value as $post_id){
                    $posts_obj[] = $this->getPost($post_id, $this->return);
                }

                return $this->formatRawValue($posts_obj, $after, $before);

            // QR_CODE_TYPE
            case MetaFieldModel::BARCODE_TYPE:

                $rawBarcodeValue = $this->getData('_barcode_value');

                if(!is_string($rawBarcodeValue)){
                    return $saved_field_value;
                }

                if(empty($rawBarcodeValue)){
                    return $saved_field_value;
                }

                if(!Strings::isJson($rawBarcodeValue)){
                    return $saved_field_value;
                }

                $barcodeValue = json_decode($rawBarcodeValue, true);

                if(!isset($barcodeValue['svg'])){
                    return $saved_field_value;
                }

                $barcodeValue['svg'] = html_entity_decode($barcodeValue['svg']);

                if(!isset($barcodeValue['format'])){
                    return $saved_field_value;
                }

                return $this->formatRawValue([
                    'text' => $saved_field_value,
                    'value' => $barcodeValue
                ], $after, $before);


            // QR_CODE_TYPE
            case MetaFieldModel::QR_CODE_TYPE:

                $rawQRCodeValue = $this->getData('_qr_code_value');

                if(!is_string($rawQRCodeValue)){
                    return $saved_field_value;
                }

                if(empty($rawQRCodeValue)){
                    return $saved_field_value;
                }

                if(!Strings::isJson($rawQRCodeValue)){
                    return $saved_field_value;
                }

                $QRCodeValue = json_decode($rawQRCodeValue, true);

                if(!isset($QRCodeValue['img'])){
                    return $saved_field_value;
                }

                if(!isset($QRCodeValue['resolution'])){
                    return $saved_field_value;
                }

                return $this->formatRawValue([
                    'url' => $saved_field_value,
                    'value' => $QRCodeValue
                ], $after, $before);

            // FLEXIBLE TYPE
            case MetaFieldModel::FLEXIBLE_CONTENT_TYPE:
                return $this->getNestedBlockValues($saved_field_value);

            // REPEATER_TYPE
            case MetaFieldModel::REPEATER_TYPE:
                return $this->getRepeaterValues($saved_field_value);

            case MetaFieldModel::PHONE_TYPE:

                $dial = $this->getData('_dial');

                return $this->formatRawValue([
                    'dial' => $dial ?? null,
                    'value' => $saved_field_value,
                ], $after, $before);

            // TERM_OBJECT
            case MetaFieldModel::TERM_OBJECT_TYPE:
                $value = $this->getTerm($saved_field_value, $this->return);

                return $this->formatRawValue($value, $after, $before);

            // TERM_OBJECT_MULTI
            case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:

                $terms_obj = [];

                if(is_string($saved_field_value)){
                    $saved_field_value = explode(",", $saved_field_value);
                }

                foreach ($saved_field_value as $term_id){
                    $terms_obj[] = $this->getTerm($term_id, $this->return);
                }

                return $this->formatRawValue($terms_obj, $after, $before);

            // WEIGHT_TYPE
            case MetaFieldModel::WEIGHT_TYPE:

                $unit = $this->getData('_weight');

                return $this->formatRawValue([
                    'weight' => $this->formatRawValue($saved_field_value, $after, $before),
                    'unit' => $unit
                ], $after, $before);

            // COUNTRY
            case MetaFieldModel::COUNTRY_TYPE:
                $country = $this->getData('_country');

                return $this->formatRawValue([
                    'value' => $this->formatNestedRawValue($saved_field_value, $after, $before),
                    'country' => $country
                ], $after, $before);

            // URL
            case MetaFieldModel::URL_TYPE:
                $label = $this->getData('_label') ?? $saved_field_value;

                return $this->formatRawValue([
                    'after' => $after,
                    'before' => $before,
                    'url' => $saved_field_value,
                    'label' => $this->formatNestedRawValue($label, $after, $before),
                ], $after, $before);

            // USER
            case MetaFieldModel::USER_TYPE:
                $value = $this->getUser($saved_field_value, $this->return);

                return $this->formatRawValue($value, $after, $before);

            // USER_MULTI
            case MetaFieldModel::USER_MULTI_TYPE:

                $users_obj = [];

                if(is_string($saved_field_value)){
                    $saved_field_value = explode(",", $saved_field_value);
                }

                foreach ($saved_field_value as $user_id){
                    $users_obj[] = $this->getUser($user_id, $this->return);
                }

                return $this->formatRawValue($users_obj, $after, $before);

            // DEFAULT VALUE
            default:

                $allowHtml = $this->fieldModel->getAdvancedOption("allow_html");

                if($allowHtml == 1){
                    $saved_field_value = html_entity_decode($saved_field_value);
                }

                return $this->formatRawValue(WPUtils::renderShortCode($saved_field_value), $after, $before);
        }
    }

    /**
     * @return string|null
     */
    private function getAttachmentId()
    {
        // legacy format
        if(!empty($this->getData('_id')) and is_numeric($this->getData('_id'))){
            return $this->getData('_id');
        }

        // current format
        return $this->getData('_attachment_id') ?? null;
    }

    /**
     * @param $before
     * @param $value
     * @param $after
     *
     * @return array|string
     */
    private function formatRawValue($value, $after = null, $before = null)
    {
        if($this->withContext){
            return [
                'before' => $before,
                'value' => $value,
                'after' => $after,
            ];
        }

        if(is_string($value)){
            $value = $before.$value.$after;

            if(is_int($value)){
                return (int)$value;
            }

            if(is_float($value)){
                return (floatval($value));
            }

            return $value;
        }

        return $value;
    }

    /**
     * @param      $value
     * @param null $after
     * @param null $before
     *
     * @return string
     */
    private function formatNestedRawValue($value, $after = null, $before = null)
    {
        if($this->withContext){
            return $value;
        }

        if(is_string($value)){
            return $before.$value.$after;
        }

        return $value;
    }

    /**
     * @param $saved_field_value
     *
     * @return array|bool
     */
    private function getNestedBlockValues($saved_field_value)
    {
        if(!is_array($saved_field_value) or !isset($saved_field_value['blocks'])) {
            return false;
        }

        $values = [];

        foreach ($saved_field_value['blocks'] as $block_index => $block){

            if(!is_array($block)) {
                return false;
            }

            $i = 0;
            foreach ($block as $block_name => $block_fields){
                foreach ($block_fields as $block_field_name => $block_field){
                    foreach ($block_field as $block_field_index => $block_field_value){
                        if(isset($block_field_value['blocks'])){
                            // nested blocks in block
                            $nested_block_name = @array_keys($block_field_value['blocks'][0])[0];
                            $values['blocks'][$block_index][$block_name][$i]['blocks'][$block_field_index][$nested_block_name][] = $this->getNestedBlockValues($block_field_value);
                        } elseif(!isset($block_field_value['value'])){
                            // nested repeaters in block
                            $values['blocks'][$block_index][$block_name][$block_field_name][$block_field_index] = $this->getRepeaterValues($block_field_value);
                        } else {
                            $values['blocks'][$block_index][$block_name][$block_field_value['original_name']][$block_field_index] = $this->getNestedRepeaterFieldValue($block_field_value);
                        }
                    }
                }

                $i++;
            }
        }

        return $values;
    }

    /**
     * @param $saved_field_value
     *
     * @return array|bool
     */
    private function getRepeaterValues($saved_field_value)
    {
        if(!is_array($saved_field_value)) {
            return false;
        }

        $values = [];

        // this is needed for values nested inside a nested repeater
        unset($saved_field_value['original_name']);
        unset($saved_field_value['type']);

        $keys = array_keys($saved_field_value);

        if(empty($keys)){
            return $values;
        }

        $firstKey = $keys[0];

        if(isset($saved_field_value[$firstKey]) and is_array($saved_field_value[$firstKey])){
            $firstElement = $saved_field_value[$firstKey];

            for ($i=0; $i<count($firstElement); $i++){
                $element = [];

                foreach (array_keys($saved_field_value) as $index => $key){

                    $nestedBefore = null;
                    $nestedAfter = null;
                    $nestedFieldModal = $this->fieldModel->getChild($key);

                    if($nestedFieldModal !== null and $nestedFieldModal->canHaveAfterAndBefore()){
                        $nestedBefore = $nestedFieldModal->getAdvancedOption("before");
                        $nestedAfter = $nestedFieldModal->getAdvancedOption("after");
                    }

                    if(isset($saved_field_value[$key]) and isset($saved_field_value[$key][$i])){
                        $rawData = $saved_field_value[$key][$i];

                        if(isset($rawData['blocks'])){
                            // block nested in repeater
                            $element[$key][] = $this->getNestedBlockValues($rawData);
                        } elseif(!isset($rawData['value'])){
                            // repeater nested in repeater
                            $element[$key][] = $this->getRepeaterValues($rawData);
                        } else {
                            if(isset($rawData['original_name'])){
                                $element[$rawData['original_name']] = $this->getNestedRepeaterFieldValue($rawData);
                            }
                        }
                    }
                }

                $values[] = $element;
            }
        }

        return $values;
    }

    /**
     * @param array $rawData
     *
     * @return WPAttachment|array|mixed|null
     */
    private function getNestedRepeaterFieldValue(array $rawData = [])
    {
        if(!isset($rawData['type'])){
            return null;
        }

        if(!isset($rawData['original_name'])){
            return null;
        }

        $type = is_array($rawData['type']) ? $rawData['type'][0] : $rawData['type'];
        $value = $rawData['value'];
        $id = (isset($rawData['id'])) ? $rawData['id'] : null;

        $before = null;
        $after = null;
        $nestedFieldModal = $this->fieldModel->getChild($rawData['original_name']);

        if($nestedFieldModal !== null and $nestedFieldModal->canHaveAfterAndBefore()){
            $before = $nestedFieldModal->getAdvancedOption("before");
            $after = $nestedFieldModal->getAdvancedOption("after");
        }

        switch ($type){

            // ADDRESS_TYPE
            case MetaFieldModel::ADDRESS_TYPE:
                return $this->formatRawValue([
                    'address' => $this->formatNestedRawValue($value, $after, $before),
                    'lat'     => $rawData['lat'] ?? null,
                    'lng'     => $rawData['lng'] ?? null,
                    'city'    => $rawData['city'] ?? null,
                    'country' => $rawData['country'] ?? null,
                ], $after, $before);

            // ADDRESS_MULTI_TYPE
            case MetaFieldModel::ADDRESS_MULTI_TYPE:

                $addresses = Address::fetchMulti($value);
                $lat = Address::fetchMulti($rawData['lat']);
                $lng = Address::fetchMulti($rawData['lng']);
                $city = Address::fetchMulti($rawData['city']);
                $country = Address::fetchMulti($rawData['country']);
                $values = [];

                foreach ($addresses as $index => $address){
                    $values[] = [
                        'address' => $this->formatNestedRawValue($address, $after, $before),
                        'lat'     => $lat[$index] ?? null,
                        'lng'     => $lng[$index] ?? null,
                        'city'    => $city[$index] ?? null,
                        'country' => $country[$index] ?? null,
                    ];
                }

                return $this->formatRawValue($values, $after, $before);

            // CURRENCY_TYPE
            case MetaFieldModel::CURRENCY_TYPE:
                return $this->formatRawValue([
                    'amount' => $this->formatNestedRawValue($value, $after, $before),
                    'unit' => $rawData['currency'] ?? null,
                ], $after, $before);

            // GALLERY_TYPE
            case MetaFieldModel::GALLERY_TYPE:

                if(!empty($id)){
                    $ids = explode(',', $id);

                    $gallery = [];

                    foreach ($ids as $_id){
                        $wpAttachment = $this->getAttachment($_id, $this->return);
                        if($wpAttachment !== null){
                            $gallery[] = $wpAttachment;
                        }
                    }

                    return $this->formatRawValue($gallery, $after, $before);
                }

                if(is_array($value)){
                    $gallery = [];

                    foreach ($value as $image){
                        $wpAttachment = $this->getAttachment($image, $this->return);
                        if($wpAttachment !== null){
                            $gallery[] = $wpAttachment;
                        }
                    }

                    return $this->formatRawValue($gallery, $after, $before);
                }

                $wpAttachment = $this->getAttachment($value, $this->return);

                if(empty($wpAttachment)){
                    return null;
                }

                return $this->formatRawValue($wpAttachment, $after, $before);

            // FILE_TYPE
            case MetaFieldModel::FILE_TYPE:

                $label = (isset($rawData['label']) and !empty($rawData['label'])) ? $rawData['label'] : null;

                if(!empty($id)){
                    $wpAttachment = $this->getAttachment($id, $this->return);
                } else {
                    $wpAttachment = $this->getAttachment($value, $this->return);
                }

                return $this->formatRawValue([
                    'file' => $wpAttachment,
                    'label' => $this->formatNestedRawValue($label, $after, $before)
                ], $after, $before);

            // IMAGE_TYPE
            // VIDEO_TYPE
            case MetaFieldModel::IMAGE_TYPE:
            case MetaFieldModel::VIDEO_TYPE:

                if(!empty($id)){
                    return $this->getAttachment($id, $this->return);
                }

                $wpAttachment = $this->getAttachment($value, $this->return);

                if(empty($wpAttachment)){
                    return null;
                }

                return $this->formatRawValue($wpAttachment, $after, $before);

            // DATE_RANGE_TYPE
            case MetaFieldModel::DATE_RANGE_TYPE:

                if(!is_string($value)){
                    return [];
                }

                $saved_field_value = explode(" - ", $value);

                if(count($saved_field_value) !== 2){
                    return [];
                }

                $defaultFormat = 'Y-m-d';
                $dateFormat = $this->fieldModel->getAdvancedOption('date_format') ?? get_option('date_format');
                $savedFormat = $this->getData('_format') ?? $defaultFormat;
                $saved_from = \DateTime::createFromFormat($savedFormat, $saved_field_value[0]);
                $saved_to = \DateTime::createFromFormat($savedFormat, $saved_field_value[1]);

                if(!$saved_from instanceof \DateTime){
                    $saved_from = \DateTime::createFromFormat($dateFormat, $saved_field_value[0]);
                }

                if(!$saved_to instanceof \DateTime){
                    $saved_to = \DateTime::createFromFormat($dateFormat, $saved_field_value[1]);
                }

                if(!$saved_from instanceof \DateTime){
                    $saved_from = \DateTime::createFromFormat($defaultFormat, $saved_field_value[0]);
                }

                if(!$saved_to instanceof \DateTime){
                    $saved_to = \DateTime::createFromFormat($defaultFormat, $saved_field_value[1]);
                }

                if(!$saved_from instanceof \DateTime){
                    return null;
                }

                if(!$saved_to instanceof \DateTime){
                    return null;
                }

                $from = Date::format($dateFormat, $saved_from);
                $to = Date::format($dateFormat, $saved_to);

                return $this->formatRawValue([
                        'format' => $dateFormat,
                        'object' => [
                                $saved_from,
                                $saved_to,
                        ],
                        'value' => [
                                $this->formatNestedRawValue($from, $after, $before),
                                $this->formatNestedRawValue($to, $after, $before),
                        ],
                ], $after, $before);

            // DATE_TIME_TYPE
            case MetaFieldModel::DATE_TIME_TYPE:

                $defaultFormat = 'Y-m-d H:i:s';
                $dateFormat = $this->fieldModel->getAdvancedOption('date_format') ?? get_option('date_format');
                $timeFormat = $this->fieldModel->getAdvancedOption('time_format') ?? get_option('time_format');
                $savedFormat = $this->getData('_format') ?? $defaultFormat;
                $saved_datetime = \DateTime::createFromFormat($savedFormat, $value);

                if(!$saved_datetime instanceof \DateTime){
                    $saved_datetime = \DateTime::createFromFormat($dateFormat, $value);
                }

                if(!$saved_datetime instanceof \DateTime){
                    $saved_datetime = \DateTime::createFromFormat($defaultFormat, $value);
                }

                if(!$saved_datetime instanceof \DateTime){
                    return null;
                }

                $datetime = Date::format($dateFormat . ' ' . $timeFormat, $saved_datetime);

                return $this->formatRawValue([
                        'format' => $dateFormat . ' ' . $timeFormat,
                        'object' => $saved_datetime,
                        'value'  => $this->formatNestedRawValue($datetime),
                ], $after, $before);

            // TIME_TYPE
            case MetaFieldModel::TIME_TYPE:

                $defaultFormat = 'H:i:s';
                $timeFormat = $this->fieldModel->getAdvancedOption('time_format') ?? get_option('time_format');
                $savedFormat =  $this->getData('_format') ?? $defaultFormat;
                $saved_time = \DateTime::createFromFormat($savedFormat, $value);

                if(!$saved_time instanceof \DateTime){
                    $saved_time = \DateTime::createFromFormat($timeFormat, $value);
                }

                if(!$saved_time instanceof \DateTime){
                    $saved_time = \DateTime::createFromFormat($defaultFormat, $value);
                }

                if(!$saved_time instanceof \DateTime){
                    return null;
                }

                $time = Date::format($timeFormat, $saved_time);

                return $this->formatRawValue([
                        'format' => $timeFormat,
                        'object' => $saved_time,
                        'value'  => $this->formatNestedRawValue($time),
                ], $after, $before);

            // DATE_TYPE
            case MetaFieldModel::DATE_TYPE:

                $dateFormat = $this->fieldModel->getAdvancedOption('date_format') ?? get_option('date_format');
                $savedFormat =  $rawData['format'] ?? 'Y-m-d';
                $saved_date = \DateTime::createFromFormat($savedFormat, $value);

                if(!$saved_date instanceof \DateTime){
                    $saved_date = \DateTime::createFromFormat($dateFormat, $value);
                }

                if(!$saved_date instanceof \DateTime){
                    $saved_date = \DateTime::createFromFormat("Y-m-d", $value);
                }

                if(!$saved_date instanceof \DateTime){
                    return null;
                }

                $date = Date::format($dateFormat, $saved_date);

                return $this->formatRawValue([
                        'format' => $savedFormat,
                        'object' => $saved_date,
                        'value'  => $this->formatNestedRawValue($date, $after, $before),
                ], $after, $before);

            // LENGTH_TYPE
            case MetaFieldModel::LENGTH_TYPE:
                return $this->formatRawValue([
                    'length' => $this->formatNestedRawValue($value, $after, $before),
                    'unit' => $rawData['length'] ?? null,
                ], $after, $before);

            // QR_CODE_TYPE
            case MetaFieldModel::QR_CODE_TYPE:

                if(!isset($rawData['qr_code_value'])){
                    return $rawData['value'];
                }

                $rawQRCodeValue = $rawData['qr_code_value'];

                if(!is_string($rawQRCodeValue)){
                    return $rawData['value'];
                }

                if(empty($rawQRCodeValue)){
                    return $rawData['value'];
                }

                if(!Strings::isJson($rawQRCodeValue)){
                    return $rawData['value'];
                }

                $QRCodeValue = json_decode($rawQRCodeValue, true);

                if(!isset($QRCodeValue['img'])){
                    return $rawData['value'];
                }

                if(!isset($QRCodeValue['resolution'])){
                    return $rawData['value'];
                }

                return $this->formatRawValue([
                    'url'   => $rawData['value'],
                    'value' => $this->formatNestedRawValue($QRCodeValue, $after, $before),
                ], $after, $before);

            // WEIGHT_TYPE
            case MetaFieldModel::WEIGHT_TYPE:
                return $this->formatRawValue([
                    'weight' => $this->formatNestedRawValue($value, $after, $before),
                    'unit' => $rawData['weight'],
                ], $after, $before);

            // TABLE_TYPE
            case MetaFieldModel::TABLE_TYPE:
                $generator = new TableFieldGenerator($value);

                return $this->formatRawValue($generator->generate(), $after, $before);

            // PHONE_TYPE
            case MetaFieldModel::PHONE_TYPE:
                return $this->formatRawValue([
                    'dial' => $rawData['dial'] ?? null,
                    'value' => $this->formatNestedRawValue($value, $after, $before),
                ], $after, $before);

            // URL_TYPE
            case MetaFieldModel::URL_TYPE:
                $label = (isset($rawData['label']) and !empty($rawData['label'])) ? $rawData['label'] : $value;

                return $this->formatRawValue([
                    'after' => $after,
                    'before' => $before,
                    'url' => $value,
                    'label' => $this->formatNestedRawValue($label, $after, $before),
                ], $after, $before);

            default:
                return $this->formatRawValue($value, $after, $before);
        }
    }

    /**
     * @param $postId
     * @param string $return
     * @return int|\WP_Post|null
     */
    private function getPost($postId, $return = 'object')
    {
        if($return === 'raw'){
            return (int)$postId;
        }

        return get_post($postId);
    }

    /**
     * @param $termId
     * @param string $return
     * @return int|\WP_Term|null
     */
    private function getTerm($termId, $return = 'object')
    {
        if($return === 'raw'){
            return (int)$termId;
        }

        return get_term($termId);
    }

    /**
     * @param $userId
     * @param string $return
     * @return int|\WP_User
     */
    private function getUser($userId, $return = 'object')
    {
        if($return === 'raw'){
            return (int)$userId;
        }

        return get_user_by('id', $userId);
    }

    /**
     * @param $optionPage
     * @param string $return
     * @return OptionPageModel|string|null
     */
    private function getOptionPage($optionPage, $return = 'object')
    {
        if($return === 'raw'){
            return (string)$optionPage;
        }

        try {
            return OptionPageRepository::getByMenuSlug($optionPage);
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return null;
        }
    }

    /**
     * @param $id
     * @param string $return
     * @return WPAttachment|int|null
     */
    private function getAttachment($id, $return = 'object')
    {
        // if $id is the media ID
        if(is_numeric($id)){

            if($return === 'raw'){
                return (int)$id;
            }

            $wpAttachment = WPAttachment::fromId($id);

            if($wpAttachment->isEmpty()){
                return null;
            }

            return $wpAttachment;
        }

        // if $id is the media URL
        $wpAttachment = WPAttachment::fromUrl($id);

        if($wpAttachment->isEmpty()){
            return null;
        }

        if($return === 'raw'){
            return (int)$wpAttachment->getId();
        }

        return $wpAttachment;
    }
}