<?php

namespace ACPT\Integrations\RankMath\Provider;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\Wordpress\Users;

class FieldProvider
{
	/**
	 * @var int
	 */
	private $find;

	/**
	 * @var string
	 */
	private $belongsTo;

	/**
	 * @var MetaFieldModel
	 */
	private $field;

	/**
	 * FieldProvider constructor.
	 *
	 * @param $find
	 * @param $belongsTo
	 * @param MetaFieldModel $field
	 */
	public function __construct($find, $belongsTo, MetaFieldModel $field)
	{
		$this->find = $find;
		$this->belongsTo = $belongsTo;
		$this->field = $field;
	}

	/**
	 * @return string
	 */
	public function getSlug(): string
	{
		return 'acpt_'.$this->field->getDbName();
	}

	/**
	 * @return string
	 */
	public  function getName(): string
	{
		return $this->field->getUiName();
	}

	/**
	 * @return string
	 */
	public  function getDescription(): string
	{
		return $this->field->getDescription() ?? '';
	}

	/**
	 * @param $args
	 *
	 * @return string|null
	 */
	public  function getData($args = null): ?string
	{
		$belongsTo = ($this->belongsTo === MetaTypes::CUSTOM_POST_TYPE) ? 'post_id' : 'term_id';

		$rawValue = get_acpt_field([
			$belongsTo => (int)$this->find,
			'box_name' => $this->field->getBox()->getName(),
			'field_name' => $this->field->getName(),
            'with_context' => true,
		]);

		if(empty($rawValue)){
			return null;
		}

        if(!isset($rawValue['value'])){
            return null;
        }

        $val = $rawValue['value'];
        $before = $rawValue['before'];
        $after = $rawValue['after'];

		switch ($this->field->getType()){

			// CHECKBOX_TYPE
			// LIST_TYPE
			// SELECT_MULTI_TYPE
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::LIST_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:
				$separator = (!empty($args)) ? $args : ', ';
                $return = [];

				if(!is_array($val)){
					return null;
				}

				foreach ($val as $item){
                    $return[] = $before . $item . $after;
                }

				return implode($separator, $return);

			// CURRENCY_TYPE
			case MetaFieldModel::CURRENCY_TYPE:

				if(!is_array($val)){
					return null;
				}

				if(!isset($val['amount'])){
					return null;
				}

				if(!isset($val['unit'])){
					return null;
				}

				return $before . $val['amount']. " " . $val['unit'] . $after;

			// LENGTH_TYPE
			case MetaFieldModel::LENGTH_TYPE:

				if(!is_array($val)){
					return null;
				}

				if(!isset($val['length'])){
					return null;
				}

				if(!isset($val['unit'])){
					return null;
				}

				return $before . $val['length']. " " . $val['unit'] . $after;

			// WEIGHT_TYPE
			case MetaFieldModel::WEIGHT_TYPE:

				if(!is_array($val)){
					return null;
				}

				if(!isset($val['weight'])){
					return null;
				}

				if(!isset($val['unit'])){
					return null;
				}

				return $before . $val['weight']. " " . $val['unit'] . $after;

			// DATE_TYPE
			case MetaFieldModel::DATE_TYPE:

                if(!isset($val['value'])){
                    return null;
                }

                if(!isset($val['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $val['object'];
                $value = $val['value'];

				$format = get_option( 'date_format' ) ? get_option( 'date_format' ) : 'Y-m-d';
				if(!empty($args)){
					if(Date::isDateFormatValid($args)){
						$format = $args;
					}
				}

				return $before . Date::format($format, $dateTimeObject) . $after;

			// DATE_RANGE_TYPE
			case MetaFieldModel::DATE_RANGE_TYPE:

                if(!isset($val['value'])){
                    return null;
                }

                if(!isset($val['object'])){
                    return null;
                }

                $dateTimeObject = $val['object'];
                $value = $val['value'];

				if(is_array($dateTimeObject) and !empty($dateTimeObject) and count($dateTimeObject) === 2){
					$format = get_option( 'date_format' ) ? get_option( 'date_format' ) : 'Y-m-d';
					if(!empty($args)){
						if(Date::isDateFormatValid($args)){
							$format = $args;
						}
					}

					if(!$dateTimeObject[0] instanceof \DateTime){
					    return null;
                    }

                    if(!$dateTimeObject[1] instanceof \DateTime){
                        return null;
                    }

                    $value  = $before;
					$value .= Date::format($format, $dateTimeObject[0]);
					$value .= ' - ';
					$value .= Date::format($format, $dateTimeObject[1]);
                    $value .= $after;

					return $value;
				}

				return null;

			// DATE_TIME_TYPE
			case MetaFieldModel::DATE_TIME_TYPE:

                if(!isset($val['value'])){
                    return null;
                }

                if(!isset($val['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $val['object'];
                $value = $val['value'];

				$dateFormat = get_option( 'date_format' ) ? get_option( 'date_format' ) : 'Y-m-d';
				$timeFormat = get_option( 'time_format' ) ? get_option( 'time_format' ) : "G:i";
				$format = $dateFormat . ' ' . $timeFormat;

				if(!empty($args)){
					if(Date::isDateFormatValid($args)){
						$format = $args;
					}
				}

				return $before . Date::format($format, $dateTimeObject) . $after;

			// RATING_TYPE
			case MetaFieldModel::RATING_TYPE:
				return $before . Strings::renderRatingAsString($val) . $after;

			// TIME_TYPE
			case MetaFieldModel::TIME_TYPE:

                if(!isset($val['value'])){
                    return null;
                }

                if(!isset($val['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $val['object'];
                $value = $val['value'];

				$format = get_option( 'time_format' ) ? get_option( 'time_format' ) : "G:i";
				if(!empty($args)){
					if(Date::isDateFormatValid($args)){
						$format = $args;
					}
				}

				return $before . Date::format($format, $dateTimeObject) . $after;

            // PHONE_TYPE
            case MetaFieldModel::PHONE_TYPE:
                if(!is_array($val)){
                    return null;
                }

                if(!isset($val['value'])){
                    return null;
                }

                $value = $val['value'];
                $dial = $val['dial'] ?? null;
                $format = Phone::FORMAT_E164;

                return $before . Phone::format($value, $dial, $format) . $after;

			// URL_TYPE
			case MetaFieldModel::URL_TYPE:
				if(!is_array($val)){
					return null;
				}

				if(!isset($val['url'])){
					return null;
				}

				return $val['url'];

			// POST_OBJECT_TYPE
			case MetaFieldModel::POST_OBJECT_TYPE:
			case MetaFieldModel::TERM_OBJECT_TYPE:
			case MetaFieldModel::USER_TYPE:
				return $this->renderRelationalField($val);

			// POST_OBJECT_MULTI_TYPE
			case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
			case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
			case MetaFieldModel::USER_MULTI_TYPE:

				if(!is_array($val)){
					return null;
				}

				$values = [];

				foreach ($val as $value){
					$values[] = $this->renderRelationalField($value);
				}

				$separator = (!empty($args)) ? $args : ', ';

				if(!is_array($values)){
					return null;
				}

				return implode($separator, $values);

			// POST_TYPE
			case MetaFieldModel::POST_TYPE:

				if(is_array($val)){

					$values = [];

					foreach ($val as $value){
						$values[] = $this->renderRelationalField($value);
					}

					$separator = (!empty($args)) ? $args : ', ';

					if(!is_array($values)){
						return null;
					}

					return implode($separator, $values);
				}

				return $this->renderRelationalField($val);

			default:
				return $before . $val . $after;
		}
	}

	/**
	 * @param $rawValue
	 *
	 * @return string|null
	 */
	private function renderRelationalField($rawValue)
	{
		if($rawValue instanceof \WP_User){
			return Users::getUserLabel($rawValue);
		}

		if($rawValue instanceof \WP_Term){
			return $rawValue->name;
		}

		if($rawValue instanceof \WP_Post){
			return $rawValue->post_title;
		}

		return null;
	}
}