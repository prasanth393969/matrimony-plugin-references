<?php

namespace ACPT\Integrations\ElementorPro\Constants;

use ACPT\Core\Models\Meta\MetaFieldModel;

class TagsConstants
{
	const GROUP_NAME = 'acpt';
	const KEY_SEPARATOR = '::::';

    /**
     * @param MetaFieldModel $field
     *
     * @return string
     */
	public static function getKey(MetaFieldModel $field)
    {
        return $field->getBelongsToLabel() . TagsConstants::KEY_SEPARATOR . $field->getFindLabel() . TagsConstants::KEY_SEPARATOR . $field->getBox()->getName() . TagsConstants::KEY_SEPARATOR . $field->getName() . TagsConstants::KEY_SEPARATOR . $field->getType();
    }
}