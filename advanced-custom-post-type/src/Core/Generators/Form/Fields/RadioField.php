<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Utils\Wordpress\Translator;

class RadioField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$value = $this->defaultValue();

		if(!is_string($value)){
            $value = null;
        }

		$options = (!empty($this->fieldModel->getExtra()['options'])) ? $this->fieldModel->getExtra()['options'] : [];
		$display = (!empty($this->fieldModel->getExtra()['display'])) ? $this->fieldModel->getExtra()['display'] : 'block';
        $css = $this->cssClass();

        $field = '<ul class="acpt-radio '.$display.' '.$css.'">';

        if($this->fieldModel->getMetaField() !== null and empty($this->fieldModel->getMetaField()->getAdvancedOption('hide_blank_radio'))){
            $field .= '
                <li><input 
                     '.$this->disabled().'
                    name="'.Strings::esc_attr($this->getIdName()).'" 
                    id="'.Strings::esc_attr($this->getIdName()).'_blank" 
                    type="radio"
                    value="" 
                    '.(empty($value) ? "checked" : "").'
			        '.$this->required().'
			        '.$this->appendDataValidateAndConditionalRenderingAttributes().'
                />
                <label for="'.Strings::esc_attr($this->getIdName()).'_blank">'.Translator::translate('No choice').'</label></li>
           ';
        }

		foreach ($options as $index => $option){
			$field .= '
				<li><input 
				    '.$this->disabled().'
					id="'.Strings::esc_attr($this->getIdName()).'_'.$index.'"
					name="'.Strings::esc_attr($this->getIdName()).'"
					type="radio" 
			        value="'.Strings::esc_attr($option['value']).'"
			        '.($option['value'] == $value ? "checked" : "").'
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
