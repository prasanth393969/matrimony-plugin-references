<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class HTMLField extends AbstractField
{
	public function render()
	{
		$this->enqueueAssets();

		if($this->isChild() or $this->isNestedInABlock()){
			$id = "html_".Strings::generateRandomId();
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::HTML_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$html = '<textarea '.$this->required().' id="'.$id.'" name="'. Strings::esc_attr($this->getIdName()).'[value]" class="acpt-admin-meta-field-input acpt-codemirror" rows="8" '.$this->appendDataValidateAndLogicAttributes().'>'.Strings::esc_attr($this->getDefaultValue())
			          .'</textarea>';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::HTML_TYPE.'">';
			$html = '<textarea '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'" name="'. Strings::esc_attr($this->getIdName()).'" class="acpt-form-control acpt-codemirror" rows="8" '.$this->appendDataValidateAndLogicAttributes().'>'.Strings::esc_attr($this->getDefaultValue()).'</textarea>';
		}

		$field .= (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $html);

		return $this->renderField($field);
	}

	public function enqueueAssets()
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