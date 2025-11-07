<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTListField extends ACPTStringField
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
					['text' => Translator::translate('Label'), 'value' => 'label'],
					['text' => Translator::translate('Value'), 'value' => 'value'],
				]
			]),
			\Breakdance\Elements\control('list', Translator::translate('List format'), [
				'type' => 'dropdown',
				'layout' => 'vertical',
				'items' => [
					['text' => Translator::translate('Unordered list'), 'value' => 'ul'],
					['text' => Translator::translate('Ordered list'), 'value' => 'ol'],
					['text' => Translator::translate('Strings'), 'value' => 'string'],
				]
			]),
			\Breakdance\Elements\control('separator',  Translator::translate('String separator'), [
				'type' => 'text',
				'layout' => 'vertical',
				'condition' => [
					'path' => 'attributes.list',
					'operand' => 'equals',
					'value' => 'string'
				]
			]),
			\Breakdance\Elements\control('classes',  Translator::translate('Classes (separated by space)'), [
				'type' => 'text',
				'layout' => 'vertical',
				'condition' => [
					'path' => 'attributes.list',
					'operand' => 'equals',
					'value' => 'list'
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
			'render' => 'value',
			'list' => 'string',
			'separator' => ',',
			'classes' => '',
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
		$format = $attributes['render'] ?? 'value';
		$list = (isset($attributes['list']) and $attributes['list'] !== null) ? $attributes['list'] : 'string';
		$separator = (isset($attributes['separator']) and $attributes['separator'] !== null) ? $attributes['separator'] : ',';
		$classes = (isset($attributes['classes']) and $attributes['classes'] !== null) ? $attributes['classes'] : '';

		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(empty($value)){
			return StringData::emptyString();
		}

		if(!is_array($value)){
			return StringData::emptyString();
		}

		$items = [];

		foreach ($value as $item){
			$items[] = ($format === 'label') ? $before.$this->fieldModel->getOptionLabel($item).$after : $before.$item.$after;
		}

		if($list === 'ul'){
			$value = '<ul>';

			foreach ($items as $item){
				$value .= '<li class="'.$classes.'">'.$item.'</li>';
			}

			$value .= '</ul>';

			return StringData::fromString($value);
		}

		if($list === 'ol'){
			$value = '<ol>';

			foreach ($items as $item){
				$value .= '<li class="'.$classes.'">'.$item.'</li>';
			}

			$value .= '</ol>';

			return StringData::fromString($value);
		}

		if(!is_array($items)){
			return StringData::emptyString();
		}

		$value = implode($separator, $items);

		return StringData::fromString($value);
	}
}