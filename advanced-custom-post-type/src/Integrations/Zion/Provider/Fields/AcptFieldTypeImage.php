<?php

namespace ACPT\Integrations\Zion\Provider\Fields;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Integrations\Zion\Provider\Utils\FieldSettings;
use ACPT\Integrations\Zion\Provider\Utils\FieldValue;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPAttachment;

class AcptFieldTypeImage extends AcptFieldBase
{
	/**
	 * Retrieve the list of all supported field types
	 * @return array
	 */
	public static function getSupportedFieldTypes()
	{
		return [
			MetaFieldModel::QR_CODE_TYPE,
			MetaFieldModel::IMAGE_TYPE,
		];
	}

	/**
	 * @return string
	 */
	public function get_category()
	{
		return self::CATEGORY_IMAGE;
	}

	/**
	 * @return string
	 */
	public function get_id()
	{
		return 'acpt-field-image';
	}

	/**
	 * @return string
	 */
	public function get_name()
	{
		return Translator::translate( 'ACPT Image field');
	}

	/**
	 * @param mixed $fieldObject
	 *
	 * @throws \Exception
	 */
	public function render($fieldObject)
	{
		//#! Invalid entry, nothing to do here
		if(empty( $fieldObject['field_name'])) {
			return;
		}

		$fieldSettings = FieldSettings::get($fieldObject['field_name']);

		if($fieldSettings === false or empty($fieldSettings)){
			return;
		}

		/** @var MetaFieldModel $metaFieldModel */
		$metaFieldModel = $fieldSettings['model'];
		$belongsTo = $fieldSettings['belongsTo'];

		if(!$this->isSupportedFieldType($metaFieldModel->getType())){
			return;
		}

		$rawValue = FieldValue::raw($belongsTo, $metaFieldModel);

		if(empty($rawValue)){
			return;
		}

        switch ($metaFieldModel->getType()){

            case MetaFieldModel::IMAGE_TYPE:
                if(!$rawValue instanceof WPAttachment){
                    return;
                }

                echo $rawValue->getSrc();
                break;

            case MetaFieldModel::QR_CODE_TYPE:
                if(!is_array($rawValue)){
                    return;
                }

                if(!isset($rawValue['value'])){
                    return;
                }

                if(!isset($rawValue['value']['img'])){
                    return;
                }

                echo $rawValue['value']['img'];
                break;
        }

		echo '';
	}
}