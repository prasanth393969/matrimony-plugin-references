<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;

class IconField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		return "
			 <div class='acpt-iconpicker-wrapper'>
		        <span id='".Strings::esc_attr($this->getIdName())."_target' class='acpt-selected-icon hidden'></span>
		        <input
					id='".Strings::esc_attr($this->getIdName())."_svg'
					name='".Strings::esc_attr($this->getIdName())."_svg'
					value=''
					type='hidden'
				/>
				<input
					id='".Strings::esc_attr($this->getIdName())."'
					name='".Strings::esc_attr($this->getIdName())."'
					placeholder='".$this->placeholder()."'
					value='".$this->defaultValue()."'
					type='text'
					class='acpt-iconpicker ".$this->cssClass()."'
					data-target='".Strings::esc_attr($this->getIdName())."'
				/>
			 </div>   
		";
	}

	public function enqueueFieldAssets()
	{
		wp_register_style('iconpicker-css',  plugins_url( 'advanced-custom-post-type/assets/vendor/iconpicker/iconpicker.theme.min.css') );
		wp_enqueue_style('iconpicker-css');

		wp_register_script('iconpicker-js',  plugins_url( 'advanced-custom-post-type/assets/vendor/iconpicker/iconpicker.min.js') );
		wp_enqueue_script('iconpicker-js');
	}
}
