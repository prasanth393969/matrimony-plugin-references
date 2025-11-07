<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTDateRangeField extends ACPTDateField
{
	/**
	 * @return array
	 */
	public function controls()
	{
		return [
			\Breakdance\Elements\control('render',  Translator::translate('Render as'), [
				'type' => 'dropdown',
				'layout' => 'vertical',
				'items' => [
					['text' => 'Start date, end date', 'value' => 'both'],
					['text' => 'Only start date', 'value' => 'start'],
					['text' => 'Only end date', 'value' => 'end'],
				],
			]),
			\Breakdance\Elements\control('separator',  Translator::translate('Date separator'), [
				'type' => 'text',
				'layout' => 'vertical',
			]),
			\Breakdance\Elements\control('format', Translator::translate('Format'), [
				'type' => 'dropdown',
				'layout' => 'vertical',
				'items' => array_merge(
					[
						['text' => 'Default', 'value' => '']
					],
					\Breakdance\DynamicData\get_date_formats(),
					[
						['text' => 'Custom', 'value' => 'Custom'],
						['text' => 'Human', 'value' => 'Human']
					]
				),
				[
					['text' => 'Custom', 'value' => 'Custom'],
					['text' => 'Human', 'value' => 'Human']
				]
			]),
			\Breakdance\Elements\control('custom_format', Translator::translate('Custom Format'), [
				'type' => 'text',
				'layout' => 'vertical',
				'condition' => [
					'path' => 'attributes.format',
					'operand' => 'equals',
					'value' => 'Custom'
				]
			]),

		];
	}

	/**
	 * @inheritDoc
	 */
	public function defaultAttributes()
	{
		return [
			'render' => 'both',
			'separator' => '/',
			'format' => 'F j, Y',
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
		$format = $attributes['format'] ?? null;
		$render = $attributes['render'] ?? 'both';
		$separator = $attributes['separator'] ?? '/';
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

        $dates = $value['value'];
        $objects = $value['object'];

		if(!is_array($dates) or empty($dates)){
			return StringData::emptyString();
		}

        if(!is_array($objects) or empty($objects)){
            return StringData::emptyString();
        }

		$start = $dates[0];
		$end = $dates[1];

		if(empty($format)){
			if($render === 'start'){
				return StringData::fromString($start);
			}

			if($render === 'end'){
				return StringData::fromString($end);
			}

			return StringData::fromString($before.$start.$separator.$end.$after);
		}

		if ($format === 'Custom') {
			$format = $attributes['custom_format'] ?? 'F j, Y';
		}

		$startDate = $this->dateString($objects[0], $format);
		$endDate = $this->dateString($objects[1], $format);

		if($render === 'start'){
			return StringData::fromString($before.$startDate.$after);
		}

		if($render === 'end'){
			return StringData::fromString($before.$endDate.$after);
		}

		return StringData::fromString($before.$startDate.$separator.$endDate.$after);
	}
}