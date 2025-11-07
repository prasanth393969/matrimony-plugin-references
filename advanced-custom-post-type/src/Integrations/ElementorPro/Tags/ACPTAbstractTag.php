<?php

namespace ACPT\Integrations\ElementorPro\Tags;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Integrations\ElementorPro\Constants\TagsConstants;
use ACPT\Integrations\ElementorPro\DynamicDataProvider;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;

abstract class ACPTAbstractTag extends Tag
{
	/**
	 * Get dynamic tag name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Dynamic tag name.
	 */
	abstract public function get_name();

	/**
	 * Get dynamic tag title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Dynamic tag title.
	 */
	abstract public function get_title();

	/**
	 * Get dynamic tag groups.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Dynamic tag groups.
	 */
	public function get_group()
	{
		return [ TagsConstants::GROUP_NAME ];
	}

	/**
	 * Get dynamic tag categories.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Dynamic tag categories.
	 */
	abstract public function get_categories();

	/**
	 * Register controls
	 */
	protected function register_controls()
	{
		$fields = DynamicDataProvider::getInstance()->getFields();
		$options = [
			'default' => esc_html__( 'Default', ACPT_PLUGIN_NAME )
		];

		if(isset($fields[static::class])){

			/** @var MetaFieldModel $field */
			foreach ($fields[static::class] as $field){
				$key = TagsConstants::getKey($field);
				$label = '['.$field->getFindLabel().'] ' . $field->getUiName();
				$options[$key] = $label;
			}

			$this->add_control(
				'field',
				[
					'label' => esc_html__( 'Field', ACPT_PLUGIN_NAME ),
					'type' => Controls_Manager::SELECT,
					'options' => $options,
				]
			);

			if($field->getBelongsToLabel() === MetaTypes::CUSTOM_POST_TYPE){
				$this->add_control(
					'postId',
					[
						'label' => esc_html__( 'Post ID', ACPT_PLUGIN_NAME ),
						'type' => Controls_Manager::TEXT,
						'description' => esc_html__('It is possible to change the default behavior and make data retrieval from a certain post ID.', ACPT_PLUGIN_NAME ),
					]
				);
			}
		}
	}

	/**
	 * @return array
	 */
	protected function extractField()
	{
		$postId = null;
		$field = $this->get_settings( 'field' );
		$field = explode(TagsConstants::KEY_SEPARATOR, $field);

		if(empty($field)){
			return [];
		}

		if($field[0] === MetaTypes::CUSTOM_POST_TYPE){
			$postId = $this->get_settings('postId');
		}

		return [
			'belongsTo' => $field[0],
			'find' => $field[1],
			'boxName' => $field[2],
			'fieldName' => $field[3],
			'fieldType' => $field[4],
			'postId' => $postId,
		];
	}

	/**
	 * @return int|mixed
	 */
	protected function getCurrentPostId()
	{
		global $post;

		return (isset($_GET['post']) and get_post_type($_GET['post']) !== 'elementor_library') ? (int)$_GET['post'] : (int)$post->ID;
	}

    /**
     * @return mixed|null
     */
    protected function getRawData()
    {
        $field = $this->extractField();

        if(!empty($field)){
            if(
                isset($field['find']) and
                isset($field['belongsTo']) and
                isset($field['boxName']) and
                isset($field['fieldName']) and
                isset($field['fieldType'])
            ){
                $find = $field['find'];
                $belongsTo = $field['belongsTo'];
                $boxName = $field['boxName'];
                $fieldName = $field['fieldName'];

                $context = null;
                $contextId = null;

                switch ($belongsTo){
                    case BelongsTo::PARENT_POST_ID:
                    case BelongsTo::POST_ID:
                    case MetaTypes::CUSTOM_POST_TYPE:
                    case BelongsTo::POST_TAX:
                    case BelongsTo::POST_CAT:
                    case BelongsTo::POST_TEMPLATE:
                        $context = 'post_id';
                        $contextId = (isset($field['postId']) and !empty($field['postId'])) ? (int)$field['postId'] : $this->getCurrentPostId();
                        break;

                    case MetaTypes::OPTION_PAGE:
                        $context = 'option_page';
                        $contextId = $find;
                        break;
                }

                if($context === null or $contextId === null){
                    echo '';
                    exit();
                }

                return get_acpt_field([
                    $context => $contextId,
                    'box_name' => $boxName,
                    'field_name' => $fieldName,
                    'with_context' => true,
                ]);
            }
        }
    }
}