<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;

class EmbedField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
	    $defaultValue = $this->defaultValue() ?? null;

        $field = "<input
		    ".$this->disabled()."
			id='".Strings::esc_attr($this->getIdName())."'
			name='".Strings::esc_attr($this->getIdName())."'
			placeholder='".$this->placeholder()."'
			value='".$defaultValue."'
			type='text'
			class='".$this->cssClass()."'
			".$this->required()."
			".$this->appendDataValidateAndConditionalRenderingAttributes()."
		/>";

        $field .= $this->getPreview($defaultValue);

		if($this->fieldModel->getMetaField() !== null){
			return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
		}

		return $field;
	}

    /**
     * @param null $defaultValue
     *
     * @return string
     */
    private function getPreview($defaultValue = null)
    {
        if(empty($defaultValue)){
            return null;
        }

        $preview = '<div class="acpt-embed-preview">';
        $preview .= '<div class="acpt-embed">';
        $preview .= (new \WP_Embed())->shortcode([
            'width' => 360,
            'height' => 270,
        ], Strings::esc_attr($defaultValue));
        $preview .= '</div>';
        $preview .= '</div>';

        return $preview;
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
