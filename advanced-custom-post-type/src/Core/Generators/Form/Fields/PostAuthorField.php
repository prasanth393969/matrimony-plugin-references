<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\Users;

class PostAuthorField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$empty = (!empty($this->fieldModel->getExtra()['empty'])) ? $this->fieldModel->getExtra()['empty'] : false;
		$value = $this->defaultValue();
		$users = Users::getList();

		$field = "<select
		    ".$this->disabled()."
			id='".Strings::esc_attr($this->getIdName())."'
			name='".Strings::esc_attr($this->getIdName())."'
			placeholder='".$this->placeholder()."'
			class='".$this->cssClass()."'
			".$this->required()."
			".$this->appendDataValidateAndConditionalRenderingAttributes()."
		>";

		if($empty){
			$field .= '
				<option value="">
			        '.Translator::translate("Select").'
				</option>';
		}

		foreach ($users as $id => $user){
			$field .= '
				<option 
			        value="'.Strings::esc_attr($id).'"
			        '.($id == $value ? "selected" : "").'
		        >
			        '.Strings::esc_attr($user).'
				</option>';
		}

		$field .= '</select>';

		return $field;
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
