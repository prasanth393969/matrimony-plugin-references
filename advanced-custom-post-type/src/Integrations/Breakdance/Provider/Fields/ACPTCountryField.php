<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\PHP\Country;
use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTCountryField extends ACPTStringField
{
	/**
	 * @return array
	 */
	public function controls()
	{
		return [
			\Breakdance\Elements\control('render', Translator::translate('Render as'), [
				'type' => 'dropdown',
				'layout' => 'vertical',
				'items' => [
					'0' => ['text' => 'Plain text', 'value' => 'text'],
					'1' => ['text' => 'Flag', 'value' => 'flag'],
					'2' => ['text' => 'Flag and text', 'value' => 'full'],
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
			'render' => 'text',
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
		$render = $attributes['render'] ?? 'text';

		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(!is_array($value)){
			return StringData::emptyString();
		}

		if(!isset($value['value'])){
			return StringData::emptyString();
		}

		if(!isset($value['country'])){
			return StringData::emptyString();
		}

		$countryName = $value['value'];
		$countryCode = $value['country'];

		if($render === 'flag'){
			return StringData::fromString(Country::getFlag($countryCode));
		}

		if($render === 'full'){
			return StringData::fromString($before . Country::fullFormat($countryCode, $countryName) . $after);
		}

		return StringData::fromString($before . $countryName . $after);
	}
}
