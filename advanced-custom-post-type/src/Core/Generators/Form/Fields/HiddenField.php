<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;

class HiddenField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		return "
			<input
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				value='".$this->defaultValue()."'
				type='hidden'
			/>";
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
