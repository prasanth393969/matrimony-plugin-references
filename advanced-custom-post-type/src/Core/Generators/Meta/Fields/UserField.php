<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\Users;

class UserField extends PostField
{
	public function render()
	{
		if($this->isChild() or $this->isNestedInABlock()){
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::USER_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::USER_TYPE.'">';
		}

        $args = [];
        $fieldName = Strings::esc_attr($this->getIdName());
        $isMulti = '';
        $defaultValue = $this->getDefaultValue();
        $layout = $this->getAdvancedOption('layout');

        if($this->getAdvancedOption('filter_role')){
            $args['user_role'] = $this->getAdvancedOption('filter_role');
        }

        $field .= $this->renderRelationFieldSelector($isMulti, MetaFieldModel::USER_TYPE, $fieldName, $args, $defaultValue, $layout, false);

		return $this->renderField($field);
	}
}