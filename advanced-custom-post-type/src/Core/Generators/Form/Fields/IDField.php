<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Id;
use ACPT\Core\Helper\Strings;
use ACPT\Utils\Wordpress\Translator;

class IDField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
        if($this->fieldModel->getMetaField() === null) {
            return null;
        }

        $idStrategy = $this->fieldModel->getMetaField()->getAdvancedOption('id_strategy') ?? Id::UUID_V1;
        $field = '<div class="acpt-before-and-after-wrapper">';
        $field .= "<div class='before'>".Id::formatStrategy($idStrategy)."</div>";

        $field .= "<input
		    ".$this->disabled()."
		    readonly
			id='".Strings::esc_attr($this->getIdName())."'
			name='".Strings::esc_attr($this->getIdName())."'
			placeholder='".$this->placeholder()."'
			value='".$this->generateId($idStrategy)."'
			type='text'
			class='acpt-id-value ".$this->cssClass()."'
			".$this->required()."
			".$this->appendDataValidateAndConditionalRenderingAttributes()."
		/>";
        $field .= '</div>';
        $field .= '<a href="#" data-index="'.$this->index.'" data-id-strategy="'.$idStrategy.'" data-target-id="'. $this->getIdName().'" class="acpt-id-generate">'.Translator::translate("Regenerate").'</a>';

		return $field;
	}

    /**
     * @param string $strategy
     *
     * @return int|string|null
     */
    private function generateId($strategy = Id::UUID_V1)
    {
        try {
            $defaultValue = $this->defaultValue();

            if(empty($defaultValue)){
                return Id::generate($strategy, $this->index);
            }

            $isValid = Id::isValid($defaultValue, $strategy);

            if($isValid){
                return $defaultValue;
            }

            return Id::generate($strategy, $this->index);
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return null;
        }
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
