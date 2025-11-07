<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Utils\Wordpress\Translator;

class ListField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
        $id = Strings::generateRandomId();
        $defaultValue = $this->defaultValue();

	    $field = "<div class='acpt-form-messages'>";
	    $field .= "<div id='".$id."' class='acpt-list-elements acpt-form-messages'>";

        if(is_array($defaultValue) and !empty($defaultValue)){
            foreach ($defaultValue as $i => $value){
                $field .= "
                    <div id='" . Strings::esc_attr( $this->getIdName() ) . "_" . $i . "' class='acpt-list-element acpt-form-inline'>
                        <input
                            id='".Strings::esc_attr($this->getIdName())."_".$i."'
                            name='".Strings::esc_attr($this->getIdName())."[]'
                            placeholder='".$this->placeholder()."'
                            value='".$value."'
                            type='text'
                            class='".$this->cssClass()."'
                        />
                        <a 
                            class='list-remove-element' 
                            data-target-id='" . Strings::esc_attr( $this->getIdName() ) . "_" . $i . "' 
                            href='#'
                            title='" . Translator::translate( 'Remove element' ) . "'
                        >
                            <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"18\" height=\"18\" viewBox=\"0 0 24 24\">
                                <path d=\"M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z\"></path><path d=\"M9 10h2v8H9zm4 0h2v8h-2z\"></path>
                            </svg>
                        </a>
                    </div>
                ";
            }
        }

        $field .= "</div>";
        $field .= '<a 
					class="list-add-element button small" 
					data-target-id="'.$id.'"
					data-parent-name="'.$this->getIdName().'"
					href="#"
				>
					' . Translator::translate( 'Add element' ) . '
				</a>';

        $field .= "</div>";

		return $field;
	}

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets()
    {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
