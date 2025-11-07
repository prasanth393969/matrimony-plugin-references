<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\RelationCostants;
use ACPT\Core\Generators\Meta\AfterAndBeforeFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldRelationshipModel;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\Users;

class PostField extends PostObjectField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        if(empty($this->fieldModel->getMetaField())){
            return null;
        }

        if(empty($this->fieldModel->getMetaField()->getRelations())){
            return null;
        }

        $empty = (!empty($this->fieldModel->getExtra()['empty'])) ? $this->fieldModel->getExtra()['empty'] : false;
        $relation = $this->fieldModel->getMetaField()->getRelations()[0];
        $value = $this->defaultValue();
        $options =  $this->getOptions($relation);
        $isMulti = $this->isMulti();

        $multiple = '';
        $fieldName = Strings::esc_attr($this->getIdName());

        if($isMulti){
            $fieldName .= "[]";
            $multiple = "multiple";
        }

        $field  = $this->inversedHiddenInputs($relation);
        $field .= "<select
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

        if(is_array($options)){
            foreach($options as $id => $option){
                $selected = $this->isSelected($option['value'], $value, $isMulti);
                $field .= '<option '.$selected.' value="'.$option['value'].'">'.esc_html($option['label']).'</option>';
            }
        }

        $field .= '</select>';

        if($this->fieldModel->getMetaField() !== null){
            return (new AfterAndBeforeFieldGenerator())->generate($this->fieldModel->getMetaField(), $field);
        }

        return $field;
    }

    /**
     * @param MetaFieldRelationshipModel $relationshipModel
     *
     * @return string
     */
    private function inversedHiddenInputs(MetaFieldRelationshipModel $relationshipModel)
    {
        $field = '';

        if($relationshipModel->getInversedBy() !== null){
            $inversedBy = $relationshipModel->getInversedBy();
            $inversedIdName = $this->getInversedIdName($inversedBy->getBox()->getName(), $inversedBy->getName());
            $defaultValues = $this->defaultValue();
            $defaultValues = (is_array($defaultValues)) ? implode(',', $defaultValues) : $defaultValues;

            $field .= '<input type="hidden" name="meta_fields[]" value="'. Strings::esc_attr($inversedIdName).RelationCostants::RELATION_KEY.'">';
            $field .= '<input type="hidden" id="inversedBy" name="'. Strings::esc_attr($inversedIdName).RelationCostants::RELATION_KEY.'" value="'.Strings::esc_attr($defaultValues).'">';
            $field .= '<input type="hidden" id="inversedBy_original_values" name="'. Strings::esc_attr($inversedIdName).RelationCostants::RELATION_KEY.'_original_values" value="'.Strings::esc_attr($defaultValues).'">';
        }

        return $field;
    }

    /**
     * @param $box
     * @param $field
     *
     * @return string
     */
    private function getInversedIdName($box, $field)
    {
        return Strings::toDBFormat($box) . RelationCostants::SEPARATOR . Strings::toDBFormat($field);
    }

    /**
     * @return bool
     */
    private function isMulti()
    {
        return $this->fieldModel->getMetaField() !== null ? $this->fieldModel->getMetaField()->getRelations()[0]->isMany() : false;
    }

    /**
     * @param MetaFieldRelationshipModel $relationshipModel
     *
     * @return array
     * @throws \Exception
     */
    private function getOptions(MetaFieldRelationshipModel $relationshipModel)
    {
        $to = $relationshipModel->to();

        switch($to->getType()){

            case MetaTypes::MEDIA:
            case MetaTypes::CUSTOM_POST_TYPE:

                $posts = [];
                $data = get_posts([
                    'exclude'        => [get_the_id()],
                    'post_type'      => $to->getValue(),
                    'posts_per_page' => -1,
                ]);

                foreach ($data as $post){
                    $posts[] = [
                        'value' => $post->ID,
                        'label' => $post->post_title,
                    ];
                }

                return $posts;

            case MetaTypes::TAXONOMY:

                $terms = [];
                $data = $categoryIds = get_terms([
                    'taxonomy'   => $to->getValue(),
                    'hide_empty' => false,
                ]);

                foreach ($data as $term){
                    $terms[] = [
                        'value' => $term->term_id,
                        'label' => $term->name,
                    ];
                }

                return $terms;

            case MetaTypes::OPTION_PAGE:

                $pages = [];
                $data = OptionPageRepository::get([]);

                foreach ($data as $page){
                    $pages[] = [
                        'label' => $page->getMenuTitle(),
                        'value' => $page->getId(),
                    ];

                    foreach ($page->getChildren() as $child){
                        $pages[] = [
                            'label' => $child->getMenuTitle(),
                            'value' => $child->getId(),
                        ];
                    }
                }

                return $pages;

            case MetaTypes::USER:

                $users = [];

                foreach(Users::getList() as $id => $user){
                    $users[] = [
                        'label' => $user,
                        'value' => $id
                    ];
                }

                return $users;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets()
    {
        // TODO: Implement enqueueFieldAssets() method.
    }
}