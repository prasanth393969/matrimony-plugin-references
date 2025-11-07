<?php

namespace ACPT\Utils\PHP;

use ACPT\Constants\Address as AddressConstants;

class Address
{
	/**
	 * @param $string
	 *
	 * @return array
	 */
	public static function toHumanReadable($string)
	{
		if(empty($string)){
			return null;
		}

		return str_replace(AddressConstants::MULTI_STRING_SEPARATOR, ', ', $string);
	}

	/**
	 * @param $string
	 *
	 * @return array
	 */
	public static function fetchMulti($string)
	{
		if(empty($string)){
			return [];
		}

		return explode(AddressConstants::MULTI_STRING_SEPARATOR, $string);
	}

	/**
	 * @param array $values
	 *
	 * @return string|null
	 */
	public static function formatMulti(array $values)
	{
		if(empty($values)){
			return null;
		}

		return implode(AddressConstants::MULTI_STRING_SEPARATOR, $values);
	}
}