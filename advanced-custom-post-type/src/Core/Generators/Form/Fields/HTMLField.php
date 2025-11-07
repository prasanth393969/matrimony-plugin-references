<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;

class HTMLField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$defaultValue = Strings::htmlspecialchars($this->defaultValue());
		$rows = (!empty($this->fieldModel->getExtra()['rows'])) ? $this->fieldModel->getExtra()['rows'] : 6;
		$cols = (!empty($this->fieldModel->getExtra()['cols'])) ? $this->fieldModel->getExtra()['cols'] : 30;

		$field = "
			<textarea
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				placeholder='".$this->placeholder()."'
				class='acpt-codemirror ".Strings::esc_attr($this->cssClass())."'
				rows='".$rows."'
				cols='".$cols."'
			>".$defaultValue."</textarea>";

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

	public function enqueueFieldAssets()
	{
		wp_register_style( 'codemirror-css', plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/codemirror5.min.css'), [], "5.65.16" );
		wp_enqueue_style( 'codemirror-css' );

		wp_register_script('codemirror-js',  plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/codemirror5.min.js') );
		wp_enqueue_script('codemirror-js');

		// Emmet
		wp_register_script('codemirror-browser-js',  plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/browser.js') );
		wp_enqueue_script('codemirror-browser-js');

		wp_register_script('codemirror-htmlmixed-js',  plugins_url( 'advanced-custom-post-type/assets/vendor/codemirror/mode/htmlmixed/htmlmixed.min.js') );
		wp_enqueue_script('codemirror-htmlmixed-js');
	}
}
