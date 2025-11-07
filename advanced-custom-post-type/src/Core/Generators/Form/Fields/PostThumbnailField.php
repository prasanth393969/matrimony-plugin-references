<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Utils\Wordpress\WPAttachment;

class PostThumbnailField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$value = $this->defaultValue();

		$field = "
			<input
			    ".$this->disabled()."
				id='".Strings::esc_attr($this->getIdName())."'
				name='".Strings::esc_attr($this->getIdName())."'
				accept='image/*'
				placeholder='".$this->placeholder()."'
				type='file'
				class='".$this->cssClass()."'
				".$this->required()."
				".$this->appendDataValidateAndConditionalRenderingAttributes()."
			/>";

		if(empty($value)){
			return $field;
		}

        $wpAttachment = WPAttachment::fromUrl($value);

        if($wpAttachment->isEmpty()){
            return $field;
        }

		return "<div class='acpt-form-inline'>
            ".$wpAttachment->render([
                'class' => 'acpt-thumbnail'
            ])."			
			".$field."
			</div>";
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
