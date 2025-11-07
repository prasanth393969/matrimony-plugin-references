<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\Wordpress\Translator;
use Breakdance\DynamicData\StringData;

class ACPTRatingField extends ACPTStringField
{
	/**
	 * @return array
	 */
	public function controls()
	{
		return [
			\Breakdance\Elements\control('id', 'ID', [
				'type' => 'text',
				'layout' => 'vertical',
			]),
			\Breakdance\Elements\control('classes', Translator::translate('Classes (separated by space)'), [
				'type' => 'text',
				'layout' => 'vertical',
			]),
			\Breakdance\Elements\control('size', Translator::translate('Size (px)'), [
				'type' => 'number',
				'layout' => 'vertical',
			]),
			\Breakdance\Elements\control('color', Translator::translate('Color'), [
				'type' => 'color',
				'layout' => 'vertical',
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
			'size' => '18',
			'color' => '',
		];
	}

	public function handler($attributes): StringData
	{
		$id = $attributes['id'] ?? '';
		$classes = $attributes['classes'] ?? '';
		$size = $attributes['size'] ?? '18';
		$color = $attributes['color'] ?? null;

		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if($value === null){
			return StringData::emptyString();
		}

		$value = '<span id="'.$id.'" class="'.$classes.'" style="'.$this->styleAttributes($size, $color).'">' . $value . '</span>';

		return StringData::fromString($before . $value . $after);
	}



	/**
	 * @param string $size
	 * @param null $spacing
	 * @param null $color
	 *
	 * @return string
	 */
	private function styleAttributes($size = '18', $color = null)
	{
		$attributes = 'font-size: '.$size.'px;';

		if($color){
			$attributes .= 'color: '.$color.'; fill: '.$color.';';
		}

		return $attributes;
	}
}