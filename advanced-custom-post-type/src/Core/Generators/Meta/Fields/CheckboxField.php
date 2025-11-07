<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaFieldOptionModel;
use ACPT\Utils\Wordpress\Translator;

class CheckboxField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$cssClass = '';
		$isNested = $this->isChild() or $this->isNestedInABlock();
        $display = $this->getAdvancedOption('display') !== null ? $this->getAdvancedOption('display') : 'block';

		if($isNested){

			if($this->isLeadingField()){
				$cssClass = ' acpt-leading-field';
			}

			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::CHECKBOX_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::CHECKBOX_TYPE.'">';
		}

		$selectedOptions = $this->selectedOptions($this->metaField->getOptions(), true);
		$field .= '<div class="acpt_checkboxes '.$display.'">';

		foreach ($this->metaField->getOptions() as $index => $option){
			$id = Strings::esc_attr($this->getIdName()).'_'.$index;
			$selected = (in_array($option->getValue(), $selectedOptions)) ? 'checked="checked"' : '';
			$field .= $this->renderCheckboxItem($id, $option, $selected, $cssClass, $isNested);
		}

		$field .= '</div>';

		return $this->renderField($field);
	}

	/**
	 * @param $id
	 * @param MetaFieldOptionModel $option
	 * @param $selected
	 * @param $cssClass
	 * @param $isNested
	 *
	 * @return string
	 */
	private function renderCheckboxItem($id, MetaFieldOptionModel $option, $selected, $cssClass, $isNested)
	{
		$name = ($isNested) ? Strings::esc_attr($this->getIdName()).'[value][]' : Strings::esc_attr($this->getIdName()).'[]';

		return '<div class="item">
					<input class="'.$cssClass.'" name="'.$name.'" id="'.$id.'" type="checkbox" '.$selected.' value="'.Strings::esc_attr(Translator::translateString($option->getValue())).'" '.$this->appendDataValidateAndLogicAttributes().' />
					<label for="'.$id.'">'. $this->renderItemLabel($option->getLabel()) . '</label>
				</div>';
	}

	/**
	 * @param $label
	 *
	 * @return string
	 */
	protected function renderItemLabel($label)
	{
		$after = $this->getAdvancedOption('after');
		$before = $this->getAdvancedOption('before');
		$label = esc_html(Translator::translateString($label));

		if(empty($after) and empty($before)){
			return "<span class='label'>" . $label . "</span>";
		}

		$return = '';

		if(!empty($before) ){
			$return .= "<span class='before'>" . Strings::htmlspecialchars($before) . "</span>";
		}

		$return .= "<span class='label'>" . $label . "</span>";

		if(!empty($after) ){
			$return .= "<span class='after'>" . Strings::htmlspecialchars($after) . "</span>";
		}

		return $return;
	}
}