<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class PasswordField extends AbstractField
{
    public function render()
    {
        $cssClass = 'regular-text acpt-admin-meta-field-input';

        if($this->hasErrors()){
            $cssClass .= ' has-errors';
        }

        if($this->isChild() or $this->isNestedInABlock()){

            if($this->isLeadingField()){
                $cssClass .= ' acpt-leading-field';
            }

            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::PASSWORD_TYPE.'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
            $text = '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'[value]" name="'. Strings::esc_attr($this->getIdName()).'[value]" type="password" class="'.$cssClass.'"';
        } else {
            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::PASSWORD_TYPE.'">';
            $text = '<input '.$this->required().' id="'.Strings::esc_attr($this->getIdName()).'" name="'. Strings::esc_attr($this->getIdName()).'" type="password" class="'.$cssClass.'"';
        }

        $text .= $this->appendDataValidateAndLogicAttributes();
        $text .= '>';

        $field .= (new AfterAndBeforeFieldGenerator())->generate($this->metaField, $text);

        return $this->renderField($field);
    }
}