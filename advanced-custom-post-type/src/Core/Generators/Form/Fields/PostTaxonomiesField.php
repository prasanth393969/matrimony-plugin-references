<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Constants\TaxonomyField;
use ACPT\Core\Helper\Strings;
use ACPT\Utils\Wordpress\Terms;
use ACPT\Utils\Wordpress\Translator;

class PostTaxonomiesField extends AbstractField
{
	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$selector = (!empty($this->fieldModel->getExtra()['selector'])) ? $this->fieldModel->getExtra()['selector'] : "select";
		$empty = (!empty($this->fieldModel->getExtra()['empty'])) ? $this->fieldModel->getExtra()['empty'] : false;
		$options = Terms::getForPostType($this->fieldModel->getFind());
		$isMulti = (!empty($this->fieldModel->getExtra()['isMulti'])) ? $this->fieldModel->getExtra()['isMulti'] : false;
		$name = Strings::esc_attr($this->getIdName());

		if($selector === "checkbox-radio"){
		    return $this->renderCheckboxes($name, $isMulti, $empty, $options);
        }

		return $this->renderSelect($name, $isMulti, $empty, $options);
	}

    /**
     * @param $name
     * @param $isMulti
     * @param $empty
     * @param $options
     *
     * @return string
     */
    private function renderCheckboxes($name, $isMulti, $empty, $options)
    {
        $field = "<div class='acpt-term-selector-wrapper'>";
        $name = $isMulti ? $name."[]" : $name;
        $inputType = $isMulti ? "checkbox" : "radio";

        foreach ($options as $taxonomy => $terms){
            $field .= "<fieldset class='acpt-term-selector'>";
            $field .= "<legend>".$taxonomy."</legend>";
            $field .= "<ul class='acpt-term-selector-list'>";

            if(!$isMulti and $empty){
                $fieldId = Strings::generateRandomId();
                $field .= '<li>';
                $field .= '<input id="'.$fieldId.'" type="'.$inputType.'" name="'.$name.'" value="" />';
                $field .= '<label for="'.$fieldId.'">'.Translator::translate("Select").'</label>';
                $field .= '</li>';
            }

            $savedTerms = $this->savedTerms($taxonomy);

            foreach ($terms as $id => $term){

                $realId = explode(TaxonomyField::SEPARATOR, $id);
                $fieldId = Strings::generateRandomId();

                $field .= '<li>';
                $field .= '<input '.(in_array($realId[1], $savedTerms) ? "checked" : "").' id="'.$fieldId.'" type="'.$inputType.'" name="'.$name.'" value="'.Strings::esc_attr($id).'" />';
                $field .= '<label for="'.$fieldId.'">'.Strings::esc_attr($term).'</label>';
                $field .= '</li>';
            }

            $field .= "</ul>";
            $field .= "</fieldset>";
        }

        $field .= "</div>";

        return $field;
    }

    /**
     * @param $name
     * @param $isMulti
     * @param $empty
     * @param $options
     *
     * @return string
     */
	private function renderSelect($name, $isMulti, $empty, $options)
    {
        $field = "<select
		    ".$this->disabled()."
			".($isMulti ? "multiple" : "")."
			id='".Strings::esc_attr($this->getIdName())."'
			name='".$name."'
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

        foreach ($options as $taxonomy => $terms){

            $savedTerms = $this->savedTerms($taxonomy);
            $field .= '<optgroup label="'.$taxonomy.'">';

            foreach ($terms as $id => $term){

                $realId = explode(TaxonomyField::SEPARATOR, $id);

                $field .= '
					<option
				        value="'.Strings::esc_attr($id).'"
				        '.(in_array($realId[1], $savedTerms) ? "selected" : "").'
			        >
				        '.Strings::esc_attr($term).'
					</option>';
            }

            $field .= '</optgroup>';
        }

        $field .= '</select>';

        return $field;
    }

    /**
     * @param $taxonomy
     *
     * @return array
     */
    private function savedTerms($taxonomy)
    {
        $savedTerms = [];
        $postId = $this->postId;

        if($postId === null and (is_single() or is_page())){
            $page = get_post();
            $postId = $page->ID;
        }

        if($postId !== null){
            $postTerms = get_the_terms($postId, $taxonomy);

            if(is_array($postTerms)){
                foreach ($postTerms as $t){
                    if($t instanceof \WP_Term){
                        $savedTerms[] = $t->term_id;
                    }
                }
            }
        }

        return $savedTerms;
    }

	/**
	 * @inheritDoc
	 */
	public function enqueueFieldAssets() {
		// TODO: Implement enqueueFieldAssets() method.
	}
}
