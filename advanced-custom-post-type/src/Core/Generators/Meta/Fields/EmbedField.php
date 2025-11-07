<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class EmbedField extends AbstractField
{
	public function render()
	{
		if($this->isChild() or $this->isNestedInABlock()){
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::EMBED_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
			$field .= '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" value="'.Strings::esc_attr($this->getDefaultValue()).'" type="url" class="acpt-form-control acpt-embed" '.$this->appendDataValidateAndLogicAttributes().'>';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::EMBED_TYPE.'">';
			$field .= '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'" name="'. Strings::esc_attr($this->getIdName()).'" type="url" class="acpt-form-control acpt-embed" '.$this->appendDataValidateAndLogicAttributes().' value="'.Strings::esc_attr($this->getDefaultValue()).'">';
		}

		$field .= $this->getPreview();

		return $this->renderField($field);
	}

	/**
	 * @return string
	 */
	private function getPreview()
	{
        $preview = '<div class="embed-preview">';

        if( !empty($this->getDefaultValue())){
            $preview .= '<div class="embed">';
            $preview .= (new \WP_Embed())->shortcode([
                'width' => 180,
                'height' => 135,
            ], Strings::esc_attr($this->getDefaultValue()));
            $preview .= '</div>';
        }

        $preview .= '</div>';

        return $preview;
	}
}