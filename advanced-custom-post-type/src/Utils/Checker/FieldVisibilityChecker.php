<?php

namespace ACPT\Utils\Checker;

use ACPT\Constants\Operator;
use ACPT\Constants\Visibility;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaFieldVisibilityModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\PHP\Logics;

class FieldVisibilityChecker
{
	/**
	 * @param string $visibility
	 * @param string $elementId
	 * @param string $belongsTo
	 * @param MetaFieldModel $metaBoxFieldModel
	 * @param array $liveData
	 * @param null $fieldIndex
	 * @param null $blockName
	 * @param null $blockIndex
	 *
	 * @return bool
	 */
	public static function check(
		$visibility,
		$elementId,
		$belongsTo,
		MetaFieldModel $metaBoxFieldModel,
		$liveData = [],
		$fieldIndex = null,
		$blockName = null,
		$blockIndex = null
	)
	{
		if(!in_array($visibility, [
			Visibility::IS_BACKEND,
			Visibility::IS_FRONTEND
		])){
			return true;
		}

		try {
			if($metaBoxFieldModel === null or !$metaBoxFieldModel->hasVisibilityConditions()){
				return true;
			}

			if(is_string($belongsTo)){
                $metaBoxFieldModel->setBelongsToLabel($belongsTo);
            }

            if(is_string($elementId)){
                $metaBoxFieldModel->setFindLabel($elementId);
            }

			$visibilityConditions = $metaBoxFieldModel->getVisibilityConditions();
			$logicBlocks = Logics::extractLogicBlocks($visibilityConditions, $visibility);

			foreach ($logicBlocks as $logicBlocksConditions){
				if(
                    self::returnTrueOrFalseForALogicBlock(
                        $elementId,
                        $belongsTo,
                        $metaBoxFieldModel,
                        $logicBlocksConditions,
                        $liveData,
                        $fieldIndex,
                        $blockName,
                        $blockIndex
                    )
                ){
				    return true;
                }
			}

			return false;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return true;
		}
	}

