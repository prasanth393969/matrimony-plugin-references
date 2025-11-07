<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class TermObjectMultiField extends PostField
{
	public function render()
	{
		if($this->isChild() or $this->isNestedInABlock()){
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::TERM_OBJECT_MULTI_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::TERM_OBJECT_MULTI_TYPE.'">';
		}

        $args = [];
        $fieldName = Strings::esc_attr($this->getIdName());
        $isMulti = 'multiple';
        $defaultValue = $this->getDefaultValue();
        $layout = $this->getAdvancedOption('layout');

        if($this->getAdvancedOption('filter_taxonomy')){
            $args['term_taxonomy'] = $this->getAdvancedOption('filter_taxonomy');
        }

        $minimumBlocks = $this->getAdvancedOption('minimum_blocks') ?? null;
        $maximumBlocks = $this->getAdvancedOption('maximum_blocks') ?? null;

        if($minimumBlocks){
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_min_blocks" value="'.$minimumBlocks.'">';
        }

        if($maximumBlocks){
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_max_blocks" value="'.$maximumBlocks.'">';
        }

        $field .= $this->renderRelationFieldSelector($isMulti, MetaFieldModel::TERM_OBJECT_MULTI_TYPE, $fieldName, $args, $defaultValue, $layout, false, $minimumBlocks, $maximumBlocks);

        return $this->renderField($field);
	}
}