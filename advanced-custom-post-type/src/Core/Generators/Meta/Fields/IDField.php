<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Id;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;

class IDField extends AbstractField
{
	public function render()
	{
        $idStrategy = $this->getAdvancedOption('id_strategy') ?? Id::UUID_V1;

		if($this->isChild() or $this->isNestedInABlock()){
			$id = "id_".Strings::generateRandomId();
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::ID_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';

			$field .= '<div class="acpt-before-and-after-wrapper">';
			$field .= "<div class='before'>".Id::formatStrategy($idStrategy)."</div>";
            $field .= '<input type="text" data-index="'.$this->index.'" readonly class="regular-text acpt-admin-meta-field-input acpt-id-value" id="'.$id.'" name="'. Strings::esc_attr($this->getIdName()).'[value]" value="'. $this->generateId($idStrategy) .'"/>';
            $field .= '</div>';
            $field .= '<a data-index="'.$this->index.'" data-id-strategy="'.$idStrategy.'" data-target-id="'. $id.'" href="#" class="acpt-id-generate">'.Translator::translate("Regenerate").'</a>';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::ID_TYPE.'">';
            $field .= '<div class="acpt-before-and-after-wrapper">';
            $field .= "<div class='before'>".Id::formatStrategy($idStrategy)."</div>";
			$field .= '<input type="text" data-index="'.$this->index.'" readonly class="regular-text acpt-admin-meta-field-input acpt-id-value" id="'.$this->getIdName().'" name="'. Strings::esc_attr($this->getIdName()).'" value="'. $this->generateId($idStrategy) .'"/>';
            $field .= '</div>';
            $field .= '<a href="#" data-index="'.$this->index.'" data-id-strategy="'.$idStrategy.'" data-target-id="'. $this->getIdName().'" class="acpt-id-generate">'.Translator::translate("Regenerate").'</a>';
		}

		return $this->renderField($field);
	}

    /**
     * @param string $strategy
     *
     * @return int|string|null
     * @throws \Exception
     */
    private function generateId($strategy = Id::UUID_V1)
    {
        $defaultValue = $this->getDefaultValue();

        if(empty($defaultValue)){
            return Id::generate($strategy, $this->index);
        }

        $isValid = Id::isValid($defaultValue, $strategy);

        if($isValid){
            return $defaultValue;
        }

        return Id::generate($strategy, $this->index);
    }
}