	/**
	 * Determine if a logic block is visible or not
	 *
	 * @param $elementId
	 * @param $belongsTo
	 * @param MetaFieldModel $metaBoxFieldModel
	 * @param MetaFieldVisibilityModel[] $conditions
	 * @param array $liveData
	 * @param null $fieldIndex
	 * @param null $blockName
	 * @param null $blockIndex
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private static function returnTrueOrFalseForALogicBlock(
		$elementId,
		$belongsTo,
		MetaFieldModel $metaBoxFieldModel,
		array $conditions,
		$liveData = [],
		$fieldIndex = null,
		$blockName = null,
		$blockIndex = null
	)
	{
		$rawData = null;
		if(!empty($liveData)){

			$filter = array_filter($liveData, function ($element) use ($metaBoxFieldModel) {
				return $metaBoxFieldModel->getId() === $element['formId'];
			});

			if(!empty($filter)){
				$rawData = array_values($filter)[0]['value'];
			}

		} else {
			$rawData = Meta::fetch($elementId, $belongsTo, self::getKey($metaBoxFieldModel));
		}

		$matches = 0;

		foreach ($conditions as $condition){

			$typeEnum = $condition->getType()['type'];
			$typeValue = $condition->getType()['value'];

			if($typeValue instanceof MetaFieldModel){
				$typeValue->setFindLabel($metaBoxFieldModel->getFindLabel());
				$typeValue->setBelongsToLabel($metaBoxFieldModel->getBelongsToLabel());
			}

			$operator = $condition->getOperator();
			$value = $condition->getValue();

			// field value of a nested field in a Repeater
			if($metaBoxFieldModel->getParentId() !== null){
				if(
					isset($rawData[$metaBoxFieldModel->getName()]) and
					isset($rawData[$metaBoxFieldModel->getName()][$fieldIndex])
				){
					$rawData = $rawData[$metaBoxFieldModel->getName()][$fieldIndex]['value'];
				}
			}

			// field value of a nested field in a Flexible content
			if($metaBoxFieldModel->getBlockId() !== null){
				if(
					isset($rawData['blocks']) and
					isset($rawData['blocks'][$blockIndex]) and
					isset($rawData['blocks'][$blockIndex][$blockName]) and
					isset($rawData['blocks'][$blockIndex][$blockName][$metaBoxFieldModel->getName()]) and
					isset($rawData['blocks'][$blockIndex][$blockName][$metaBoxFieldModel->getName()][$fieldIndex])
				){
					$rawData = $rawData['blocks'][$blockIndex][$blockName][$metaBoxFieldModel->getName()][$fieldIndex]['value'];
				}
			}

			if($typeEnum === 'VALUE'){
				switch ($operator) {
					case Operator::EQUALS:
						if($rawData == $value){
							$matches++;
						}
						break;

					case Operator::NOT_EQUALS:
						if($rawData != $value){
							$matches++;
						}
						break;

					case Operator::LT:
						if($rawData < $value){
							$matches++;
						}
						break;

					case Operator::GT:
						if($rawData > $value){
							$matches++;
						}
						break;

					case Operator::LTE:
						if($rawData <= $value){
							$matches++;
						}
						break;

					case Operator::GTE:
						if($rawData >= $value){
							$matches++;
						}
						break;

					case Operator::LIKE:
						if(Strings::likeMatch('%'.$value.'%',$rawData)){
							$matches++;
						}
						break;

					case Operator::NOT_LIKE:
						if(false === Strings::likeMatch('%'.$value.'%',$rawData)){
							$matches++;
						}
						break;

					case Operator::NULL:
						if($rawData === null){
							$matches++;
						}
						break;

					case Operator::NOT_NULL:
						if($rawData !== null){
							$matches++;
						}
						break;

					case Operator::BLANK:
						if($rawData === ''){
							$matches++;
						}
						break;

					case Operator::NOT_BLANK:
						if($rawData !== ''){
							$matches++;
						}
						break;

					case Operator::CHECKED:
						if($rawData == 1){
							$matches++;
						}
						break;

					case Operator::NOT_CHECKED:
						if($rawData == 0){
							$matches++;
						}
						break;

					case Operator::IN:
						$value = trim($value);
						$value = explode(',', $value);
						$rawData = is_array($rawData) ? $rawData : [$rawData];

						$check = array_intersect($rawData, $value);

						if(count($check) > 0){
							$matches++;
						}
						break;

					case Operator::NOT_IN:
						$value = trim($value);
						$value = explode(',', $value);
						$rawData = is_array($rawData) ? $rawData : [$rawData];

						$check = array_intersect($rawData, $value);

						if(empty($check)){
							$matches++;
						}
						break;
				}
			}

			if($typeEnum === 'POST_ID' or $typeEnum === 'TERM_ID' or $typeEnum === 'USER_ID' or $typeEnum === 'OPTION_PAGE'){
				switch ($operator) {
					case Operator::EQUALS:
						if($value == $elementId){
							$matches++;
						}
						break;

					case Operator::NOT_EQUALS:
						if($value !== $elementId){
							$matches++;
						}
						break;

					case Operator::IN:
						$value = trim($value);
						$value = explode(',', $value);

						if(in_array($elementId, $value)){
							$matches++;
						}
						break;

					case Operator::NOT_IN:
						$value = trim($value);
						$value = explode(',', $value);

						if(!in_array($elementId, $value)){
							$matches++;
						}
						break;
				}
			}

            if($typeEnum === 'USER'){
                $currentUserId = get_current_user_id();

                if($currentUserId == 0){
                    return false;
                }

                switch ($operator) {
                    case Operator::EQUALS:
                        if($value == $currentUserId){
                            $matches++;
                        }
                        break;

                    case Operator::NOT_EQUALS:
                        if($value != $currentUserId){
                            $matches++;
                        }
                        break;

                    case Operator::IN:
                        $value = explode(',', $value);
                        if(in_array($currentUserId, $value)){
                            $matches++;
                        }
                        break;

                    case Operator::NOT_IN:
                        $value = explode(',', $value);
                        if(!in_array($currentUserId, $value)){
                            $matches++;
                        }
                        break;
                }
            }

			if($typeEnum === 'TAXONOMY'){

				$categories = wp_get_post_categories((int)$elementId);
				$taxonomies = wp_get_post_terms((int)$elementId, $typeValue);

				if(is_array($taxonomies)){
					$allTerms = array_merge($categories, $taxonomies);
					$termIds = [];

					foreach ($allTerms as $term){
						if(isset($term->term_id)){
							$termIds[] = $term->term_id;
						}
					}

					switch ($operator) {

                        case Operator::EQUALS:
							$termIds = is_array($termIds) ? $termIds : [$termIds];

							if(count($termIds) === 1 and $value == $termIds[0]){
								$matches++;
							}
							break;

						case Operator::NOT_EQUALS:
							$termIds = is_array($termIds) ? $termIds : [$termIds];

							if(!in_array($value, $termIds)){
								$matches++;
							}
							break;

						case Operator::IN:
							$value = trim($value);
							$value = explode(',', $value);
							$termIds = is_array($termIds) ? $termIds : [$termIds];

							$check = array_intersect($termIds, $value);

							if(count($check) > 0){
								$matches++;
							}
							break;

						case Operator::NOT_IN:
							$value = trim($value);
							$value = explode(',', $value);
							$termIds = is_array($termIds) ? $termIds : [$termIds];

							$check = array_intersect($termIds, $value);

							if(empty($check)){
								$matches++;
							}
							break;

						case Operator::BLANK:
							if(empty($termIds)){
								$matches++;
							}
							break;


						case Operator::NOT_BLANK:
							if(!empty($termIds)){
								$matches++;
							}
							break;
					}
				}
			}

			if($typeEnum === 'OTHER_FIELDS' and $typeValue instanceof MetaFieldModel){

				$fieldRawData = null;
				if(!empty($liveData)){

					$filter = array_filter($liveData, function ($element) use ($typeValue, $belongsTo, $elementId, $fieldIndex, $blockIndex, $blockName) {

						if(!isset($element['id'])){
							return false;
						}

						// nested field inside a repeater
						if($typeValue->hasParent() and $fieldIndex !== null){
							$parentMetaField = MetaRepository::getMetaFieldById($typeValue->getParentId(), true);
							$elementId = $parentMetaField->getDbName().'['.$typeValue->getName().']['.$fieldIndex.'][value]';

							return $elementId === $element['id'];
						}

						// nested field inside a block
						if($typeValue->hasParentBlock() and $blockIndex !== null and $blockName !== null){
							$parentBlock = MetaRepository::getMetaBlockById($typeValue->getBlockId(), true);
							$parentMetaField = MetaRepository::getMetaFieldById($parentBlock->getMetaField()->getId(), true);
							$elementId = $parentMetaField->getDbName().'[blocks]['.$blockIndex.']['.$blockName.']['.$typeValue->getName().']['.$fieldIndex.'][value]';

							return $elementId === $element['id'];
						}

						return $typeValue->getId() === $element['formId'];
					});

					if(!empty($filter)){
						$fieldRawData = array_values($filter)[0]['value'];
					}

				} else {

				    // cloned fields
				    if($metaBoxFieldModel->getForgedBy() !== null){
                        $typeValue->forgeBy($metaBoxFieldModel->getForgedBy());
                    }

					// nested field inside a repeater
					if($typeValue->hasParent()){
						$parentMetaField = MetaRepository::getMetaFieldById($typeValue->getParentId(), true);
						$parentMetaField->setBelongsToLabel($belongsTo);
						$parentMetaField->setFindLabel($elementId);
						$parentFieldRawData = Meta::fetch($elementId, $belongsTo, $parentMetaField->getDbName());

						if(
							isset($parentFieldRawData[$typeValue->getName()]) and
							isset($parentFieldRawData[$typeValue->getName()][$fieldIndex]) and
							isset($parentFieldRawData[$typeValue->getName()][$fieldIndex]['value'])
						){
							$fieldRawData = $parentFieldRawData[$typeValue->getName()][$fieldIndex]['value'];
						}
					}
					// nested field inside a block
					elseif($typeValue->hasParentBlock()) {
						$parentBlock = MetaRepository::getMetaBlockById($typeValue->getBlockId());
						$parentMetaField = MetaRepository::getMetaFieldById($parentBlock->getMetaField()->getId(), true);
						$parentMetaField->setBelongsToLabel($belongsTo);
						$parentMetaField->setFindLabel($elementId);
						$parentFieldRawData = Meta::fetch($elementId, $belongsTo, $parentMetaField->getDbName());

						if(
							isset($parentFieldRawData['blocks']) and
							isset($parentFieldRawData['blocks'][$blockIndex]) and
							isset($parentFieldRawData['blocks'][$blockIndex][$blockName]) and
							isset($parentFieldRawData['blocks'][$blockIndex][$blockName][$typeValue->getName()]) and
							isset($parentFieldRawData['blocks'][$blockIndex][$blockName][$typeValue->getName()][$fieldIndex]) and
							isset($parentFieldRawData['blocks'][$blockIndex][$blockName][$typeValue->getName()][$fieldIndex]['value'])
						){
							$fieldRawData = $parentFieldRawData['blocks'][$blockIndex][$blockName][$typeValue->getName()][$fieldIndex]['value'];
						}
					}

					// regular fields
					else {
						$fieldRawData = Meta::fetch($elementId, $belongsTo, $typeValue->getDbName());
					}
				}

				if(!empty($fieldRawData)){

					// for relational fields, which can return an array, even with just one element
					$fieldRawData = (is_array($fieldRawData) and !empty($fieldRawData)) ? $fieldRawData[0] : $fieldRawData;

					switch ($operator) {
						case Operator::EQUALS:
							if($fieldRawData == $value){
								$matches++;
							}
							break;

						case Operator::NOT_EQUALS:
							if($fieldRawData != $value){
								$matches++;
							}
							break;

						case Operator::LT:
							if($fieldRawData < $value){
								$matches++;
							}
							break;

						case Operator::GT:
							if($fieldRawData > $value){
								$matches++;
							}
							break;

						case Operator::LTE:
							if($fieldRawData <= $value){
								$matches++;
							}
							break;

						case Operator::GTE:
							if($fieldRawData >= $value){
								$matches++;
							}
							break;

						case Operator::LIKE:
							if(Strings::likeMatch('%'.$value.'%',$fieldRawData)){
								$matches++;
							}
							break;

						case Operator::NOT_LIKE:
							if(false === Strings::likeMatch('%'.$value.'%',$fieldRawData)){
								$matches++;
							}
							break;

						case Operator::NULL:
							if($fieldRawData === null){
								$matches++;
							}
							break;

						case Operator::NOT_NULL:
							if($fieldRawData !== null){
								$matches++;
							}
							break;

						case Operator::BLANK:
							if($fieldRawData === ''){
								$matches++;
							}
							break;

						case Operator::NOT_BLANK:
							if($fieldRawData !== ''){
								$matches++;
							}
							break;

						case Operator::CHECKED:
							if($fieldRawData == 1){
								$matches++;
							}
							break;

						case Operator::NOT_CHECKED:
							if($fieldRawData == 0){
								$matches++;
							}
							break;

						case Operator::IN:
							$value = trim($value);
							$value = explode(',', $value);
							$fieldRawData = is_array($fieldRawData) ? $fieldRawData : [$fieldRawData];

							$check = array_intersect($fieldRawData, $value);

							if(count($check) > 0){
								$matches++;
							}
							break;

						case Operator::NOT_IN:
							$value = trim($value);
							$value = explode(',', $value);
							$fieldRawData = is_array($fieldRawData) ? $fieldRawData : [$fieldRawData];

							$check = array_intersect($fieldRawData, $value);

							if(empty($check)){
								$matches++;
							}
							break;
					}
				}
			}
		}

		return $matches === count($conditions);
	}

	/**
	 * @param MetaFieldModel $metaBoxFieldModel
	 *
	 * @return string
	 * @throws \Exception
	 */
	private static function getKey(MetaFieldModel $metaBoxFieldModel)
	{
		if($metaBoxFieldModel->getParentId() !== null){

			$metaBoxParentFieldModel = MetaRepository::getMetaFieldById( $metaBoxFieldModel->getParentId());
			$metaBoxParentFieldModel->setBelongsToLabel($metaBoxFieldModel->getBelongsToLabel());
			$metaBoxParentFieldModel->setFindLabel($metaBoxFieldModel->getFindLabel());

			return $metaBoxParentFieldModel->getDbName();
		}

		if($metaBoxFieldModel->getBlockId() !== null){
			$metaBoxParentBlockModel = MetaRepository::getMetaBlockById($metaBoxFieldModel->getBlockId());
			$metaBoxParentFieldModel = $metaBoxParentBlockModel->getMetaField();
			$metaBoxParentFieldModel->setBelongsToLabel($metaBoxFieldModel->getBelongsToLabel());
			$metaBoxParentFieldModel->setFindLabel($metaBoxFieldModel->getFindLabel());

			return $metaBoxParentFieldModel->getDbName();
		}

		return $metaBoxFieldModel->getDbName();
	}
}