<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class NumberField extends AbstractField
{
	public function render()
	{
		$cssClass = 'regular-text acpt-admin-meta-field-input';

		if($this->hasErrors()){
			$cssClass .= ' has-errors';
		}

		$defaultValue = (is_numeric($this->getDefaultValue())) ? $this->getDefaultValue() : null;

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::NUMBER_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$number = '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" value="'.Strings::esc_attr($defaultValue).'" type="number" class="'.$cssClass.'"';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::NUMBER_TYPE.'">';
			$number = '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'" name="'. Strings::esc_attr($this->getIdName()).'" type="number" class="'.$cssClass.'" value="'. Strings::esc_attr($defaultValue).'"';
		}


		$min = $this->getAdvancedOption('min');
		$max = $this->getAdvancedOption('max');
		$step = $this->getAdvancedOption('step');

		if($min){
			$number .= ' min="'.$min.'"';
		}

		if($max){
			$number .= ' max="'.$max.'"';
		}

		if($step){
			$number .= ' step="'.$step.'"';
		}

		$number .= $this->appendDataValidateAndLogicAttributes();
		$number .= '>';

		$field .= (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $number);

		return $this->renderField($field);
	}
}
