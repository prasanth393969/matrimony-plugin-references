<?php

namespace ACPT\Utils\Checker;

use ACPT\Core\Repository\MetaRepository;

class FieldsVisibilityLiveChecker
{
	/**
	 * @param $visibility
	 * @param $elementId
	 * @param $belongsTo
	 * @param $liveData
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function check(
		$visibility,
		$elementId,
		$belongsTo,
		$liveData
	)
	{
		$check = [];

		foreach ($liveData as $rawDatum){

			$formId = $rawDatum['formId'];
			$metaField = MetaRepository::getMetaFieldById($formId);

			// fields inside a repeater
			if($metaField->hasParent() and isset($rawDatum['fieldIndex']) and $rawDatum['fieldIndex'] !== null) {

			    // if parent field is hidden all the children are hidden
			    $parentVisibility = $check[$metaField->getParentId()];

			    if($parentVisibility === false){
			        $fieldVisibility = false;
                } else {
                    $fieldVisibility = FieldVisibilityChecker::check(
                        $visibility,
                        $elementId,
                        $belongsTo,
                        $metaField,
                        $liveData,
                        (int)$rawDatum['fieldIndex']
                    );
                }

				$check[ $formId ][ (int) $rawDatum['fieldIndex'] ] = $fieldVisibility;

			// fields inside a flexible block
			} elseif($metaField->hasParentBlock() and isset($rawDatum['blockIndex']) and $rawDatum['blockIndex'] !== null and isset($rawDatum['blockName']) and $rawDatum['blockName'] !== null){

			    // check if $check[ $formId ] exists and is not an array
				if(isset($check[ $formId ]) and !is_array($check[ $formId ])){
					$check[ $formId ] = [];
				}

                // if parent field is hidden all the children are hidden
                $parentBlock = ($metaField->getParentBlock() !== null) ? $metaField->getParentBlock() : MetaRepository::getMetaBlockById($metaField->getBlockId());
                $parentVisibility = $check[$parentBlock->getMetaField()->getId()];

                if($parentVisibility === false){
                    $fieldVisibility = false;
                } else {
                    $fieldVisibility = FieldVisibilityChecker::check(
                        $visibility,
                        $elementId,
                        $belongsTo,
                        $metaField,
                        $liveData,
                        (int)$rawDatum['fieldIndex'],
                        $rawDatum['blockName'],
                        (int)$rawDatum['blockIndex']
                    );
                }

				$check[ $formId ][ $rawDatum['blockName'] ][ (int)$rawDatum['blockIndex'] ][ (int) $rawDatum['fieldIndex'] ] = $fieldVisibility;

			// other fields
			} else {
				$check[$formId] = FieldVisibilityChecker::check(
					$visibility,
					$elementId,
					$belongsTo,
					$metaField,
					$liveData
				);
			}
		}

		return $check;
	}
}