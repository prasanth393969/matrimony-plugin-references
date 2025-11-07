<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Terms;
use ACPT\Utils\Wordpress\Translator;

class TermObjectField extends PostObjectField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $empty = (!empty($this->fieldModel->getExtra()['empty'])) ? $this->fieldModel->getExtra()['empty'] : false;
        $value = $this->defaultValue();
        $terms = $this->termList();
        $isMulti = $this->isMulti();

        $multiple = '';
        $fieldName = Strings::esc_attr($this->getIdName());

        if($isMulti){
            $fieldName .= "[]";
            $multiple = "multiple";
        }

        $field = "<select
		    ".$this->disabled()."
			".$multiple."
			id='".Strings::esc_attr($this->getIdName())."'
			name='".$fieldName."'
			placeholder='".$this->placeholder()."'
			class='".$this->cssClass()."'
			".$this->required()."
		>";

        if($empty){
            $field .= '
				<option value="">
			        '.Translator::translate("Select").'
				</option>';
        }

        if(is_array($terms)){
            foreach($terms as $element){
                $field .= '<optgroup label="'.$element['taxonomy'].'">';

                foreach ($element['terms'] as $id => $term){
                    $selected = $this->isSelected($id, $value, $isMulti);
                    $field .= '<option '.$selected.' value="'.$id.'">'.esc_html($term).'</option>';
                }

                $field .= '</optgroup>';
            }
        }

        $field .= '</select>';

        if($this->fieldModel->getMetaField() !== null){
            return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
        }

        return $field;
    }

    /**
     * @return array
     */
    protected function termList()
    {
        $termQuery = [];

        if($this->fieldModel->getMetaField() and $this->fieldModel->getMetaField()->getAdvancedOption('filter_taxonomy')){
            $termQuery['taxonomy'] = $this->fieldModel->getMetaField()->getAdvancedOption('filter_taxonomy');
        }

        return Terms::getList($termQuery);
    }

    /**
     * @return bool
     */
    private function isMulti()
    {
        return $this->fieldModel->getMetaField() !== null ? $this->fieldModel->getMetaField()->getType() === MetaFieldModel::TERM_OBJECT_MULTI_TYPE : false;
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets() {
        // TODO: Implement enqueueFieldAssets() method.
    }
}
