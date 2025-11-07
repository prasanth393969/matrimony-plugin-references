<?php

namespace ACPT\Utils\PHP;

class JSON
{
    /**
     * @param $string
     * @return bool
     */
    public static function isValid($string)
    {
        if(is_numeric($string)){
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

	/**
	 * @param array $object
	 *
	 * @return string
	 */
	public static function arrayToEscapedJson($object = [])
	{
		$json = json_encode($object);
		$json = str_replace('"', '&quot;', $json);

		return $json;
	}

	/**
	 * @param $json
	 *
	 * @return array
	 */
	public static function escapedJsonToArray($json): array
	{
		$array = str_replace('&quot;', '"', $json);
		$array = json_decode($array, true);

		return $array;
	}
}