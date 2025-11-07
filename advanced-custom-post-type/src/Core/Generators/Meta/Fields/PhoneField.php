<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class PhoneField extends AbstractField
{
	public function render()
	{
		$this->enqueueAssets();

		$cssClass = 'regular-text acpt-admin-meta-field-input acpt-phone';

		if($this->hasErrors()){
			$cssClass .= ' has-errors';
		}

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$phone = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::PHONE_TYPE.'">';
			$phone .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$phone .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[utils]" value="'.$this->getUtilsUrl() . '">';
			$phone .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[dial]" value="'.$this->getDialCode() . '">';
			$phone .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[country]" value="'.$this->getCountry() . '">';
			$phone .= '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" value="'.Strings::esc_attr($this->getDefaultPhoneValue()).'" type="tel" class="'.$cssClass.'"';

		} else {
			$phone = '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_type" value="' . MetaFieldModel::PHONE_TYPE . '">';
			$phone .= '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_utils" value="'.$this->getUtilsUrl() . '">';
			$phone .= '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_dial" value="'.$this->getDialCode() . '">';
			$phone .= '<input type="hidden" name="' . Strings::esc_attr( $this->getIdName() ) . '_country" value="'.$this->getCountry() . '">';
			$phone .= '<input ' . $this->required() . ' id="' . Strings::esc_attr( $this->getIdName() ) . '" name="' . Strings::esc_attr( $this->getIdName() ) . '" type="tel" class="'.$cssClass.'" value="' . Strings::esc_attr( $this->getDefaultPhoneValue() ) . '"';
		}
		$min = $this->getAdvancedOption('min');
		$max = $this->getAdvancedOption('max');
		$pattern = $this->getAdvancedOption('pattern');

		$phone .= $this->appendPatternMaxlengthAndMinlength($max, $min, $pattern);
		$phone .= $this->appendDataValidateAndLogicAttributes();
		$phone .= '>';

		$field = (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $phone);

		return $this->renderField($field);
	}

	/**
	 * @return string
	 */
	private function getUtilsUrl()
	{
		return plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/js/utils.min.js');
	}

    /**
     * @return string
     */
    private function getDefaultPhoneValue()
    {
        if(is_string($this->getDefaultValue())){
            return $this->getDefaultValue();
        }

        $defaultValue = $this->getDefaultValue();

        if(isset($defaultValue['phone']) and is_string($defaultValue['phone'])){
            return $defaultValue['phone'];
        }

        return null;
    }

	/**
	 * @return string|null
	 */
	private function getCountry()
	{
        $defaultValue = (is_array($this->getDefaultValue()) and isset($this->getDefaultValue()['country']['iso2'])) ? $this->getDefaultValue()['country']['iso2'] : 'us';

		return $this->getDefaultAttributeValue('country', $defaultValue);
	}

	/**
	 * @return string|null
	 */
	private function getDialCode()
	{
        $defaultValue = (is_array($this->getDefaultValue()) and isset($this->getDefaultValue()['country']['dial'])) ? $this->getDefaultValue()['country']['dial'] : '1';

		return $this->getDefaultAttributeValue('dial', $defaultValue);
	}

	/**
	 * Enqueue needed assets
	 */
	private function enqueueAssets()
	{
		wp_enqueue_script( 'intlTelInput-js', plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/js/intlTelInput.min.js'), [], '1.10.60', true);
		wp_enqueue_style( 'intlTelInput-css', plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/css/intlTelInput.min.css'), [], '1.10.60', 'all');
	}
}
