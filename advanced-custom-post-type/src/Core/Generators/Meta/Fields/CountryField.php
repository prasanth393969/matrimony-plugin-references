<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class CountryField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$this->enqueueAssets();

		$cssClass = 'regular-text acpt-admin-meta-field-input acpt-country';

		if($this->hasErrors()){
			$cssClass .= ' has-errors';
		}

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$country = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::COUNTRY_TYPE.'">';
			$country .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$country .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[country]" value="'.$this->getCountry() . '">';
			$country .= '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" value="'.Strings::esc_attr($this->getDefaultValue()).'" type="text" class="'.$cssClass.'"';
		} else {
			$country = '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_type" value="' . MetaFieldModel::COUNTRY_TYPE . '">';
			$country .= '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_country" value="'.$this->getCountry() . '">';
			$country .= '<input ' . $this->required() . ' id="' . Strings::esc_attr( $this->getIdName() ) . '" name="' . Strings::esc_attr( $this->getIdName() ) . '" type="text" class="'.$cssClass.'" value="' . Strings::esc_attr( $this->getDefaultValue() ) . '"';
		}

		$min = $this->getAdvancedOption('min');
		$max = $this->getAdvancedOption('max');
		$pattern = $this->getAdvancedOption('pattern');

		$country .= $this->appendPatternMaxlengthAndMinlength($max, $min, $pattern);
		$country .= $this->appendDataValidateAndLogicAttributes();
		$country .= '>';

		$field = (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $country);

		return $this->renderField($field);
	}

	/**
	 * @return string|null
	 */
	private function getCountry()
	{
		return $this->getDefaultAttributeValue('country', 'us');
	}

	/**
	 * Enqueue needed assets
	 */
	private function enqueueAssets()
	{
		wp_enqueue_script( 'countrySelect-js', plugins_url( 'advanced-custom-post-type/assets/vendor/countrySelect/js/countrySelect.min.js'), [], '2.1.1', true);
		wp_enqueue_style( 'countrySelect-css', plugins_url( 'advanced-custom-post-type/assets/vendor/countrySelect/css/countrySelect.min.css'), [], '2.1.1', 'all');
	}
}