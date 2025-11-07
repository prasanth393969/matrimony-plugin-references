<?php

namespace ACPT\Integrations\Zion\Provider\Fields;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Integrations\Zion\Provider\Utils\FieldSettings;
use ACPT\Integrations\Zion\Provider\Utils\FieldValue;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\Translator;

class AcptFieldTypeTime extends AcptFieldBase
{
	/**
	 * Retrieve the list of all supported field types
	 * @return array
	 */
	public static function getSupportedFieldTypes()
	{
		return [
			MetaFieldModel::TIME_TYPE
		];
	}

	/**
	 * @return string
	 */
	public function get_category()
	{
		return self::CATEGORY_TEXT;
	}

	/**
	 * @return string
	 */
	public function get_id()
	{
		return 'acpt-field-time';
	}

	/**
	 * @return string
	 */
	public function get_name()
	{
		return Translator::translate( 'ACPT Time field');
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function get_options()
	{
		return array_merge(
			parent::get_options(),
			[
				'format' => [
					'type'        => 'select',
					'title'       => Translator::translate('Time format'),
					'description' => Translator::translate('Select the time format'),
					'placeholder' => Translator::translate('--Select--'),
					'default'     => 'H:i',
					'options'     => [
						['name' => 'H:i', 'id' => 'H:i'],
						['name' => 'g:i a', 'id' => 'g:i a'],
						['name' => 'g:i A', 'id' => 'g:i A'],
					]
				],
			]
		);
	}

	/**
	 * @param mixed $fieldObject
	 *
	 * @throws \Exception
	 */
	public function render($fieldObject)
	{
		//#! Invalid entry, nothing to do here
		if (empty($fieldObject[ 'field_name' ])) {
			return;
		}

		$fieldSettings = FieldSettings::get($fieldObject[ 'field_name' ]);

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

        if(!isset($rawValue['value'])){
            return;
        }

        if(!isset($rawValue['object'])){
            return;
        }

        /** @var $dataTimeObject \DateTime */
        $dataTimeObject = $rawValue['object'];
        $value = $rawValue['value'];
		$format = $fieldObject['format'] ?? 'H:i';

        echo ($format !== null) ? Date::format($format, $dataTimeObject) : $value;
	}
}