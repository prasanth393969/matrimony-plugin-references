<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\Users;

class UserField extends PostObjectField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $empty = (!empty($this->fieldModel->getExtra()['empty'])) ? $this->fieldModel->getExtra()['empty'] : false;
        $value = $this->defaultValue();
        $users = $this->userList();
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

        if(is_array($users)){
            foreach($users as $id => $user){
                $selected = $this->isSelected($id, $value, $isMulti);
                $field .= '<option '.$selected.' value="'.$id.'">'.esc_html($user).'</option>';
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
    protected function userList()
    {
        $userQuery = [];

        if($this->fieldModel->getMetaField() and $this->fieldModel->getMetaField()->getAdvancedOption('filter_role')){
            $userQuery['role'] = $this->fieldModel->getMetaField()->getAdvancedOption('filter_role');
        }

        return Users::getList($userQuery);
    }

    /**
     * @return bool
     */
    private function isMulti()
    {
        return $this->fieldModel->getMetaField() !== null ? $this->fieldModel->getMetaField()->getType() === MetaFieldModel::USER_MULTI_TYPE : false;
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets() {
        // TODO: Implement enqueueFieldAssets() method.
    }
}
