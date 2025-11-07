<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use Breakdance\DynamicData\StringData;

class ACPTUrlAsUrlField extends ACPTStringAsUrlField
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

		if(!isset($value['url']) or empty($value['url'])){
			return StringData::emptyString();
		}

		$href = $value['url'];

		return StringData::fromString($href);
	}
}
