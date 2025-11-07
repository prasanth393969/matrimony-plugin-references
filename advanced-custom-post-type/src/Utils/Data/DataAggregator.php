<?php

namespace ACPT\Utils\Data;

use ACPT\Constants\ExtraFields;

/**
 * Class RepeaterDataAggregator
 * @package ACPT\Utils\Data
 */
class DataAggregator
{
	/**
	 * Aggregate saved nested fields
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function aggregateNestedFieldsData($data)
	{
		if(!is_array($data)){
			return [];
		}

		$dataToRender = [];
		$keys = array_keys($data);

		if(!isset($keys[0])){
			return [];
		}

		$firstKey = $keys[0];
		$firstElement = $data[$firstKey];

		if(empty($firstElement)){
		    return [];
        }

		if(!is_array($firstElement)){
            return [];
        }

		// filter only numeric indexes
        // (get rid of dirty data)
		$filteredFirstElement = array_filter(array_keys($firstElement), function($key){
            return is_numeric($key);
        });

		if(is_countable($filteredFirstElement)){
			for ($i=0; $i < count($filteredFirstElement); $i++){
				$element = [];

                if(!is_array($data)){
                    return [];
                }

				foreach (array_keys($data) as $index => $key){

				    $el = [
                        'key' => $key,
                        'type' => isset($data[$key][$i]['type']) ? $data[$key][$i]['type'] : null,
                        'value' => isset($data[$key][$i]['value']) ? $data[$key][$i]['value'] : null,
                    ];

                    // Add extra data here
				    foreach (ExtraFields::ALLOWED_VALUES as $e){
                        if(isset($data[$key][$i][$e]) and !isset($el[$e])){
                            $el[$e] = $data[$key][$i][$e];
                        }
                    }

					$element[] = $el;
				}

				$dataToRender[] = $element;
			}
		}

		return $dataToRender;
	}
}