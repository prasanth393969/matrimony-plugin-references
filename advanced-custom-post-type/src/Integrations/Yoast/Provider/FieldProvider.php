<?php

namespace ACPT\Integrations\Yoast\Provider;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Includes\ACPT_DB;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\Users;
use WPML\PHP\DateTime;

class FieldProvider
{
    /**
	 * @return array
	 */
	public static function getData()
	{
		$data = [];
		$snippets = [];
		$context = self::resolveContext();

		if(empty($context)){
			return [];
		}

		try {
			// CPT meta fields
			$customPostTypes = CustomPostTypeRepository::get([]);

			foreach ($customPostTypes as $customPostType){
				$data = array_merge($data, self::getFields(MetaTypes::CUSTOM_POST_TYPE, $customPostType->getName(), $context, $snippets));
			}

			// Taxonomies meta fields
			$taxonomies = TaxonomyRepository::get();
			foreach ($taxonomies as $taxonomy){
				$data = array_merge($data, self::getFields(MetaTypes::TAXONOMY, $taxonomy->getSlug(), $context, $snippets));
			}

			return $data;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return [];
		}
	}

    const ALLOWED_FIELDS = [
        MetaFieldModel::CHECKBOX_TYPE,
        MetaFieldModel::CURRENCY_TYPE,
        MetaFieldModel::DATE_TYPE,
        MetaFieldModel::DATE_RANGE_TYPE,
        MetaFieldModel::DATE_TIME_TYPE,
        MetaFieldModel::EMAIL_TYPE,
        MetaFieldModel::ID_TYPE,
        MetaFieldModel::LENGTH_TYPE,
        MetaFieldModel::LIST_TYPE,
        MetaFieldModel::NUMBER_TYPE,
        MetaFieldModel::PHONE_TYPE,
        MetaFieldModel::POST_TYPE,
        MetaFieldModel::POST_OBJECT_TYPE,
        MetaFieldModel::POST_OBJECT_MULTI_TYPE,
        MetaFieldModel::RADIO_TYPE,
        MetaFieldModel::RATING_TYPE,
        MetaFieldModel::SELECT_TYPE,
        MetaFieldModel::SELECT_MULTI_TYPE,
        MetaFieldModel::TERM_OBJECT_TYPE,
        MetaFieldModel::TERM_OBJECT_MULTI_TYPE,
        MetaFieldModel::TEXT_TYPE,
        MetaFieldModel::TEXTAREA_TYPE,
        MetaFieldModel::TIME_TYPE,
        MetaFieldModel::WEIGHT_TYPE,
        MetaFieldModel::URL_TYPE,
        MetaFieldModel::USER_TYPE,
        MetaFieldModel::USER_MULTI_TYPE,
    ];

    /**
	 * @param $belongsTo
	 * @param $find
	 * @param $context
	 * @param $snippets
	 *
	 * @return array
	 */
	private static function getFields($belongsTo, $find, $context, &$snippets)
	{
		$fields = [];

		try {
			$groups = MetaRepository::get([
				'belongsTo' => $belongsTo,
				'find' => $find
			]);

			foreach ($groups as $group){
				foreach ($group->getBoxes() as $box){
					foreach ($box->getFields() as $field){

						$snippet = self::getSnippet($field);

						// avoid duplicates snippet
						if(in_array($field->getType(), self::ALLOWED_FIELDS) and !in_array($snippet, $snippets)){
							$fields[] = [
								'snippet' => $snippet,
								'replace' => self::getReplace($field, $context),
								'help' => self::getHelp($field),
							];

							$snippets[] = $snippet;
						}
					}
				}
			}

			return $fields;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return [];
		}
	}

	/**
	 * @param MetaFieldModel $field
	 *
	 * @return string
	 */
	private static function getSnippet(MetaFieldModel $field)
	{
		return '%%acpt_'.$field->getDbName().'%%';
	}

