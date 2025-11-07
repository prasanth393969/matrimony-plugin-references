<?php

namespace ACPT\Integrations\SlimSeo\Provider;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Integrations\SlimSeo\Provider\Fields\Address;
use ACPT\Integrations\SlimSeo\Provider\Fields\AddressMulti;
use ACPT\Integrations\SlimSeo\Provider\Fields\ArrayValues;
use ACPT\Integrations\SlimSeo\Provider\Fields\Base;
use ACPT\Integrations\SlimSeo\Provider\Fields\Currency;
use ACPT\Integrations\SlimSeo\Provider\Fields\Date;
use ACPT\Integrations\SlimSeo\Provider\Fields\DateRange;
use ACPT\Integrations\SlimSeo\Provider\Fields\DateTime;
use ACPT\Integrations\SlimSeo\Provider\Fields\Length;
use ACPT\Integrations\SlimSeo\Provider\Fields\Phone;
use ACPT\Integrations\SlimSeo\Provider\Fields\Post;
use ACPT\Integrations\SlimSeo\Provider\Fields\PostMulti;
use ACPT\Integrations\SlimSeo\Provider\Fields\Relationship;
use ACPT\Integrations\SlimSeo\Provider\Fields\Term;
use ACPT\Integrations\SlimSeo\Provider\Fields\TermMulti;
use ACPT\Integrations\SlimSeo\Provider\Fields\Time;
use ACPT\Integrations\SlimSeo\Provider\Fields\Url;
use ACPT\Integrations\SlimSeo\Provider\Fields\User;
use ACPT\Integrations\SlimSeo\Provider\Fields\UserMulti;
use ACPT\Integrations\SlimSeo\Provider\Fields\Weight;

class FieldRenderer
{
    /**
     * @var \stdClass
     */
    private $fieldObject;

    /**
     * Renderer constructor.
     * @param $fieldObject
     */
    public function __construct($fieldObject)
    {
        $this->fieldObject = $fieldObject;
    }

    /**
     * @return string
     */
    public function render()
    {
        if(!isset($this->fieldObject->type)){
            return null;
        }

        if(!isset($this->fieldObject->value)){
            return null;
        }

        if(!is_array($this->fieldObject->value)){
            return null;
        }

        $type = $this->fieldObject->type;

        if($type === MetaFieldModel::REPEATER_TYPE or $type === MetaFieldModel::FLEXIBLE_CONTENT_TYPE){
            return null;
        }

        $value = $this->fieldObject->value['value'];
        $before = $this->fieldObject->value['before'];
        $after = $this->fieldObject->value['after'];

        switch ($type){

            // ADDRESS_TYPE
            case MetaFieldModel::ADDRESS_TYPE:
                $field = new Address($value, $before, $after);
                break;

            // ADDRESS_MULTI_TYPE
            case MetaFieldModel::ADDRESS_MULTI_TYPE:
                $field = new AddressMulti($value, $before, $after);
                break;

            // CURRENCY_TYPE
            case MetaFieldModel::CURRENCY_TYPE:
                $field = new Currency($value, $before, $after);
                break;

            // DATE_TYPE
            case MetaFieldModel::DATE_TYPE:
                $field = new Date($value, $before, $after);
                break;

            // DATE_TIME_TYPE
            case MetaFieldModel::DATE_TIME_TYPE:
                $field = new DateTime($value, $before, $after);
                break;

            // TIME_TYPE
            case MetaFieldModel::TIME_TYPE:
                $field = new Time($value, $before, $after);
                break;

            // DATE_RANGE_TYPE
            case MetaFieldModel::DATE_RANGE_TYPE:
                $field = new DateRange($value, $before, $after);
                break;

            // RAW ARRAY VALUES
            case MetaFieldModel::CHECKBOX_TYPE:
            case MetaFieldModel::LIST_TYPE:
            case MetaFieldModel::SELECT_MULTI_TYPE:
                $field = new ArrayValues($value, $before, $after);
                break;

            // LENGTH_TYPE
            case MetaFieldModel::LENGTH_TYPE:
                $field = new Length($value, $before, $after);
                break;

            // PHONE_TYPE
            case MetaFieldModel::PHONE_TYPE:
                $field = new Phone($value, $before, $after);
                break;

            // POST_TYPE
            case MetaFieldModel::POST_TYPE:
                $field = new Relationship($value);
                break;

            // POST_OBJECT_TYPE
            case MetaFieldModel::POST_OBJECT_TYPE:
                $field = new Post($value);
                break;

            // POST_OBJECT_MULTI_TYPE
            case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
                $field = new PostMulti($value);
                break;

            // QR_CODE_TYPE
            // URL_TYPE
            case MetaFieldModel::QR_CODE_TYPE:
            case MetaFieldModel::URL_TYPE:
                $field = new Url($value, $before, $after);
                break;

            // TERM_OBJECT_TYPE
            case MetaFieldModel::TERM_OBJECT_TYPE:
                $field = new Term($value);
                break;

            // TERM_OBJECT_MULTI_TYPE
            case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
                $field = new TermMulti($value);
                break;

            // USER_TYPE
            case MetaFieldModel::USER_TYPE:
                $field = new User($value);
                break;

            // USER_MULTI_TYPE
            case MetaFieldModel::USER_MULTI_TYPE:
                $field = new UserMulti($value);
                break;

            // WEIGHT_TYPE
            case MetaFieldModel::WEIGHT_TYPE:
                $field = new Weight($value, $before, $after);
                break;

            // DEFAULT
            default:
                $field = new Base($value, $before, $after);
        }

        return $field->getValue();
    }
}
