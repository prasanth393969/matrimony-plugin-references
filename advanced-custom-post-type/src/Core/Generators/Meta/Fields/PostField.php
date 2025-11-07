<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\RelationCostants;
use ACPT\Constants\Relationships;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaFieldRelationshipModel;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\Translator;
use function Twig\Extension\length;

class PostField extends AbstractField
{
	/**
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function render()
	{
		if(empty($this->metaField->getRelations())){
			return '<p data-message-id="'.$this->metaField->getId().'" class="update-nag notice notice-warning inline no-records">'.Translator::translate("No relation set on this field.").'</p>';
		}

		$relation = $this->metaField->getRelations()[0];
		$errors = $this->checkIfTheFieldCanBeRendered($relation);

		if(!empty($errors)){
			return '<p data-message-id="'.$this->metaField->getId().'" class="update-nag notice notice-warning inline no-records">'.Translator::translate($errors).'</p>';
		}

		if($this->isChild() or $this->isNestedInABlock()){
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::POST_TYPE.'">';
			$field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
		} else {
			$field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::POST_TYPE.'">';
		}

		$fieldName = Strings::esc_attr($this->getIdName());
		$isMulti = $this->isMulti($relation->getRelationship()) ? 'multiple' : '';
		$defaultValue = $this->getDefaultValue();
		$layout = $this->getAdvancedOption('layout');

		$args = [
            'toType' => $relation->to()->getType(),
            'toValue' => $relation->to()->getValue(),
        ];

        $addPostLink = $relation->to()->getType() === MetaTypes::CUSTOM_POST_TYPE;

		$field .= $this->inversedHiddenInputs($relation);

        $minimumBlocks = $this->getAdvancedOption('minimum_blocks') ?? null;
        $maximumBlocks = $this->getAdvancedOption('maximum_blocks') ?? null;

        if($minimumBlocks){
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_min_blocks" value="'.$minimumBlocks.'">';
        }

        if($maximumBlocks){
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_max_blocks" value="'.$maximumBlocks.'">';
        }

		$field .= $this->renderRelationFieldSelector($isMulti, MetaFieldModel::POST_TYPE, $fieldName, $args, $defaultValue, $layout, $addPostLink, $minimumBlocks, $maximumBlocks);

		return $this->renderField($field);
	}

	/**
	 * @param MetaFieldRelationshipModel $relationshipModel
	 *
	 * @return string|null
	 */
	private function checkIfTheFieldCanBeRendered(MetaFieldRelationshipModel $relationshipModel)
	{
        $pagenow = Url::pagenow();

		$from = $relationshipModel->from();

		switch ($pagenow){

			case "user-edit.php":
				if($from->getType() !== MetaTypes::USER){
					return 'From entity is not an user';
				}
				break;

			case "admin.php":
				if($from->getType() !== MetaTypes::OPTION_PAGE){
					return 'From entity is not an option page';
				}
				break;

			case "post.php":
				$postType = isset($_GET['post']) ? get_post_type($_GET['post']) : 'post';

				if($from->getType() !== MetaTypes::CUSTOM_POST_TYPE){
					return 'From entity is not a custom post type';
				}

				if($from->getValue() !== $postType){
					return 'From entity is not valid';
				}

				break;

			case "post-new.php":
				$postType = $_GET['post_type'] ?? 'post';

				if($from->getType() !== MetaTypes::CUSTOM_POST_TYPE){
					return 'From entity is not a custom post type';
				}

				if($from->getValue() !== $postType){
					return 'From entity is not valid';
				}

				break;

			case "edit-tags.php":
			case "term.php":
				$taxonomy = $_GET['taxonomy'];

				if($from->getType() !== MetaTypes::TAXONOMY){
					return 'From entity is not a taxonomy';
				}

				if($from->getValue() !== $taxonomy){
					return 'From entity is not valid';
				}

				break;
		}

		return null;
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
			$defaultValues = $this->getDefaultValue();
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
	 * @param string $relationship
	 *
	 * @return bool
	 */
	private function isMulti($relationship)
	{
		return (
			$relationship === Relationships::ONE_TO_MANY_UNI or
			$relationship === Relationships::ONE_TO_MANY_BI or
			$relationship === Relationships::MANY_TO_MANY_UNI or
			$relationship === Relationships::MANY_TO_MANY_BI
		);
	}

    /**
     * @param string   $isMulti
     * @param string   $fieldType
     * @param string   $fieldName
     * @param array    $args
     * @param null     $defaultValue
     * @param null     $layout
     * @param bool     $addPostLink
     * @param int|null $min
     * @param int|null $max
     *
     * @return string
     */
	protected function renderRelationFieldSelector( string $isMulti, string $fieldType, string $fieldName, array $args = [], $defaultValue = null, $layout = null, $addPostLink = false, ?int $min = null, ?int $max = null)
	{
		$id = "relational_".Strings::generateRandomId();

		if(is_array($defaultValue)){
            $defaultValue = implode(",", $defaultValue);
        }

        $selector = $this->getAdvancedOption('selector') ?? "advanced"; // advanced, select2

        if($selector === "select2"){
            return $this->renderSelect2($isMulti, $fieldType, $fieldName, $args, $defaultValue, $layout, $addPostLink, $min, $max);
        }

        $this->enqueueAssets();

		$defaultValueAsString = $defaultValue ?? '';

		$toType = $args['toType'] ?? null;
		$toValue = $args['toValue'] ?? null;
		$postType = $args['post_type'] ?? null;
		$postStatus = $args['post_status'] ?? null;
		$postTaxonomy = $args['post_taxonomy'] ?? null;
		$termTaxonomy = $args['term_taxonomy'] ?? null;
		$userRole = (isset($args['user_role']) and !empty($args['user_role'])) ? implode(",", $args['user_role']) : null;

		$return = '<div 
            id="'.$id.'" 
            class="acpt-relation-field-selector" 
            data-field-type="'.$fieldType.'" 
            data-default-values="'.$defaultValueAsString.'"
            data-layout="'.$layout.'"';

		if($toType !== null){
            $return .= ' data-to-type="'.$toType.'"';
        }

        if($toValue !== null){
            $return .= ' data-to-value="'.$toValue.'"';
        }

        if($postType !== null){
            $return .= ' data-post-type="'.$postType.'"';
        }

        if($postStatus !== null){
            $return .= ' data-post-status="'.$postStatus.'"';
        }

        if($postTaxonomy !== null){
            $return .= ' data-post-taxonomy="'.$postTaxonomy.'"';
        }

        if($termTaxonomy !== null){
            $return .= ' data-term-taxonomy="'.$termTaxonomy.'"';
        }

        if($userRole !== null){
            $return .= ' data-user-role="'.$userRole.'"';
        }

        $return .= ">";

		if($this->isChild() or $this->isNestedInABlock()){
			$return .= '<input type="hidden" '.$this->appendDataValidateAndLogicAttributes().' id="values_'.$id.'" name="'. Strings::esc_attr($fieldName).'[value]" value="'.$defaultValueAsString.'"/>';
		} else {
			$return .= '<input type="hidden" '.$this->appendDataValidateAndLogicAttributes().' id="values_'.$id.'" name="'. Strings::esc_attr($fieldName).'" value="'.$defaultValueAsString.'"/>';
		}

		// options
		$return .= '<div class="options">';
		$return .= '<div class="search">';
		$return .= '<input type="text" disabled="disabled" id="search_'.$id.'" class="search-input acpt-form-control" placeholder="search items" />';
		$return .= '</div>';

		$minValue = $min ?? '';
		$maxValue = !$isMulti ? 1 : $max ?? 9999999999;

		if(is_array($defaultValue)){
            $defaultValue = implode(",",$defaultValue);
        }

        $defaultValueCount = empty($defaultValue) ? 0 : count(explode(",",$defaultValue));

        $notAllowed = $maxValue <= $defaultValueCount ? "not-allowed" : "";

		$return .= '<div class="values '. $notAllowed .'" data-min="'.$minValue.'" data-max="'.$maxValue.'" id="options_'.$id.'">';
        $return .= "<div class='loading'>Loading...</div>";
		$return .= '</div>';

		// Add post
        if($addPostLink){
            $return .= $this->addPostLink($id, $toValue ?? $postType ?? null);
        }

		$return .= '</div>';

		// selected items
		$return .= '<div class="selected-items">';
		$return .= '<div class="title" id="title_'.$id.'">';
		$return .= '<h4>'.Translator::translate('Selected items').'</h4>';

		$return .= '</div>';
		$return .= '<ul class="acpt-sortable values" id="selected_items_'.$id.'">';
        $return .= "<li class='loading'>Loading...</li>";
		$return .= '</ul>';
		$return .= '</div>';
		$return .= '</div>';

		if($min !== null or $max !== null){
            $return .= '<div class="acpt-min-max-counts float-right">';

            if($min !== null){
                $return .= '<span class="min">'.Translator::translate('Min items required').' <span class="count">'.$min.'</span></span>';
            }

            if($max !== null){
                $return .= '<span class="max">'.Translator::translate('Max items allowed').' <span class="count">'.$max.'</span></span>';
            }

            $return .= '</div>';
		}

        return $return;
	}

    /**
     * @param      $id
     * @param null $postType
     *
     * @return string
     */
	private function addPostLink($id, $postType = null)
    {
        if (!current_user_can('publish_posts')) {
            return '';
        }

        $return = '';

        $return .= '<div class="add-post-wrapper">';
        $return .= '<a class="acpt-modal-link add-post" href="#acpt-create-post-'.$id.'" rel="modal:open">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" style="fill: currentColor;transform: ;msFilter:;"><path d="M13 7h-2v4H7v2h4v4h2v-4h4v-2h-4z"></path><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path></svg>
            <span>'.Translator::translate("Add new post a link it").'</span>
        </a>';

        // modal
        $return .= '<div id="acpt-create-post-'.$id.'" class="acpt-create-post-modal modal">';
        $return .= '<h3>'.Translator::translate("Create a new post").'</h3>';

        $idField = Strings::generateRandomId();
        $return .= "<label class='acpt-form-label' for='".$idField."'>The post title</label>";
        $return .= '<input type="text" id="'.$idField.'" class="post-name acpt-form-control" style="margin-bottom: var(--acpt-spacing);" placeholder="Write the post title here..." />';

        if(!$postType){
            $idField = Strings::generateRandomId();
            $return .= "<label class='acpt-form-label' for='".$idField."'>Choose che post type</label>";
            $return .= "<select  id='".$idField."' class='acpt-form-control' style='margin-bottom: var(--acpt-spacing);'>";

            $postTypes = get_post_types([
                'public'  => true,
                'show_ui' => true,
            ]);

            foreach ($postTypes as $postType){
                $return .= "<option value='".$postType."'>".$postType."</option>";
            }

            $return .= "</select>";
        }

        $return .= '<button disabled class="acpt-modal-link button button-primary" 
                        data-field-id="'.$this->metaField->getId().'" 
                        data-entity-type="'.MetaTypes::CUSTOM_POST_TYPE.'" 
                        data-entity-value="'.$postType.'" 
                        data-entity-id="'.$this->find.'">
                            '.Translator::translate("Create").'
                        </button>';
        $return .= '</div>';

        $return .= '</div>';


        return $return;
    }

    /**
     * @param string   $isMulti
     * @param string   $fieldType
     * @param string   $fieldName
     * @param array    $args
     * @param null     $defaultValue
     * @param null     $layout
     * @param bool     $addPostLink
     * @param int|null $min
     * @param int|null $max
     *
     * @return string
     */
    private function renderSelect2( string $isMulti, string $fieldType, string $fieldName, array $args = [], $defaultValue = null, $layout = null, $addPostLink = false, ?int $min = null, ?int $max = null)
    {
        $id = "relational_".Strings::generateRandomId();
        $multiple = $isMulti ? "multiple" : "";

        $fieldName = Strings::esc_attr($this->getIdName());

        if($this->isChild() or $this->isNestedInABlock()){
            $fieldName .= "[value]";
        }

        if($isMulti){
            $fieldName .= "[]";
        }

        $defaultValueAsString = $defaultValue ?? '';

        $toType = $args['toType'] ?? null;
        $toValue = $args['toValue'] ?? null;
        $postType = $args['post_type'] ?? null;
        $postStatus = $args['post_status'] ?? null;
        $postTaxonomy = $args['post_taxonomy'] ?? null;
        $termTaxonomy = $args['term_taxonomy'] ?? null;
        $userRole = (isset($args['user_role']) and !empty($args['user_role'])) ? implode(",", $args['user_role']) : null;

        $field = '<select 
            '.$multiple.'
            '.$this->required().'
            id="'.$id.'" 
            name="'. $fieldName.'" 
            '.$this->appendDataValidateAndLogicAttributes() . ' 
            class="acpt-select2 acpt-select2-ajax regular-text" 
            data-max="'.$max.'" 
            data-min="'.$min.'"
            data-field-type="'.$fieldType.'" 
            data-default-values="'.$defaultValueAsString.'"
            data-layout="'.$layout.'"';

        if($toType !== null){
            $field .= ' data-to-type="'.$toType.'"';
        }

        if($toValue !== null){
            $field .= ' data-to-value="'.$toValue.'"';
        }

        if($postType !== null){
            $field .= ' data-post-type="'.$postType.'"';
        }

        if($postStatus !== null){
            $field .= ' data-post-status="'.$postStatus.'"';
        }

        if($postTaxonomy !== null){
            $field .= ' data-post-taxonomy="'.$postTaxonomy.'"';
        }

        if($termTaxonomy !== null){
            $field .= ' data-term-taxonomy="'.$termTaxonomy.'"';
        }

        if($userRole !== null){
            $field .= ' data-user-role="'.$userRole.'"';
        }

        $field .= '></select>';
        $field .= "<div class='acpt-placeholder'>Loading...</div>";

        if($min !== null or $max !== null){
            $field .= '<div class="acpt-min-max-counts" style="margin-top: var(--acpt-spacing);">';

            if($min !== null){
                $field .= '<span class="min">'.Translator::translate('Min items required').' <span class="count">'.$min.'</span></span>';
            }

            if($max !== null){
                $field .= '<span class="max">'.Translator::translate('Max items allowed').' <span class="count">'.$max.'</span></span>';
            }

            $field .= '</div>';
        }

        return $field;
    }

    /**
     * Enqueue assets
     */
    protected function enqueueAssets()
    {
        wp_enqueue_script( 'html5sortable', plugins_url( 'advanced-custom-post-type/assets/vendor/html5sortable/dist/html5sortable.min.js'), [], '2.2.0', true );
        wp_enqueue_script( 'jquery.modal-js', plugins_url( 'advanced-custom-post-type/assets/vendor/jquery.modal/jquery.modal.min.js'), ['jquery'], '3.1.0', true);
        wp_enqueue_style( 'jquery.modal-css', plugins_url( 'advanced-custom-post-type/assets/vendor/jquery.modal/jquery.modal.min.css'), [], '3.1.0', 'all');
        wp_enqueue_script_module( 'custom-post-js', plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/js/post.js' : 'advanced-custom-post-type/assets/static/js/post.min.js'), ['jquery'], ACPT_PLUGIN_VERSION );
    }
}
