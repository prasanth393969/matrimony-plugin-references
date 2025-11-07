<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTQRCodeField extends ACPTStringField
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
					['text' =>  Translator::translate('Link'), 'value' => 'link'],
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

		if(!isset($value['url']) or empty($value['url'])){
			return StringData::emptyString();
		}

		if($format === 'link'){
			$value = '<a href="'.Url::sanitize($value['url']).'" target="_blank">'.$before . $value['url'] . $after .'</a>';

			return StringData::fromString($value);
		}

        return StringData::fromString($before . QRCode::render($value) . $after);
	}
}
