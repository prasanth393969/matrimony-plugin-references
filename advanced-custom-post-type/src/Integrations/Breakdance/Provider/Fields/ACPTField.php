<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Integrations\Breakdance\Provider\Helper\RawValueConverter;

class ACPTField
{
	/**
	 * @param MetaFieldModel $fieldModel
	 *
	 * @return string
	 */
	public static function label(MetaFieldModel $fieldModel)
	{
		$label = '['.$fieldModel->getBox()->getGroup()->getName().']';
		
		if($fieldModel->hasParent() and $fieldModel->getParentField() !== null){
			$label .= '['.$fieldModel->getParentField()->getName().']';
		}

		$label .= ' - ' . $fieldModel->getBox()->getName() . ' ' . $fieldModel->getName();

		return $label;
	}

	/**
	 * @return string
	 */
	public static function category()
	{
		return 'ACPT Meta fields';
	}

    /**
     * @param MetaFieldModel $fieldModel
     * @param null $find
     * @return string|null
     */
	public static function subcategory(MetaFieldModel $fieldModel, $find = null)
	{
	    if($find){
	        return $find;
        }

		return $fieldModel->getBox()->getGroup()->getUIName();
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 *
	 * @return string
	 */
	public static function slug(MetaFieldModel $fieldModel)
	{
		if($fieldModel->hasParent() and $fieldModel->getParentField() !== null){
			return $fieldModel->getBox()->getName() . '_' . $fieldModel->getParentField()->getName() . '_' . $fieldModel->getName();
		}

		if(
			$fieldModel->isNestedInABlock() and
			$fieldModel->getParentBlock() !== null and
			$fieldModel->getParentBlock()->getMetaField() !== null
		){
			return $fieldModel->getBox()->getName() . '_' . $fieldModel->getParentBlock()->getMetaField()->getName() . '_' . $fieldModel->getParentBlock()->getName() . '_' . $fieldModel->getName();
		}

		return $fieldModel->getBox()->getName() . '_' . $fieldModel->getName();
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 * @param $attributes
	 *
	 * @return mixed|null
	 * @throws \Exception
	 */
	public static function getValue(MetaFieldModel $fieldModel, $attributes)
	{
		$rawValue = null;
		$isVisible = true;
		$belongsTo = $fieldModel->getBelongsToLabel();

		if($belongsTo === null){
			return null;
		}

        $savedMetaField = MetaRepository::getMetaFieldById($fieldModel->getId());

		if($savedMetaField->hasParent()){
            $fieldModel->setParentId($savedMetaField->getParentId());
        }

        $context = self::getContext($fieldModel, $belongsTo);
		$a = $context['a'];
		$b = $context['b'];

		// render the field only if there is a context
		if($a !== null and $b !== null){

			// REPEATER
			if($fieldModel->hasParent() and $fieldModel->getParentField() !== null){
				$parentField = $fieldModel->getParentField();
				$breakdanceLoop = \Breakdance\DynamicData\LoopController::getInstance($parentField->getId());

				if(isset($breakdanceLoop->field['field']) and isset($breakdanceLoop->field['index'])){
					$loopField = $breakdanceLoop->field['field'];
					$loopIndex = $breakdanceLoop->field['index'];

					if($parentField->isEqualsTo($loopField)){

						$rawValue = get_acpt_child_field([
							$a => $b,
							'box_name' => $fieldModel->getBox()->getName(),
							'field_name' => $fieldModel->getName(),
							'parent_field_name' => $parentField->getName(),
							'index' => $loopIndex,
                            'with_context' => true,
                            'return' => 'raw',
						]);

						$isVisible = is_acpt_field_visible([
							$a => $b,
							'box_name' => $fieldModel->getBox()->getName(),
							'field_name' => $fieldModel->getName(),
							'parent_field_name' => $parentField->getName(),
							'index' => $loopIndex,
						]);

						if(empty($rawValue)){
							return null;
						}

						if($isVisible === false){
							return null;
						}

						return RawValueConverter::convert($rawValue, $fieldModel->getType(), $attributes);
					}

					return null;
				}

				return null;
			}

			// FLEXIBLE
			if($fieldModel->isNestedInABlock() and $fieldModel->getParentBlock() !== null){

				$parentBlock = $fieldModel->getParentBlock();
				$breakdanceLoop = \Breakdance\DynamicData\LoopController::getInstance($parentBlock->getId());

				if(
					isset($breakdanceLoop->field['block']) and
					isset($breakdanceLoop->field['limit']) and
					isset($breakdanceLoop->field['block_index']) and
					isset($breakdanceLoop->field['field_index'])
				){
					$loopBlock = $breakdanceLoop->field['block'];
					$blockIndex = $breakdanceLoop->field['block_index'];
					$fieldIndex = $breakdanceLoop->field['field_index'];

					if($parentBlock->isEqualsTo($loopBlock)){

						$rawValue = get_acpt_block_child_field([
							$a => $b,
							'box_name' => $fieldModel->getBox()->getName(),
							'field_name' => $fieldModel->getName(),
							'parent_field_name' => $parentBlock->getMetaField()->getName(),
							'block_name' => $parentBlock->getName(),
							'block_index' => $blockIndex,
							'index' => $fieldIndex,
                            'with_context' => true,
                            'return' => 'raw',
						]);

						$isVisible = is_acpt_field_visible([
							$a => $b,
							'box_name' => $fieldModel->getBox()->getName(),
							'field_name' => $fieldModel->getName(),
							'parent_field_name' => $parentBlock->getMetaField()->getName(),
							'block_name' => $parentBlock->getName(),
							'block_index' => $blockIndex,
							'index' => $fieldIndex,
						]);

						if(empty($rawValue)){
							return null;
						}

						if($isVisible === false){
							return null;
						}

						return RawValueConverter::convert($rawValue, $fieldModel->getType(), $attributes);
					}

					return null;
				}

				return null;
			}

			$rawValue = get_acpt_field([
				$a => $b,
				'box_name' => $fieldModel->getBox()->getName(),
				'field_name' => $fieldModel->getName(),
                'with_context' => true,
                'return' => 'raw',
			]);

			$isVisible = is_acpt_field_visible([
				$a => $b,
				'box_name' => $fieldModel->getBox()->getName(),
				'field_name' => $fieldModel->getName(),
			]);
		}

		if(empty($rawValue)){
			return null;
		}

		if($isVisible === false){
			return null;
		}

		return RawValueConverter::convert($rawValue, $fieldModel->getType(), $attributes);
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 * @param $belongsTo
	 *
	 * @return array
	 */
	private static function getContext(MetaFieldModel $fieldModel, $belongsTo): array
	{
		$a = null;
		$b = null;

		switch ($belongsTo){
			case MetaTypes::CUSTOM_POST_TYPE:
			case BelongsTo::POST_ID:
			case BelongsTo::POST_CAT:
			case BelongsTo::POST_TAX:
			case BelongsTo::POST_TEMPLATE:

				$postId = get_the_ID();

				if($postId !== null){
					$a = 'post_id';
					$b = $postId;
				}

				break;

			case MetaTypes::TAXONOMY:
			case BelongsTo::TERM_ID:

                global $breakdance_current_term;

				if(!empty($breakdance_current_term)){
					$a = 'term_id';
					$b = $breakdance_current_term->term_id;
				}

				if($a === null and $b === null){
                    $queriedObject = get_queried_object();

                    if(!empty($queriedObject)){
                        $a = 'term_id';
                        $b = $queriedObject->term_id;
                    }
                }

				break;

			case MetaTypes::OPTION_PAGE:
				$a = 'option_page';
				$b = $fieldModel->getFindLabel() ?? 'test';
				break;

            case MetaTypes::USER:
                $a = 'user_id';
                $b = get_current_user_id() ?? null;
                break;
		}

		return [
			'a' => $a,
			'b' => $b,
		];
	}
}