<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTBarcodeField extends ACPTStringField
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
					['text' =>  Translator::translate('Image'), 'value' => 'image'],
					['text' =>  Translator::translate('Text'), 'value' => 'text'],
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
			'render' => 'image',
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
		$format = $attributes['render'] ?? null;
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(!isset($value['text']) or empty($value['text'])){
			return StringData::emptyString();
		}

		if($format === 'text'){
			return StringData::fromString($before . $value['text'] . $after);
		}

        if(!isset($value['value']['svg'])){
            return StringData::fromString($before . $value['text'] . $after);
        }

        preg_match_all('/<svg(.*?)id=\"(.*?)\"(.*?)>/', $value['value']['svg'], $match);
        if(isset($match[2]) and isset($match[2][0])){
            return StringData::fromString($before . $value['value']['svg'] . $after);
        }

        return StringData::fromString($before . $value['text'] . $after);
	}
}
