<?php

namespace ACPT\Integrations\Zion\Provider\Utils;

use ACPT\Core\Repository\MetaRepository;
use ACPT\Integrations\Zion\Provider\Constants\ZionConstants;

class FieldSettings
{
	/**
	 * @param $fieldKey
	 *
	 * @return array|bool
	 * @throws \Exception
	 */
	public static function get($fieldKey)
	{
		$field = explode(ZionConstants::FIELD_KEY_SEPARATOR, $fieldKey);

		if(empty($field)){
			return false;
		}

		$belongsTo = $field[0];
		$find = $field[1];
		$fieldId = $field[2];
		$forgedBy = (isset($field[3]) and !empty($field[3])) ? $field[3] : null;

		$metaFieldSettings = MetaRepository::getMetaFieldById($fieldId);
		$metaFieldSettings->setBelongsToLabel($belongsTo);
		$metaFieldSettings->setFindLabel($find);

		if ($metaFieldSettings === null){
			return false;
		}

		if($forgedBy !== null){
            $cloneField = MetaRepository::getMetaFieldById($forgedBy);
            $metaFieldSettings->forgeBy($cloneField);
        }

		return [
			'id' => $fieldId,
			'belongsTo' => $belongsTo,
			'find' => $find,
			'model' => $metaFieldSettings,
		];
	}
}