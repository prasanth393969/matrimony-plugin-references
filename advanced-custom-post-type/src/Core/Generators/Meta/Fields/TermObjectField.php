<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class TermObjectField extends PostField
{
	public function render()
	{
		if($this->isChild() or $this->isNestedInABlock()){
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::TERM_OBJECT_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::TERM_OBJECT_TYPE.'">';
		}

        $args = [];
        $fieldName = Strings::esc_attr($this->getIdName());
        $isMulti = '';
        $defaultValue = $this->getDefaultValue();
        $layout = $this->getAdvancedOption('layout');

        if($this->getAdvancedOption('filter_taxonomy')){
            $args['term_taxonomy'] = $this->getAdvancedOption('filter_taxonomy');
        }

        $field .= $this->renderRelationFieldSelector($isMulti, MetaFieldModel::TERM_OBJECT_TYPE, $fieldName, $args, $defaultValue, $layout, false);

		return $this->renderField($field);
	}
}