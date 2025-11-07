<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Weights;

class WeightField extends AbstractField
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
					value="'.$this->defaultWeightValue().'"
					type="number"
					id="'.$this->getIdName().'"
					name="'.$this->getIdName().'"
					class="'.$this->cssClass().'"
					'.$this->required().'
					'.$this->appendDataValidateAndConditionalRenderingAttributes().'
					'.$this->appendMaxMinAndStep($max, $min, $step).'
				/>
				'.$this->renderUom($this->defaultWeight(), 'weight', Weights::getList()).'
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
    private function defaultWeightValue()
    {
        $defaultValue = $this->defaultValue();

        if(is_scalar($defaultValue)){
            return $defaultValue;
        }

        if(is_array($defaultValue) and isset($defaultValue['weightValue'])){
            return $defaultValue['weightValue'];
        }

        return null;
    }

    /**
     * @return string
     */
    private function defaultWeight()
    {
        $savedWeight = $this->defaultExtraValue("weight");

        if(!empty($savedWeight)){
            return $savedWeight;
        }

        if(isset($this->fieldModel->getExtra()['defaultValue']) and isset($this->fieldModel->getExtra()['defaultValue']['weight'])){
            return $this->fieldModel->getExtra()['defaultValue']['weight'];
        }

        return (isset($this->fieldModel->getExtra()['uom'])) ? Strings::esc_attr($this->fieldModel->getExtra()['uom']) : 'KILOGRAM';
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}