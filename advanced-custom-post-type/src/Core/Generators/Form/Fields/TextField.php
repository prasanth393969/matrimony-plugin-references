<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;

class TextField extends AbstractField
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

        $field = "<input
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

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
