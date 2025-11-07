<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\Wordpress\WPAttachment;
use Breakdance\DynamicData\StringData;

class ACPTVideoAsUrlField extends ACPTStringAsUrlField
{
	/**
	 * @param mixed $attributes
	 *
	 * @return StringData
	 * @throws \Exception
	 */
	public function handler($attributes): StringData
	{
		$value = ACPTField::getValue($this->fieldModel, $attributes);

        $after = $value['after'] ?? null;
        $before = $value['before'] ?? null;
        $value = $value['value'] ?? null;

		if(empty($value)){
			return StringData::emptyString();
		}

        $wpAttachment = WPAttachment::fromId($value);

        if($wpAttachment->isEmpty()){
            return StringData::emptyString();
        }

		return StringData::fromString($wpAttachment->getSrc());
	}
}
