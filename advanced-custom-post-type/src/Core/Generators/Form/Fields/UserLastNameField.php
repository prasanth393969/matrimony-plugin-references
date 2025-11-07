<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;

class UserLastNameField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
        $min = (!empty($this->fieldModel->getExtra()['min'])) ? Strings::esc_attr($this->fieldModel->getExtra()['min']) : null;
        $max = (!empty($this->fieldModel->getExtra()['max'])) ? Strings::esc_attr($this->fieldModel->getExtra()['max']) : null;

		return "
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				placeholder='".$this->placeholder()."'
				value='".$this->defaultValue()."'
				type='text'
				class='".$this->cssClass()."'
				".$this->required()."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
				".$this->appendMaxLengthAndMinLength($max, $min)."
			/>";
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
