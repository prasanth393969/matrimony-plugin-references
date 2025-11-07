<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTTimeField extends ACPTStringField
{
	/**
	 * @return array
	 */
	public function controls()
	{
		return [
			\Breakdance\Elements\control('format',  Translator::translate('Format'), [
				'type' => 'dropdown',
				'layout' => 'vertical',
				'items' => array_merge(
					[['text' => 'Default', 'value' => 'H:i']],
					\Breakdance\DynamicData\get_time_formats(),
					[
						['text' => 'Custom', 'value' => 'Custom'],
					]
				)
			]),
			\Breakdance\Elements\control('custom_format',  Translator::translate('Custom Format'), [
				'type' => 'text',
				'layout' => 'vertical',
				'condition' => [
					'path' => 'attributes.format',
					'operand' => 'equals',
					'value' => 'Custom'
				]
			])
		];
	}

	/**
	 * @inheritDoc
	 */
	public function defaultAttributes()
	{
		return [
			'format' => 'H:i'
		];
	}

	/**
	 * @param mixed $attributes
	 *
	 * @return StringData
	 * @throws \Exception
	 */
	public function handler($attributes): StringData
	{
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

        if(!isset($value['value'])){
            return StringData::emptyString();
        }

        if(!isset($value['object'])){
            return StringData::emptyString();
        }

        if(!$value['object'] instanceof \DateTime){
            return StringData::emptyString();
        }

        $date = $value['value'];
        $object = $value['object'];

		if(!is_string($date) or $date === null){
			return StringData::emptyString();
		}

		if(empty($attributes['format'])){
			return StringData::fromString($before . $date . $after);
		}

		$format = $attributes['format'];

		if ($format === 'Custom') {
			$format = $attributes['custom_format'] ?? 'H:i';
		}

        try {
            $value = Date::format($format, $object);

            return StringData::fromString($before . $value . $after);
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return StringData::emptyString();
        }
	}
}