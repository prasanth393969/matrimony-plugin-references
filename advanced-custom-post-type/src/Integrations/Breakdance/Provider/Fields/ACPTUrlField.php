<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTUrlField extends ACPTStringField
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
					['text' =>  Translator::translate('Plain text'), 'value' => 'text'],
					['text' =>  Translator::translate('Link'), 'value' => 'link'],
				]
			]),
			\Breakdance\Elements\control('id', 'ID', [
				'type' => 'text',
				'layout' => 'vertical',
				'condition' => [
					'path' => 'attributes.render',
					'operand' => 'equals',
					'value' => 'link'
				]
			]),
			\Breakdance\Elements\control('classes', Translator::translate('Classes (separated by space)'), [
				'type' => 'text',
				'layout' => 'vertical',
				'condition' => [
					'path' => 'attributes.render',
					'operand' => 'equals',
					'value' => 'link'
				]
			]),
			\Breakdance\Elements\control('target', Translator::translate('Target link'), [
				'type' => 'dropdown',
				'layout' => 'vertical',
				'items' => [
					['text' => Translator::translate('Opens in a new window or tab '), 'value' => '_blank'],
					['text' => Translator::translate('Opens in the full body of the window'), 'value' => '_self'],
					['text' => Translator::translate('Opens in the parent frame'), 'value' => '_parent'],
					['text' => Translator::translate('Opens in the same frame as it was clicked'), 'value' => '_top'],
				],
				'condition' => [
					'path' => 'attributes.render',
					'operand' => 'equals',
					'value' => 'link'
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
			'id' => '',
			'classes' => '',
			'render' => 'text',
			'target' => '_blank',
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
		$id = $attributes['id'] ?? '';
		$classes = $attributes['classes'] ?? '';
		$format = $attributes['render'] ?? null;
		$target = $attributes['target'] ?? '_blank';
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(!isset($value['url']) or empty($value['url'])){
			return StringData::emptyString();
		}

		$href = $value['url'];
		$anchorText = (!empty($value['label'])) ? $before . $value['label'] . $after : $value['url'];

		if(!is_string($href)){
			return StringData::emptyString();
		}

		if($format === 'link'){
			$value = '<a id="'.$id.'" class="'.$classes.'" href="'.Url::sanitize($href).'" target="'.$target.'">'.$anchorText.'</a>';

			return StringData::fromString($value);
		}

		return StringData::fromString($anchorText);
	}
}
