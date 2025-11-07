<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Visibility;
use ACPT\Core\Generators\Form\FormGenerator;
use ACPT\Core\Generators\Validation\DataValidateAttributes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Core\Models\Form\FormModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Checker\FieldVisibilityChecker;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\PHP\Session;

abstract class AbstractField
{
	/**
	 * @var FormFieldModel
	 */
	protected FormFieldModel $fieldModel;

	/**
	 * @var FormModel
	 */
	protected FormModel $formModel;

	/**
	 * @var null
	 */
	protected $postId;

	/**
	 * @var null
	 */
	protected $userId;

	/**
	 * @var null
	 */
	protected $termId;

    /**
     * @var
     */
    protected $isNested = false;

    /**
     * @var
     */
    protected $index;

    /**
     * @var MetaFieldModel|null
     */
    protected $parentField;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $extra = [];

    /**
	 * AbstractField constructor.
	 *
	 * @param FormModel $formModel
	 * @param FormFieldModel $fieldModel
	 * @param null $postId
	 * @param null $termId
	 * @param null $userId
	 */
	public function __construct(FormModel $formModel, FormFieldModel $fieldModel, $postId = null, $termId = null, $userId = null)
	{
		$this->fieldModel = $fieldModel;
		$this->formModel = $formModel;
		$this->postId = $postId;
		$this->termId = $termId;
		$this->userId = $userId;

		$locationBelong = ( $this->formModel->getMetaDatum('fill_meta_location_belong') !== null) ? $this->formModel->getMetaDatum('fill_meta_location_belong')->getValue() : null; // ---> customPostType
		$locationItem = ( $this->formModel->getMetaDatum('fill_meta_location_item') !== null) ? $this->formModel->getMetaDatum('fill_meta_location_item')->getValue() : null;

		if($this->postId === null and $locationBelong === MetaTypes::CUSTOM_POST_TYPE){
			$this->postId = $locationItem;
		}

		if($this->termId === null and $locationBelong === MetaTypes::TAXONOMY){
			$this->termId = $locationItem;
		}

		if($this->userId === null and $locationBelong === MetaTypes::USER){
			$this->userId = $locationItem;
		}

        $this->isNested = false;
	}

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @param array $extra
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
    }

    /**
     * @return MetaFieldModel|null
     */
    public function getParentField(): ?MetaFieldModel
    {
        return $this->parentField;
    }

    /**
     * @param MetaFieldModel|null $parentField
     */
    public function setParentField(?MetaFieldModel $parentField): void
    {
        $this->parentField = $parentField;
    }

    /**
     * @return mixed
     */
    public function isNested()
    {
        return $this->isNested;
    }

    /**
     * @param mixed $isNested
     */
    public function setIsNested($isNested): void
    {
        $this->isNested = $isNested;
    }

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index): void
    {
        $this->index = $index;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        if(empty($this->fieldModel->getMetaField())){
            return true;
        }

        if(empty($this->formModel->getMetaDatum("fill_meta_location_find"))){
            return true;
        }

        if(empty($this->formModel->getMetaDatum("fill_meta_location_belong"))){
            return true;
        }

        return FieldVisibilityChecker::check(
            Visibility::IS_BACKEND,
            $this->postId ?? $this->termId ?? $this->userId,
            $this->formModel->getMetaDatum("fill_meta_location_belong")->getValue(),
            $this->fieldModel->getMetaField(),
            [],
            $this->index ?? null,
            null,
            null
        );
    }

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function renderElement()
	{
	    if(!$this->isDisabled()){
            $this->enqueueFieldAssets();
            $select2 = (!empty($this->fieldModel->getExtra()['select2'])) ? $this->fieldModel->getExtra()['select2'] : false;

            // Automatically include tom-select is select2 option is enabled
            if($select2){
                wp_enqueue_script( 'tom-select-js', plugins_url( 'advanced-custom-post-type/assets/vendor/tom-select/tom-select.complete.min.js'), [], '2.4.3', true);
                wp_enqueue_style( 'tom-select-css', plugins_url( 'advanced-custom-post-type/assets/vendor/tom-select/tom-select.default.min.css'), [], '2.4.3', 'all');
            }
        }

	    $fontSize = $this->fieldModel->getExtra()['fontSize'] ?? "m";
	    $isVisible = !$this->isVisible() ? "hidden" : "";
	    $fieldId = $this->fieldModel->getMetaField() !== null ? $this->fieldModel->getMetaField()->getId() : null;
		$settings = $this->fieldModel->getSettings();
		$layout = isset($settings['layout']) ? $settings['layout'] : "block";
		$cols = isset($settings['cols']) ? $settings['cols'] : 12;
		$label = $this->fieldModel->getLabel();
		$description = $this->fieldModel->getDescription();
		$required = ($this->fieldModel->isRequired()) ? ' <span class="acpt-required">*</span>' : '';
		$element = "<div class='col-".$cols."'>";
		$element .= "<div id='".$this->fieldModel->getId()."' data-field-id='".$fieldId."' class='acpt-form-element font-size-".$fontSize." acpt-form-".$layout." ".$isVisible."'>";

        if($this->isTypographic()){
            $element .= $this->render();
            $element .= "</div>";
            $element .= "</div>";

            return $element;
        }

		if(!empty($label)){
			$element .= '<label class="acpt-form-label" for="'.Strings::esc_attr($this->getIdName()).'">'.$label.$required.'</label>';
		}

		$element .= "<input type='hidden' name='".Strings::esc_attr($this->getIdName("type"))."' value='".$this->fieldModel->getType()."' />";

		if($this->fieldModel->getMetaField() !== null){
            $element .= "<input type='hidden' name='".Strings::esc_attr($this->getIdName("id"))."' value='".$fieldId."' />";
        }

		$element .= $this->render();
		$element .= $this->renderErrors();

		if(!empty($description)){
			$element .= '<div id="'.Strings::esc_attr($this->getIdName("description")).'" class="acpt-form-description">'.$description.'</div>';
		}

		$element .= "</div>";
		$element .= "</div>";

		return $element;
	}

	/**
	 * @return string
	 */
	protected function required()
	{
		return ($this->fieldModel->isRequired()) ? 'required="required"' : '';
	}

    /**
     * @param null $label
     * @return string
     */
	protected function getIdName($label = null)
	{
	    // ex: test_repeater[testo][0][value]
	    if($this->isNested and !empty($this->fieldModel->getMetaField()) and !empty($this->fieldModel->getMetaField()->getParentField())){

	        $base = $this->fieldModel->getMetaField()->getParentField()->getDbName().'['.$this->fieldModel->getName().']['.$this->index.']';

	        if(!empty($label)){
	            return $base.'['.$label.']';
            }

            return $base.'[value]';
        }

	    $base = $this->fieldModel->getName();

        if(!empty($label)){
            return $base.'_'.$label;
        }

		return $base;
	}

	/**
	 * @return mixed|string
	 */
	protected function cssClass()
	{
		$base = (!empty($this->fieldModel->getSettings()['css'])) ? Strings::esc_attr($this->fieldModel->getSettings()['css']) : 'acpt-form-control';

		if($this->hasErrors()){
			$base .= ' has-errors';
		}

        if($this->isDisabled()){
            $base .= ' disabled';
        }

        $select2 = (!empty($this->fieldModel->getExtra()['select2'])) ? $this->fieldModel->getExtra()['select2'] : false;

        if($select2){
            $base .= ' acpt-select2';
        }

		return $base;
	}

	/**
	 * @return string|null
	 */
	protected function placeholder()
	{
		return (!empty($this->fieldModel->getExtra()['placeholder'])) ? Strings::esc_attr($this->fieldModel->getExtra()['placeholder']) : null;
	}

    /**
     * @return string
     */
    protected function disabled()
    {
        return $this->isDisabled() ? 'disabled="disabled"' : "";
    }

    /**
     * @return bool
     */
    protected function isDisabled()
    {
        return (!empty($this->fieldModel->getExtra()['disabled'])) ? $this->fieldModel->getExtra()['disabled'] : false;
    }

    /**
     * @return bool
     */
    protected function isTypographic()
    {
        return $this->fieldModel->getGroup() === "Typography elements";
    }

	/**
	 * @return mixed|null
	 */
	protected function defaultValue()
	{
	    if($this->value){
	        return $this->value;
        }

	    // is an ACPT field
		if($this->fieldModel->getMetaField()){

            $value = null;
			$belong = $this->fieldModel->getBelong();
			$find = $this->fieldModel->getFind();

			if($belong !== null and $belong === MetaTypes::OPTION_PAGE and $find !== null){
				$value = Meta::fetch($find, MetaTypes::OPTION_PAGE, $find.'_'.$this->fieldModel->getMetaField()->getDbName(), true);
			}

			if($this->postId){
				$value = Meta::fetch($this->postId, MetaTypes::CUSTOM_POST_TYPE, $this->fieldModel->getMetaField()->getDbName(), true);
			}

			if($this->termId){
				$value = Meta::fetch($this->termId, MetaTypes::TAXONOMY, $this->fieldModel->getMetaField()->getDbName(), true);
			}

			if($this->userId){
				$value = Meta::fetch($this->userId, MetaTypes::USER, $this->fieldModel->getMetaField()->getDbName(), true);
			}

            if($value === null and !empty($this->fieldModel->getExtra()['defaultValue'])){
                $value = $this->fieldModel->getExtra()['defaultValue'];
            }

            return $value;
		}

		return $this->WordPressFieldCurrentValue();
	}

    /**
     * @param $label
     * @return mixed|null
     */
    protected function defaultExtraValue($label)
    {
        if($this->fieldModel->getMetaField()){

            $belong = $this->fieldModel->getBelong();
            $find = $this->fieldModel->getFind();

            // get nested fields values
            if($this->isNested){

                if(isset($this->extra[$label])){
                    return $this->extra[$label];
                }

                $data = $this->getParentData();
                $key = Strings::toDBFormat($this->fieldModel->getMetaField()->getName());

                // Field nested in a repeater
                if(
                    isset($data[$key]) and
                    isset($data[$key][$this->index]) and
                    isset($data[$key][$this->index][$label])
                ){
                    return $data[$key][$this->index][$label];
                }

                return null;
            }

            if($belong !== null and $belong === MetaTypes::OPTION_PAGE and $find !== null){
                return Meta::fetch($find, MetaTypes::OPTION_PAGE, $find.'_'.$this->fieldModel->getMetaField()->getDbName()."_".$label, true);
            }

            if($this->postId){
                return Meta::fetch($this->postId, MetaTypes::CUSTOM_POST_TYPE, $this->fieldModel->getMetaField()->getDbName()."_".$label, true);
            }

            if($this->termId){
                return Meta::fetch($this->termId, MetaTypes::TAXONOMY, $this->fieldModel->getMetaField()->getDbName()."_".$label, true);
            }

            if($this->userId){
                return Meta::fetch($this->userId, MetaTypes::USER, $this->fieldModel->getMetaField()->getDbName()."_".$label, true);
            }
        }

        return null;
    }

	/**
	 * This function fetches the current value of a WP field
	 * (ex. Post title, Post content, User email, etc...)
	 *
	 * @return mixed
	 */
	private function WordPressFieldCurrentValue()
	{
		// post field current value
		if(!empty($this->postId)){
			switch ($this->fieldModel->getType()){

				// WORDPRESS_POST_THUMBNAIL
				case FormFieldModel::WORDPRESS_POST_THUMBNAIL:
					return get_the_post_thumbnail_url($this->postId);

				// WORDPRESS_POST_TITLE
				case FormFieldModel::WORDPRESS_POST_TITLE:
					return get_the_title($this->postId);

				// WORDPRESS_POST_CONTENT
				case FormFieldModel::WORDPRESS_POST_CONTENT:
					return get_the_content(null, false, $this->postId);

				// WORDPRESS_POST_EXCERPT
				case FormFieldModel::WORDPRESS_POST_EXCERPT:
					return get_the_excerpt($this->postId);

				// WORDPRESS_POST_DATE
				case FormFieldModel::WORDPRESS_POST_DATE:
					return get_the_date('Y-m-d', $this->postId);

				// WORDPRESS_POST_AUTHOR
				case FormFieldModel::WORDPRESS_POST_AUTHOR:
					return get_post_field( 'post_author', $this->postId );
			}
		}

		if(!empty($this->termId)){
			$term = get_term( $this->termId );

			if($term instanceof \WP_Term){
				switch ($this->fieldModel->getType()){
					case FormFieldModel::WORDPRESS_TERM_NAME:
						return $term->name;

					case FormFieldModel::WORDPRESS_TERM_DESCRIPTION:
						return $term->description;

					case FormFieldModel::WORDPRESS_TERM_SLUG:
						return $term->slug;
				}
			}
		}

		// user field current value
		if(!empty($this->userId)){
			$userData = get_userdata( $this->userId );

			if($userData){
				switch ($this->fieldModel->getType()){

					// WORDPRESS_USER_EMAIL
					case FormFieldModel::WORDPRESS_USER_EMAIL:
						return $userData->user_email;

					// WORDPRESS_USER_FIRST_NAME
					case FormFieldModel::WORDPRESS_USER_FIRST_NAME:
						return $userData->first_name;

					// WORDPRESS_USER_LAST_NAME
					case FormFieldModel::WORDPRESS_USER_LAST_NAME:
						return $userData->last_name;

					// WORDPRESS_USER_USERNAME
					case FormFieldModel::WORDPRESS_USER_USERNAME:
						return $userData->display_name;

					// WORDPRESS_USER_BIO
					case FormFieldModel::WORDPRESS_USER_BIO:
						return $userData->description;
				}
			}
		}

		if(!empty($this->fieldModel->getExtra()['defaultValue'])){
			return Strings::esc_attr($this->fieldModel->getExtra()['defaultValue']);
		}

		return null;
	}

	/**
	 * @param $defaultValue
	 * @param $uom
	 * @param $options
	 *
	 * @return string
	 */
	protected function renderUom($defaultValue, $uom, $options)
	{
		$defaultValueUom = $defaultValue;
		$render = '<select 
				id="'.$this->getIdName().'_'.$uom.'"
				name="'.$this->getIdName().'_'.$uom.'"
				class="'.$this->cssClass().'"
			>';

		foreach ($options as $symbol => $data){
			$render .= '<option 
				value="'.Strings::esc_attr($symbol).'" 
				'.selected($symbol, $defaultValueUom, false).'
				data-symbol="'.Strings::esc_attr($data['symbol']).'" 
				data-placeholder="0.00" 
				>
					'.esc_html($symbol).'
				</option>';
		}

		$render .= '</select>';

		return $render;
	}

    /**
     * @return mixed
     */
    protected function getParentData()
    {
        // field nested in a repeater
        if($this->parentField !== null){
            $key = '';
            $belong = $this->fieldModel->getBelong();
            $find = $this->fieldModel->getFind();

            if($belong === MetaTypes::OPTION_PAGE){
                $key .= $find.'_';
            }

            if($this->parentField->hasParent()){
                $key .= $this->parentField->getParentField()->getDbName();
            } elseif($this->parentField->hasParentBlock()){
                $key .= $this->parentField->getParentBlock()->getMetaField()->getDbName();
            }  else {
                $key .= $this->parentField->getDbName();
            }

            if($belong !== null and $belong === MetaTypes::OPTION_PAGE and $find !== null){
                return Meta::fetch($find, MetaTypes::OPTION_PAGE, $key, true);
            }

            if($this->postId){
                return Meta::fetch($this->postId, MetaTypes::CUSTOM_POST_TYPE, $key, true);
            }

            if($this->termId){
                return Meta::fetch($this->termId, MetaTypes::TAXONOMY, $key, true);
            }

            if($this->userId){
                return Meta::fetch($this->userId, MetaTypes::USER, $key, true);
            }
        }

        return null;
    }

    /**
     * @param null $max
     * @param null $min
     * @param null $step
     * @return string
     */
    protected function appendMaxMinAndStep($max = null, $min = null, $step = null)
    {
        $attr = '';

        if($min){
            $attr .= ' min="'.$min.'"';
        }

        if($max){
            $attr .= ' max="'.$max.'"';
        }

        if($step){
            $attr .= ' step="'.$step.'"';
        }

        return $attr;
    }

    /**
     * @param null $max
     * @param null $min
     * @return string
     */
    protected function appendMaxLengthAndMinLength($max = null, $min = null)
    {
        $attr = '';

        if($min){
            $attr .= ' minlength="'.$min.'"';
        }

        if($max){
            $attr .= ' maxlength="'.$max.'"';
        }

        return $attr;
    }

	/**
	 * @return string|null
	 */
	protected function appendDataValidateAndConditionalRenderingAttributes()
	{
        $attr = '';

        // conditional rules
	    if($this->fieldModel->getMetaField() !== null){
            $attr .= ' data-conditional-rules-id="'.$this->fieldModel->getMetaField()->getId().'"';

            if($this->fieldModel->getMetaField()->hasParent()){
                $attr .= ' data-conditional-rules-field-index="'.$this->getIndex().'"';
            }

            if($this->fieldModel->getMetaField()->hasParentBlock()){
                $attr .= ' data-conditional-rules-field-index="'.$this->getIndex().'"';
            }
        }

        // validation rules
		if($this->fieldModel->canFieldHaveValidationAndLogicRules()){
            $attr .= DataValidateAttributes::generate(
                $this->fieldModel->getValidationRules(),
                $this->fieldModel->isTextualField(),
                $this->fieldModel->isRequired()
            );
		}

		return $attr;
	}

	/**
	 * @return bool
	 */
	private function hasErrors()
	{
		if(Session::has(FormGenerator::ERRORS_SESSION_KEY)){
			return array_key_exists($this->fieldModel->getId(), Session::get(FormGenerator::ERRORS_SESSION_KEY));
		}

		return false;
	}

	/**
	 * @return string
	 */
	private function renderErrors()
	{
		$errorsList = '<ul class="acpt-error-list" id="acpt-error-list-'.$this->fieldModel->getName().'">';

		if(Session::has(FormGenerator::ERRORS_SESSION_KEY)){
			foreach (Session::get(FormGenerator::ERRORS_SESSION_KEY) as $id => $errors){
				if($id === $this->fieldModel->getId()) {
					if(is_array($errors)){
						foreach ($errors as $error){
							$errorsList .= '<li>'.$error.'</li>';
						}
					}
				}
			}
		}

		$errorsList .= '</ul>';

		return $errorsList;
	}

	/**
	 * @return string
	 */
	abstract public function render();

	/**
	 * @return mixed
	 */
	abstract public function enqueueFieldAssets();
}