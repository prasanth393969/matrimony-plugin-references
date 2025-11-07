<?php

namespace ACPT\Utils\PHP;

use ACPT\Constants\Visibility;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\Meta\MetaBoxVisibilityModel;
use ACPT\Core\Models\Meta\MetaFieldVisibilityModel;

class Logics
{
	/**
	 * @param $elements
	 * @param null $visibility
	 *
	 * @return array
	 */
	public static function extractLogicBlocks($elements, $visibility = null)
	{
		if(empty($elements)){
			return [];
		}

		if(!is_array($elements)){
			return [];
		}

		$logicBlocks = [];
		$storedLogicBlocks = [];

		foreach ($elements as $index => $element){

			if(
				(($element instanceof MetaFieldVisibilityModel or $element instanceof MetaBoxVisibilityModel) and self::hasConditionToBeConsidered($visibility, $element)) or
				$element instanceof BelongModel
			){
				$isLast = $index === (count($elements)-1);
				$logic  = $element->getLogic();

				// OR
				if($logic === 'OR' and !$isLast){
					if(!empty($storedLogicBlocks)){
						$storedLogicBlocks[] = $element;
						$logicBlocks[] = $storedLogicBlocks;
						$storedLogicBlocks = [];
					} else {
						$logicBlocks[] = [$element];
					}
				}

				// AND
				if($logic === 'AND' and !$isLast){
					$storedLogicBlocks[] = $element;
				}

				// Last element
				if($isLast){
					if(!empty($storedLogicBlocks)){
						$storedLogicBlocks[] = $element;
						$logicBlocks[] = $storedLogicBlocks;
						$storedLogicBlocks = [];
					} else {
						$logicBlocks[] = [$element];
					}
				}
			}
		}

		return $logicBlocks;
	}

	/**
	 * @param $visibility
	 * @param MetaFieldVisibilityModel|MetaBoxVisibilityModel $visibilityCondition
	 *
	 * @return bool
	 */
	private static function hasConditionToBeConsidered($visibility, $visibilityCondition): bool
	{
	    if(!$visibilityCondition instanceof MetaFieldVisibilityModel and !$visibilityCondition instanceof MetaBoxVisibilityModel){
            return false;
        }

		if($visibility === Visibility::IS_BACKEND and $visibilityCondition->isBackEnd()){
			return true;
		}

		if($visibility === Visibility::IS_FRONTEND and $visibilityCondition->isFrontEnd()){
			return true;
		}

		return false;
	}
}