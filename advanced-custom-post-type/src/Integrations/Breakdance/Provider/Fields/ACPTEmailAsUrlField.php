<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\PHP\Email;
use Breakdance\DynamicData\StringData;

class ACPTEmailAsUrlField extends ACPTStringAsUrlField
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

		if(!is_string($value) or $value === null){
			return StringData::emptyString();
		}

		$value = "mailto:".Email::sanitize($value);

		return StringData::fromString($value);
	}
}
