<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\PHP\Date;
use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTDateTimeField extends ACPTStringField
{
	/**
	 * @return array
	 */
	public function controls()
	{
		return [
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
			\Breakdance\Elements\control('time_format',  Translator::translate('Time format'), [
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
			'format' => 'F j, Y',
			'time_format' => 'H:i',
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

		if(empty($attributes['format']) and empty($attributes['time_format'])){
			return StringData::fromString($before . $date . $after);
		}

		$format = $attributes['format'] ?? get_option('date_format');
		$timeFormat = $attributes['time_format'] ?? get_option('time_format');

		if (empty($format) || $format === 'Default') {
			$format = get_option('date_format');
		}

		if (empty($timeFormat) || $timeFormat === 'Default') {
			$timeFormat = get_option('time_format');
		}

		if ($format === 'Custom') {
			$format = $attributes['custom_format'] ?? 'F j, Y G:i';
		}

		return StringData::fromString($before . $this->dateString($object, $format . ' ' . $timeFormat) . $after);
	}

	/**
	 * @param \DateTime $datetime
	 * @param null $format
	 *
	 * @return false|string
	 */
	protected function dateString(\DateTime $datetime, $format = null)
	{
		$format = ($format !== '') ? $format : 'F j, Y';

		if ($format === 'Human') {
			return human_time_diff(wp_date('U', $datetime->getTimestamp()));
		}

		return Date::format($format, $datetime);
	}
}