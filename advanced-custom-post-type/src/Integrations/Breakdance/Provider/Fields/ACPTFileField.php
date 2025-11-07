<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPAttachment;
use Breakdance\DynamicData\StringData;

class ACPTFileField extends ACPTStringField
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
					['text' => Translator::translate('Plain text'), 'value' => 'text'],
					['text' => Translator::translate('Link'), 'value' => 'link'],
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
		$format = $attributes['render'] ?? null;
		$target = $attributes['target'] ?? '_blank';
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(!isset($value['file']) or empty($value['file'])){
			return StringData::emptyString();
		}

		$file = $value['file'];

        $wpAttachment = WPAttachment::fromId($file);

        if($wpAttachment->isEmpty()){
            return StringData::emptyString();
        }

		$src = $wpAttachment->getSrc();
		$label = (isset($value['label'])) ? $before . $value['label'] . $after : $src;

		if($format === 'link'){
			$value = '<a href="'.$src.'" target="'.$target.'">'.$label.'</a>';

			return StringData::fromString($value);
		}

		return StringData::fromString($label);
	}
}
