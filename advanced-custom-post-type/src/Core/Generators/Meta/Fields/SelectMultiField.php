<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;

class SelectMultiField extends AbstractField
{
	public function render()
	{
		$cssClass = 'acpt-select2 acpt-admin-meta-field-input';

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::SELECT_MULTI_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$select = '<select '.$this->required().' multiple id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value][]" class="'.$cssClass.'" '.$this->appendDataValidateAndLogicAttributes() . '>';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::SELECT_MULTI_TYPE.'">';
			$select = '<select '.$this->required().' multiple id="'.Strings::esc_attr($this->getIdName()).'" name="'. Strings::esc_attr($this->getIdName()).'[]" class="'.$cssClass.'" '.$this->appendDataValidateAndLogicAttributes() . '>';
		}

		$selectedOptions = $this->selectedOptions($this->metaField->getOptions(), true);

		foreach ($this->metaField->getOptions() as $option){
			$selected = (in_array($option->getValue(), $selectedOptions)) ? 'selected="selected"' : '';
			$select .= '<option '.$selected.' value="'.Strings::esc_attr(Translator::translateString($option->getValue())).'">'.esc_html(Translator::translateString($option->getLabel())).'</option>';
		}

		$select .= '</select>';
        $field .= "<div class='acpt-placeholder'>Loading...</div>";

		$field .= (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $select);

		return $this->renderField($field);
	}
}