	/**
	 * @param MetaFieldModel $field
	 * @param array $context
	 *
	 * @return mixed|string|null
	 */
	private static function getReplace(MetaFieldModel $field, $context = [])
	{
		if(empty($context) === null){
			return null;
		}

		if(!isset($context['context'])){
			return null;
		}

		if(!isset($context['id'])){
			return null;
		}

		$rawValue = get_acpt_field([
			$context['context'] => (int)$context['id'],
			'box_name' => $field->getBox()->getName(),
			'field_name' => $field->getName(),
			'with_context' => true,
		]);

		if(empty($rawValue)){
			return null;
		}

		if(!isset($rawValue['value'])){
		    return null;
        }

		$val = $rawValue['value'];
		$after = $rawValue['after'];
		$before = $rawValue['before'];

		switch ($field->getType()){

			// CHECKBOX_TYPE
			// LIST_TYPE
			// SELECT_MULTI_TYPE
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::LIST_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:
				$separator = (!empty($args)) ? $args : ', ';
                $values = [];

				if(!is_array($val)){
					return null;
				}

				foreach ($val as $item){
				    $values[] = $before . $item . $after;
                }

				return implode($separator, $values);

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
            case MetaFieldModel::PHONE_TYPE:

                $format = Phone::FORMAT_E164;
                $value = $val['value'] ?? null;
                $dial = $val['dial'] ?? null;

                return Phone::format($value, $dial, $format);

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

					if(!$dateTimeObject[0] instanceof DateTime){
					    return null;
                    }

                    if(!$dateTimeObject[1] instanceof DateTime){
                        return null;
                    }

                    $value = $before;
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
				return self::renderRelationalField($val);

			// POST_OBJECT_MULTI_TYPE
			case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
			case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
			case MetaFieldModel::USER_MULTI_TYPE:

				if(!is_array($val)){
					return null;
				}

				$values = [];

				foreach ($val as $value){
					$values[] = self::renderRelationalField($value);
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
						$values[] = self::renderRelationalField($value);
					}

					$separator = (!empty($args)) ? $args : ', ';

					if(!is_array($values)){
						return null;
					}

					return implode($separator, $values);
				}

				return self::renderRelationalField($val);

			default:
				return $before . $val . $after;
		}
	}

	/**
	 * @param MetaFieldModel $field
	 *
	 * @return string
	 */
	private static function getHelp(MetaFieldModel $field)
	{
		return 'Gets the value for `'.$field->getUiName().'` field';
	}

	/**
	 * @param $rawValue
	 *
	 * @return string|null
	 */
	private static function renderRelationalField($rawValue)
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

	/**
	 * @return array
	 */
	private static function resolveContext()
	{
		global $post;
		global $wpdb;

        $pagenow = Url::pagenow();

		if($post){
			return [
				'context' => 'post_id',
				'id' => $post->ID,
			];
		}

		if($pagenow === "post.php" and isset($_GET['post'])){
			return [
				'context' => 'post_id',
				'id' => $_GET['post'],
			];
		}

		if(isset($_GET['p'])){
			return [
				'context' => 'post_id',
				'id' => $_GET['p'],
			];
		}

		$slug = Url::getLastPartOfUrl(Url::fullUrl());

		if(!empty($slug)){
			$page = ACPT_DB::getResults("SELECT ID FROM {$wpdb->prefix}posts WHERE post_name = %s", [$slug]);

			if(!empty($page)){
				return [
					'context' => 'post_id',
					'id' => $page[0]->ID,
				];
			}
		}

		if(isset($_GET['taxonomy']) and isset($_GET['tag_ID'])){
			return [
				'context' => 'term_id',
				'id' => $_GET['tag_ID']
			];
		}

		if(isset($_GET['cat'])){
			return [
				'context' => 'term_id',
				'id' => $_GET['cat']
			];
		}

		if(isset($_GET['tag'])){
			return [
				'context' => 'term_id',
				'id' => $_GET['tag']
			];
		}

		if(!empty($slug)){
			$term = ACPT_DB::getResults("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = %s", [$slug]);

			if(!empty($term)){
				return [
					'context' => 'term_id',
					'id' => $term[0]->term_id,
				];
			}
		}

		return [];
	}
}