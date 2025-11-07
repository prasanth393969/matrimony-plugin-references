<?php

namespace ACPT\Core\Generators\Form\Fields;

class ToggleField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		return '
			<div class="toggle-group">
                <label class="toggle">
                    <input
                        '.$this->disabled().'
                        id="'.$this->getIdName().'"
                        name='.$this->getIdName().'
                        type="checkbox"
                        value="1"
            			'.($this->isChecked() ? "checked" : "").'            
                    	'.$this->required().'
                    	'.$this->appendDataValidateAndConditionalRenderingAttributes().'
                    />
                    <span class="slider round"/>
                </label>
            </div>
		';
	}

    /**
     * @return bool
     */
	public function isChecked()
    {
        $allowedValues = [0, 1];
        $defaultValue = $this->defaultValue();

        // this check avoids to use not boolean previously saved values
        if($defaultValue !== null and in_array($defaultValue, $allowedValues)){
            return $defaultValue == 1;
        }

        return (isset($this->fieldModel->getExtra()['checked']) and $this->fieldModel->getExtra()['checked'] == 1) ? true : false;
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
