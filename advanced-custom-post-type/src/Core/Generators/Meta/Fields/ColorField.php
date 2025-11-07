<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Browser;
use ACPT\Utils\Wordpress\Translator;

class ColorField extends AbstractField
{
	public function render()
	{
		$cssClass = 'acpt-admin-meta-field-input acpt-color-picker';

		if($this->hasErrors()){
			$cssClass .= ' has-errors';
		}

		if($this->isChild() or $this->isNestedInABlock()){

			if($this->isLeadingField()){
				$cssClass .= ' acpt-leading-field';
			}

			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::COLOR_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$field .= '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" type="text" class="'.$cssClass.'" value="' .Strings::esc_attr($this->getDefaultValue()).'" '.$this->appendDataValidateAndLogicAttributes() . '>';

			if($this->isBrowserEnabled()){
                $field .= '<button data-target-id="'.Strings::esc_attr($this->getIdName()).'[value]" class="acpt-eye-dropper-button button">'.Translator::translate('Eye dropper').'</button>';
            }

		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::COLOR_TYPE.'">';
			$field .= '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'" name="'. Strings::esc_attr($this->getIdName()).'" type="text" class="'.$cssClass.'" value="' .Strings::esc_attr($this->getDefaultValue()).'" '.$this->appendDataValidateAndLogicAttributes() . '>';

			if($this->isBrowserEnabled()){
                $field .= '<button data-target-id="'.Strings::esc_attr($this->getIdName()).'" class="acpt-eye-dropper-button button">'.Translator::translate('Eye dropper').'</button>';
            }
		}

		return $this->renderField($field);
	}

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/API/EyeDropper/EyeDropper#browser_compatibility
     * @return bool
     */
	private function isBrowserEnabled()
    {
        $allowedBrowsers = [
            'Edge',
            'Chrome',
        ];

        $currentBrowser = Browser::getBrowser();

        return in_array($currentBrowser['ub'], $allowedBrowsers);
    }
}