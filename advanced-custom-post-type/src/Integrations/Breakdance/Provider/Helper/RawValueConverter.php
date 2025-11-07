<?php

namespace ACPT\Integrations\Breakdance\Provider\Helper;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;

class RawValueConverter
{
	/**
	 * @param $rawValue
	 * @param $fieldType
	 * @param $attributes
	 *
	 * @return array|string|null
	 */
	public static function convert($rawValue, $fieldType, $attributes)
	{
		try {
            $value = $rawValue['value'] ?? null;
            $after = $rawValue['after'] ?? null;
            $before = $rawValue['before'] ?? null;

            if(empty($value)){
                return null;
            }

			switch ($fieldType){

				case MetaFieldModel::RATING_TYPE:

					if(empty($value)){
						return null;
					}

					$size = isset($attributes['size']) ? $attributes['size'] : null;
                    $value = $before. Strings::renderStars($value, $size) . $after;
					break;

				case MetaFieldModel::GALLERY_TYPE:

					if(empty($value)){
						return null;
					}

					if(!is_array($value)){
						return null;
					}

					return $value;

//				case MetaFieldModel::LIST_TYPE:
//
//					if(!is_array($value)){
//						return null;
//					}
//
//					$list = '<ul>';
//
//					foreach ($value as $item){
//						$list .= '<li>'.$before . $item . $after.'</li>';
//					}
//
//					$list .= '</ul>';
//                    $value = $list;
//
//					break;
			}

			return [
                'value' => $value,
                'before' => $before,
                'after' => $after,
            ];
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}