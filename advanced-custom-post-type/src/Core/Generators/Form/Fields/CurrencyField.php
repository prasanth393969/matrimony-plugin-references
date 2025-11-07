<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Currencies;
use ACPT\Core\Helper\Strings;

class CurrencyField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
        if($this->isNested and $this->fieldModel->getMetaField() !== null){
            $min = $this->fieldModel->getMetaField()->getAdvancedOption("min") ?? 0.01;
            $max = $this->fieldModel->getMetaField()->getAdvancedOption("max") ?? 99999999999999;
            $step = $this->fieldModel->getMetaField()->getAdvancedOption("step") ?? 0.01;
        } else {
            $min = (!empty($this->fieldModel->getExtra()['min'])) ? Strings::esc_attr($this->fieldModel->getExtra()['min']) : 0.01;
            $max = (!empty($this->fieldModel->getExtra()['max'])) ? Strings::esc_attr($this->fieldModel->getExtra()['max']) : 99999999999999;
            $step = (!empty($this->fieldModel->getExtra()['step'])) ? Strings::esc_attr($this->fieldModel->getExtra()['step']) : 0.01;
        }

		$field = '
			<div class="acpt-uom">
				<input 
				    '.$this->disabled().'
					placeholder="'.$this->placeholder().'"
					value="'.$this->defaultCurrencyValue().'"
					type="number"                 
					id="'.$this->getIdName().'"
					name="'.$this->getIdName().'"
					class="'.$this->cssClass().'"
					'.$this->required().'
					'.$this->appendDataValidateAndConditionalRenderingAttributes().'
					'.$this->appendMaxMinAndStep($max, $min, $step).'
				/>
				'.$this->renderUom($this->defaultCurrency(), 'currency', Currencies::getList()).'
			</div>
		';

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

    /**
     * @return string
     */
    private function defaultCurrencyValue()
    {
        $defaultValue = $this->defaultValue();

        if(is_scalar($defaultValue)){
            return $defaultValue;
        }

        if(is_array($defaultValue) and isset($defaultValue['currencyValue'])){
            return $defaultValue['currencyValue'];
        }

        return null;
    }

    /**
     * @return string
     */
	private function defaultCurrency()
    {
        $savedCurrency = $this->defaultExtraValue("currency");

        if(!empty($savedCurrency)){
            return $savedCurrency;
        }

        if(isset($this->fieldModel->getExtra()['defaultValue']) and isset($this->fieldModel->getExtra()['defaultValue']['currency'])){
            return $this->fieldModel->getExtra()['defaultValue']['currency'];
        }

        return (isset($this->fieldModel->getExtra()['uom'])) ? Strings::esc_attr($this->fieldModel->getExtra()['uom']) : 'USD';
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}