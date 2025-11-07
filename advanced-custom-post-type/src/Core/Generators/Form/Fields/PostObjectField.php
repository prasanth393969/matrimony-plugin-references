<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Posts;
use ACPT\Utils\Wordpress\Translator;

class PostObjectField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $empty = (!empty($this->fieldModel->getExtra()['empty'])) ? $this->fieldModel->getExtra()['empty'] : false;
        $value = $this->defaultValue();
        $posts = $this->postTypeList();
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

        if(is_array($posts)){
            foreach($posts as $element){
                $field .= '<optgroup label="'.$element['postType'].'">';

                foreach ($element['posts'] as $id => $post){
                    $selected = $this->isSelected($id, $value, $isMulti);
                    $field .= '<option '.$selected.' value="'.$id.'">'.esc_html($post).'</option>';
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
     * @param $id
     * @param $value
     * @param $isMulti
     * @return string
     */
    protected function isSelected($id, $value, $isMulti)
    {
        if($isMulti and is_array($value)){
            return (in_array($id, $value)) ? 'selected="selected"' : '';
        }

        return ($id === (int)$value) ? 'selected="selected"' : '';
    }

    /**
     * @return array
     */
    protected function postTypeList()
    {
        $postQuery = [];

        if($this->fieldModel->getMetaField() and $this->fieldModel->getMetaField()->getAdvancedOption('filter_post_type')){
            $postQuery['post_type'] = $this->fieldModel->getMetaField()->getAdvancedOption('filter_post_type');
        }

        if($this->fieldModel->getMetaField() and $this->fieldModel->getMetaField()->getAdvancedOption('filter_post_status')){
            $postQuery['post_status'] = $this->fieldModel->getMetaField()->getAdvancedOption('filter_post_status');
        }

        if($this->fieldModel->getMetaField() and $this->fieldModel->getMetaField()->getAdvancedOption('filter_taxonomy')){
            $postQuery['taxonomy'] = $this->fieldModel->getMetaField()->getAdvancedOption('filter_taxonomy');
        }

        return Posts::getList($postQuery);
    }

    /**
     * @return bool
     */
    private function isMulti()
    {
        return $this->fieldModel->getMetaField() !== null ? $this->fieldModel->getMetaField()->getType() === MetaFieldModel::POST_OBJECT_MULTI_TYPE : false;
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets() {
        // TODO: Implement enqueueFieldAssets() method.
    }
}
