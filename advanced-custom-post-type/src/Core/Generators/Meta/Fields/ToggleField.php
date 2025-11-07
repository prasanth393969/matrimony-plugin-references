<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class ToggleField extends AbstractField
{
	public function render()
	{
		$checked = ($this->getToggleValue() == 1) ? 'checked="checked"' : '';

		if($this->isChild() or $this->isNestedInABlock()){
			$id = "toggle_".Strings::generateRandomId();
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::TOGGLE_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$field .= '<input type="hidden" id="'.$id.'" name="'.esc_html($this->getIdName()).'[value]" value="'.Strings::esc_attr($this->getToggleValue()).'">';
			$field .= '<input 
				id="'.$id.'"
				name="' . $id . '" 
				type="checkbox" 
				value="1" 
				class="wppd-ui-toggle" 
				'.$checked.' 
				'.$this->appendDataValidateAndLogicAttributes() . '
			/>';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::TOGGLE_TYPE.'">';
			$field .= '<input type="hidden" id="'.Strings::esc_attr($this->getIdName()).'" name="'.esc_html($this->getIdName()).'" value="'.Strings::esc_attr($this->getToggleValue()).'">';
			$field .= '<input 
				id="'.Strings::esc_attr($this->getIdName()).'" 
				name="' . Strings::esc_attr( $this->getIdName() ) . '" 
				type="checkbox" 
				value="1" 
				class="wppd-ui-toggle" 
				'.$checked.' 
				'.$this->appendDataValidateAndLogicAttributes() . '
			/>';
		}

		return $this->renderField($field);
	}

	/**
	 * @return int
	 */
	private function getToggleValue()
	{
		$allowedValues = [0, 1];
		$defaultValue = $this->getDefaultValue();

		// this check avoids to use not boolean previously saved values
		if($defaultValue !== null and in_array($defaultValue, $allowedValues)){
			return $this->getDefaultValue();
		}

		$defaultValue = $this->metaField->getDefaultValue();

        if($defaultValue !== null and in_array($defaultValue, $allowedValues)){
			return $defaultValue;
		}

		return 0;
	}
}
