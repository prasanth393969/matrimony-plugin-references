<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Lengths;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class LengthField extends AbstractField
{
	public function render()
	{
		$list = Lengths::getList();
		$cssClass = 'currency-amount';

		if($this->hasErrors()){
			$cssClass .= ' has-errors';
		}

		$after = (!empty($this->getAdvancedOption('after'))) ? "<span class='after'>".Strings::htmlspecialchars($this->getAdvancedOption('after'))."</span>" : "";
		$before =  (!empty($this->getAdvancedOption('before'))) ? "<span class='before'>".Strings::htmlspecialchars($this->getAdvancedOption('before'))."</span>" : "";

		$min = $this->getAdvancedOption('min') ? $this->getAdvancedOption('min') : 1;
		$max = $this->getAdvancedOption('max') ? $this->getAdvancedOption('max') : null;
		$step = $this->getAdvancedOption('step') ? $this->getAdvancedOption('step') : 1;
        $symbol = $list[$this->getDefaultLengthValue()]['symbol'] ?? Lengths::DEFAULT_LENGTH;

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::LENGTH_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$field .= '<input type="hidden" name="meta_fields[]" value="'.Strings::esc_attr($this->getIdName()).'[length]">';
			$field .= '<div class="currency-group w-full">
					'.$before.'
                    <div class="currency-symbol">'.$symbol.'</div>
                    <input 
                        '.$this->required().' 
                        id="'.Strings::esc_attr($this->getIdName()).'[value]" 
                        name="'. Strings::esc_attr($this->getIdName()).'[value]" 
                        type="number" 
                        min="'.$min.'" 
                        max="'.$max.'" 
                        step="'.$step.'"
                        class="'.$cssClass.'" 
                        value="'.Strings::esc_attr($this->defaultValue()).'"
                        '.$this->appendDataValidateAndLogicAttributes() . '
                    >
                    <div class="currency-addon">
                        <select name="'. Strings::esc_attr($this->getIdName()).'[length]" class="currency-selector">';

			foreach (Lengths::getList() as $currency => $data){
				$selected = ($currency === $this->getDefaultLengthValue()) ? 'selected' : '';
                $symb = $data['symbol'] ?? Lengths::DEFAULT_LENGTH;
				$field .= '<option value="'.Strings::esc_attr($currency).'" data-symbol="'.Strings::esc_attr($symb).'" data-placeholder="0.00" '.$selected.'>'.esc_html($currency).'</option>';
			}

		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::LENGTH_TYPE.'">';
			$field .= '<div class="currency-group">
					'.$before.'
                    <div class="currency-symbol">'.$symbol.'</div>
                    <input 
                    	'.$this->required().' 
                    	id="'.Strings::esc_attr($this->getIdName()).'" 
                    	name="'. Strings::esc_attr($this->getIdName()).'" 
                    	type="number" 
                    	min="'.$min.'" 
                    	max="'.$max.'" 
                    	step="'.$step.'" 
                    	class="'.$cssClass.'" 
                    	value="'.Strings::esc_attr($this->defaultValue()).'"
                    	'.$this->appendDataValidateAndLogicAttributes() . '
					>
                  	<div class="currency-addon">
                        <select name="'. Strings::esc_attr($this->getIdName()).'_length" class="currency-selector">';

			foreach ($list as $currency => $data){
				$selected = ($currency === $this->getDefaultLengthValue()) ? 'selected' : '';
                $symb = $data['symbol'] ?? Lengths::DEFAULT_LENGTH;
				$field .= '<option value="'.Strings::esc_attr($currency).'" data-symbol="'.Strings::esc_attr($symb).'" data-placeholder="0.00" '.$selected.'>'.esc_html($currency).'</option>';
			}
		}

		$field .=' </select>
              </div>
              '.$after.'
            </div>';

		return $this->renderField($field);
	}

    /**
     * @return mixed|null
     */
    private function defaultValue()
    {
        $defaultValue = $this->getDefaultValue();

        if(is_scalar($defaultValue)){
            return $defaultValue;
        }

        if(is_array($defaultValue) and isset($defaultValue['lengthValue'])){
            return $defaultValue['lengthValue'];
        }

        return null;
    }

	/**
	 * @return string
	 */
	private function getDefaultLengthValue()
	{
        $defaultValue = $this->getDefaultValue();

        if(is_scalar($defaultValue)){
            $uomDefaultValue = $this->getAdvancedOption('uom_default_value') ? $this->getAdvancedOption('uom_default_value') : 'METER';

            return $this->getDefaultAttributeValue('length', $uomDefaultValue);
        }

        if(is_array($defaultValue) and isset($defaultValue['length'])){
            return $defaultValue['length'];
        }

        return null;
	}
}