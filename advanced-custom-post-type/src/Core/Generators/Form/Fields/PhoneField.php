<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;

class PhoneField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
        if($this->isNested and $this->fieldModel->getMetaField() !== null){
            $min = $this->fieldModel->getMetaField()->getAdvancedOption("min") ?? null;
            $max = $this->fieldModel->getMetaField()->getAdvancedOption("max") ?? null;
        } else {
            $min = (!empty($this->fieldModel->getExtra()['min'])) ? Strings::esc_attr($this->fieldModel->getExtra()['min']) : null;
            $max = (!empty($this->fieldModel->getExtra()['max'])) ? Strings::esc_attr($this->fieldModel->getExtra()['max']) : null;
        }

        $field =  '<input type="hidden" id="' . Strings::esc_attr( $this->getIdName() ) . '_utils" name="' . $this->getIdName( "utils" ) . '" value="'.$this->getUtilsUrl() . '">';
        $field .= '<input type="hidden" id="' . Strings::esc_attr( $this->getIdName() ) . '_dial" name="' . $this->getIdName( "dial" ) . '" value="'.$this->getDialCode() . '">';
        $field .= '<input type="hidden" id="' . Strings::esc_attr( $this->getIdName() ) . '_country" name="' . $this->getIdName(  "country" ) . '" value="'.$this->getCountry() . '">';
        $field .= "
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				placeholder='".$this->placeholder()."'
				value='".$this->defaultValue()."'
				type='tel'
				class='acpt-phone ".$this->cssClass()."'
				".$this->required()."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
				".$this->appendMaxLengthAndMinLength($max, $min)."
			/>";

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

    /**
     * @return string
     */
    private function getUtilsUrl()
    {
        return plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/js/utils.min.js');
    }

    /**
     * @return string|null
     */
    private function getCountry()
    {
        return $this->defaultExtraValue('country') ?? 'us';
    }

    /**
     * @return string|null
     */
    private function getDialCode()
    {
        return $this->defaultExtraValue('dial') ?? "1";
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets()
    {
        wp_enqueue_script( 'intlTelInput-js', plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/js/intlTelInput.min.js'), [], '1.10.60', true);
        wp_enqueue_style( 'intlTelInput-css', plugins_url('advanced-custom-post-type/assets/vendor/intlTelInput/css/intlTelInput.min.css'), [], '1.10.60', 'all');
	}
}
