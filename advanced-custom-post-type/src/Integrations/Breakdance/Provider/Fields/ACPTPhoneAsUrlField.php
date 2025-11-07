<?php

namespace ACPT\Integrations\Breakdance\Provider\Fields;

use ACPT\Utils\PHP\Phone;
use Breakdance\DynamicData\StringData;

class ACPTPhoneAsUrlField extends ACPTStringAsUrlField
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

        $val = $value['value'] ?? null;
        $dial = $value['dial'] ?? null;

		if(!is_string($val) or $val === null){
			return StringData::emptyString();
		}

		return StringData::fromString(Phone::format($val, $dial, Phone::FORMAT_RFC3966));
	}
}
