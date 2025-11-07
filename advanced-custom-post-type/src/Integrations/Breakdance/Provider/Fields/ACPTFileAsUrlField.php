<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\Wordpress\WPAttachment;
use Breakdance\DynamicData\StringData;

class ACPTFileAsUrlField extends ACPTStringAsUrlField
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

		if(!isset($value['file']) or empty($value['file'])){
			return StringData::emptyString();
		}

		$file = $value['file'];
        $wpAttachment = WPAttachment::fromId($file);

        if($wpAttachment->isEmpty()){
            return StringData::emptyString();
        }

		$src = $wpAttachment->getSrc();

		return StringData::fromString($src);
	}
}
