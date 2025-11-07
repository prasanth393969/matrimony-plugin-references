<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;

class CheckboxField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$value = $this->defaultValue();

		if(!is_array($value)){
            $value = [];
        }

		$options = (!empty($this->fieldModel->getExtra()['options'])) ? $this->fieldModel->getExtra()['options'] : [];
        $display = (!empty($this->fieldModel->getExtra()['display'])) ? $this->fieldModel->getExtra()['display'] : 'block';
        $css = $this->cssClass();

		$field = '<ul class="acpt-checkboxes '.$display.' '.$css.'">';

		foreach ($options as $index => $option){
			$field .= '
				<li><input 
				    '.$this->disabled().'
					id="'.Strings::esc_attr($this->getIdName()).'_'.$index.'"
					name="'.Strings::esc_attr($this->getIdName()).'[]"
					type="checkbox" 
			        value="'.Strings::esc_attr($option['value']).'"
			        '.(in_array($option['value'], $value) ? "checked" : "").'
			        '.$this->required().'
			        '.$this->appendDataValidateAndConditionalRenderingAttributes().'
		        />
			    <label class="checkbox-label" for="'.Strings::esc_attr($this->getIdName()).'_'.$index.'">
			    	'.Strings::esc_attr($option['label']).'    
				</label></li>';
		}

        $field .= '</ul>';

		return $field;
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
