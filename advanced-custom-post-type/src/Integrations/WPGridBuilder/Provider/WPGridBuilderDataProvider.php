<?php

namespace ACPT\Integrations\WPGridBuilder\Provider;

use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\PHP\Email;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\Users;
use ACPT\Utils\Wordpress\WPAttachment;
use ACPT\Utils\Wordpress\WPUtils;

class WPGridBuilderDataProvider
{
	/**
	 * @var array
	 */
	private array $fields = [];

	public function __construct()
	{
		add_filter( 'wp_grid_builder/custom_fields', [ $this, 'customFields' ], 10, 2 );
		add_filter( 'wp_grid_builder/facet/sort_query_vars', [ $this, 'sortQueryVars' ] );
		add_filter( 'wp_grid_builder/block/custom_field', [ $this, 'customFieldBlock' ], 10, 2 );
		add_filter( 'wp_grid_builder/indexer/index_object', [ $this, 'indexObject' ], 10, 3 );
		add_filter( 'wp_grid_builder/metadata', [ $this, 'metadataValue' ], 10, 4 );
	}

	/**
	 * $key is the unique field identifier
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	private function setFields($key = 'key')
	{
		try {
			$fields = [];
			$groups = MetaRepository::get([
			    'clonedFields' => true
            ]);

			foreach ($groups as $group){
				foreach ($group->getBoxes() as $box){
					foreach ($box->getFields() as $field){

						$nowAllowed = [
							MetaFieldModel::FLEXIBLE_CONTENT_TYPE,
							MetaFieldModel::REPEATER_TYPE,
							MetaFieldModel::CLONE_TYPE,
						];

						if(!in_array($field->getType(), $nowAllowed)){
							$fields[] = [
								$key => $this->getFieldKey($field),
								'id' => $field->getId(),
								'label' => 'ACPT > ' . $field->getUiName(),
								'type' => $field->getType(),
								'box' => $field->getBox()->getName(),
								'field' => $field->getName(),
							];
						}

						// register nested fields
						if($field->hasChildren()){
							foreach ($field->getChildren() as $child){
								$fields[] = [
									$key => $this->getFieldKey($child),
									'id' => $child->getId(),
									'label' => 'ACPT > ' . $child->getUiName(),
									'type' => $child->getType(),
									'box' => $child->getBox()->getName(),
									'field' => $child->getName(),
									'parent' => $field->getName(),
								];
							}
						}
					}
				}
			}

			$this->fields = $fields;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			$this->fields = [];
		}
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 *
	 * @return string
	 */
	private function getFieldKey(MetaFieldModel $fieldModel)
	{
		if($fieldModel->getParentField() !== null){
			return "acpt_". $fieldModel->getParentField()->getDbName(). "_" . Strings::toDBFormat($fieldModel->getName());
		}

		return "acpt_". $fieldModel->getDbName();
	}

	/**
	 * @param $fieldId
	 *
	 * @return mixed|null
	 */
	private function getField($fieldId)
	{
		$filtered = array_filter($this->fields, function ($f) use($fieldId){
			return $f['name'] === $fieldId;
		});

		if(empty($filtered)){
			return null;
		}

		return array_values($filtered)[0];
	}

	/**
	 * Retrieve all ACPT fields
	 *
	 * @param array $fields Holds registered custom fields.
	 * @param string $key Key type to retrieve.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function customFields($fields, $key = 'key')
	{
		$this->setFields($key);

		if (!empty($this->fields)) {
			$fields['ACPT'] = array_combine(
				array_column( $this->fields, $key ),
				array_column( $this->fields, 'label' )
			);
		}

		return $fields;
	}

	/**
	 * Change sort query variables
	 *
	 * @param array $queryVars Holds query sort variables.
	 * @return array
	 */
	public function sortQueryVars($queryVars)
	{
		if (empty($queryVars['meta_key'])){
			return $queryVars;
		}

		$queryVars['meta_key'] = str_replace("acpt_","", $queryVars['meta_key']);

		return $queryVars;
	}

	/**
	 * @see https://docs.wpgridbuilder.com/resources/filter-indexer-index-object/
	 *
	 * @param array $rows Holds rows to index.
	 * @param array $objectId Object id to index.
	 * @param array $facet Holds facet settings.
	 *
	 * @return array
	 */
	public function indexObject($rows, $objectId, $facet )
	{
		$source = explode( '/', $facet['source'] );
		$source = reset( $source );

		if ('post_meta' === $source or 'user_meta' === $source or 'term_meta' === $source) {
			$rows = $this->indexACPT($rows, $objectId, $source, $facet);
		}

		return $rows;
	}

	/**
	 * @param $rows
	 * @param $objectId
	 * @param $originalSource
	 * @param $facet
	 *
	 * @return mixed
	 */
	private function indexACPT($rows, $objectId, $originalSource, $facet)
	{
		$source = explode( '/', $facet['source'] );

		if (empty( $source[1])) {
			return $rows;
		}

		if(empty($this->fields)){
			$this->setFields('name');
		}

		$field = $this->getField($source[1]);

		if(empty($field)){
			return $rows;
		}

		$args = [
			'box_name' => $field['box'],
			'field_name' => ((isset($field['parent']) and !empty($field['parent'])) ? $field['parent'] : $field['field']),
            'with_context' => true,
		];

		switch ($originalSource){
			case "post_meta":
				$args['post_id'] = $objectId;
				break;
			case "term_meta":
				$args['term_id'] = $objectId;
				break;
			case "user_meta":
				$args['user_id'] = $objectId;
				break;
		}

		if(!empty($field['forged_by'])){
            $args['forged_by'] = $field['forged_by'];
        }

		$rawValue = get_acpt_field($args);

		if(empty($rawValue)){
			return $rows;
		}

		// Handle repeater values.
		if(isset($field['parent'])){

		    $value = $rawValue['value'];

			if(!is_array($value)){
				return null;
			}

			unset($field['parent']);

			foreach ($value as $item){
				if(isset($item[$field['field']])){
					$rows = array_merge($rows, $this->indexField($field, $item[$field['field']]));
				}
			}

		} else {
			$rows = array_merge($rows, $this->indexField($field, $rawValue));
		}


		return $rows;
	}

	/**
	 * @param array $field
	 * @param array $rawValue
	 *
	 * @return array
	 */
	private function indexField($field, $rawValue)
	{
	    if(!isset($field['type'])){
	        return [];
        }

        if(!is_array($rawValue)){
            return [];
        }

        if(!isset($rawValue['value'])){
            return [];
        }

        $value = $rawValue['value'];
        $after = $rawValue['after'] ?? null;
        $before = $rawValue['before'] ?? null;

		switch ($field['type']){

			// ADDRESS_TYPE
			case MetaFieldModel::ADDRESS_TYPE:
				if (!isset( $value['lat'], $value['lng'])){
					return [];
				}

				return [
					[
						'facet_value' => $value['lat'],
						'facet_name'  => $value['lng'],
					],
				];

			// ADDRESS_MULTI_TYPE
			case MetaFieldModel::ADDRESS_MULTI_TYPE:

				$rows = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $order => $item){

						if(isset($item['lat']) and isset($item['lng'])){
							$rows[] = [
								'facet_value' => $item['lat'],
								'facet_name'  => $item['lng'],
							];
						}
					}
				}

				return $rows;

			// CHECKBOX_TYPE
			// LIST_TYPE
			// SELECT_MULTI_TYPE
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::LIST_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:

				$rows = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $order => $item){

						if (!is_string( $item) ) {
							continue;
						}

						$rows[] = [
							'facet_value' => $item,
							'facet_name'  => $item,
							'facet_order' => $order,
						];
					}
				}

				return $rows;

			// COUNTRY_TYPE
			case MetaFieldModel::COUNTRY_TYPE:

				if(is_array($value) and isset($value['value'])){
					return [
						'facet_value' => $value['value'],
						'facet_name'  => $value['value'],
					];
				}

				return [];

			// CURRENCY_TYPE
			case MetaFieldModel::CURRENCY_TYPE:

				if(
					is_array($value) and
					isset($value['amount']) and
					isset($value['unit'])
				){
                    return [
                        [
                            'facet_value' => $value['amount'],
                            'facet_name'  => $value['amount'] . " " . $value['unit'],
                        ],
                    ];
				}

				return [];

            // DATE_RANGE_TYPE
            case MetaFieldModel::DATE_TYPE:
            case MetaFieldModel::TIME_TYPE:
            case MetaFieldModel::DATE_TIME_TYPE:

                if(!isset($value['value'])){
                    return [];
                }

                if(!isset($value['object'])){
                    return [];
                }

                $dateTimeObject = $value['object'];
                $val = $value['value'];

                if(is_string($val)){
                    return [
                        [
                            'facet_value' => $val,
                            'facet_name'  => $val,
                        ],
                    ];
                }

                return [];

			// DATE_RANGE_TYPE
			case MetaFieldModel::DATE_RANGE_TYPE:

                if(!isset($value['value'])){
                    return [];
                }

                if(!isset($value['object'])){
                    return [];
                }

                $dateTimeObject = $value['object'];
                $val = $value['value'];

				if(is_array($val) and !empty($val) and count($val) === 2){
					$from = $val[0];
					$to = $val[1];

					$value = $from;
					$value .= ' - ';
					$value .= $to;

                    return [
                        [
                            'facet_value' => $value,
                            'facet_name'  => $value,
                        ],
                    ];
				}

				return [];

			// LENGTH_TYPE
			case MetaFieldModel::LENGTH_TYPE:

				if(
					is_array($value) and
					isset($value['length']) and
					isset($value['unit'])
				){
                    return [
                        [
                            'facet_value' => $value['length'],
                            'facet_name'  => $value['length'] . " " . $value['unit'],
                        ],
                    ];
				}

				return [];

            // POST_OBJECT_TYPE
            case MetaFieldModel::POST_TYPE:

                $rows = [];

                if(is_array($value) and !empty($value)){
                    foreach ($value as $order => $item){
                        if($item instanceof \WP_Post){
                            $rows[] = [
                                'facet_value' => $item->ID,
                                'facet_name'  => $item->post_title,
                                'facet_order' => $order,
                            ];
                        } elseif($item instanceof \WP_Term){
                            $rows[] = [
                                'facet_value' => $item->term_id,
                                'facet_name'  => $item->name,
                                'facet_order' => $order,
                            ];
                        } elseif($item instanceof \WP_User){
                            $rows[] = [
                                'facet_value' => $item->ID,
                                'facet_name'  => Users::getUserLabel($item),
                                'facet_order' => $order,
                            ];
                        }
                    }
                }

                return $rows;

			// POST_OBJECT_TYPE
			case MetaFieldModel::POST_OBJECT_TYPE:

				if($value instanceof \WP_Post){
					return [
						[
							'facet_value' => $value->ID,
							'facet_name'  => $value->post_title,
						],
					];
				}

				return [];

			// POST_OBJECT_MULTI_TYPE
			case MetaFieldModel::POST_OBJECT_MULTI_TYPE:

				$rows = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $order => $item){

						if($item instanceof \WP_Post){
							$rows[] = [
								'facet_value' => $item->ID,
								'facet_name'  => $item->post_title,
								'facet_order' => $order,
							];
						}
					}
				}

				return $rows;

			// TERM_OBJECT_TYPE
			case MetaFieldModel::TERM_OBJECT_TYPE:

				if($value instanceof \WP_Term){
					return [
						[
							'facet_value' => $value->term_id,
							'facet_name'  => $value->name,
						],
					];
				}

				return [];

			// TERM_OBJECT_MULTI_TYPE
			case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:

				$rows = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $order => $item){

						if($item instanceof \WP_Term){
							$rows[] = [
								'facet_value' => $item->term_id,
								'facet_name'  => $item->name,
								'facet_order' => $order,
							];
						}
					}
				}

				return $rows;

			// TOGGLE_TYPE
			case MetaFieldModel::TOGGLE_TYPE:

				if($value != 0 or $value != 1){
					return [];
				}

				$name = (int) $value > 0 ? __( 'Yes', 'wp-grid-builder' ) : __( 'No', 'wp-grid-builder' );

				return [
					[
						'facet_value' => $value,
						'facet_name'  => $name,
					],
				];

            // PHONE_TYPE
            case MetaFieldModel::PHONE_TYPE:


                if(!is_array($value)){
                    return [];
                }

                if(!isset($value['dial'])){
                    return [];
                }

                if(!isset($value['value'])){
                    return [];
                }

                return [
                    [
                        'facet_value' => Phone::url($value['value'], $value['dial']),
                        'facet_name'  => $before.Phone::format($value['value'], $value['dial'], Phone::FORMAT_INTERNATIONAL).$after,
                    ],
                ];

			// URL_TYPE
			case MetaFieldModel::URL_TYPE:


				if(!is_array($value)){
					return [];
				}

				if(!isset($value['url'])){
					return [];
				}

				$label = (isset($value['url']) and !empty($value['label'])) ? $value['label'] : $value['url'];

				return [
					[
						'facet_value' => Url::sanitize($rawValue['url']),
						'facet_name'  => $before.$label.$after,
					],
				];

			// USER_TYPE
			case MetaFieldModel::USER_TYPE:

				if($value instanceof \WP_User){
					return [
						[
							'facet_value' => $value->ID,
							'facet_name'  => Users::getUserLabel($value),
						],
					];
				}

				return [];

			// USER_MULTI_TYPE
			case MetaFieldModel::USER_MULTI_TYPE:

				$rows = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $order => $item){

						if($item instanceof \WP_User){
							$rows[] = [
								'facet_value' => $item->ID,
								'facet_name'  => Users::getUserLabel($item),
								'facet_order' => $order,
							];
						}
					}
				}

				return $rows;

			// WEIGHT_TYPE
			case MetaFieldModel::WEIGHT_TYPE:

				if(
					is_array($value) and
					isset($value['weight']) and
					isset($value['unit'])
				){
                    return [
                        [
                            'facet_value' => $value['weight'],
                            'facet_name'  => $value['weight'] . " " . $value['unit'],
                        ],
                    ];
				}

				return [];

			// default, ignore not scalar values
			default:
				if(is_scalar($value)){
					return [
						[
							'facet_value' => $value,
							'facet_name'  => $before.$value.$after,
						],
					];
				}
		}

		return [];
	}

	/**
	 * Return ACPT field value as string
	 *
	 * @param string $output   Custom field output.
	 * @param string $fieldId  Field identifier.
	 * @return mixed
	 */
	public function customFieldBlock($output, $fieldId)
	{
		if(empty($this->fields)){
			$this->setFields('name');
		}

		$field = $this->getField($fieldId);

		if(empty($field)){
			return null;
		}

		$args = [
			'box_name' => $field['box'],
			'field_name' => ((isset($field['parent']) and !empty($field['parent'])) ? $field['parent'] : $field['field']),
            'with_context' => true,
		];

		$object = wpgb_get_object();
		$objectType = wpgb_get_object_type();

		switch ($objectType){
			case "post":
				$args['post_id'] = $object->ID;
				break;
			case "term":
				$args['term_id'] = $object->ID;
				break;
			case "user":
				$args['user_id'] = $object->ID;
				break;
		}

        if(!empty($field['forged_by'])){
            $args['forged_by'] = $field['forged_by'];
        }

		$rawValue = get_acpt_field($args);

		return $this->returnValue($field, $rawValue);
	}

	/**
	 * @param $field
	 * @param $rawValue
	 *
	 * @return mixed|null
	 */
	private function returnValue($field, $rawValue)
	{
		if(empty($rawValue)){
			return null;
		}

        if(!isset($rawValue['value'])){
            return null;
        }

		$value  = $rawValue['value'];
		$after  = $rawValue['after'] ?? null;
		$before = $rawValue['before'] ?? null;

		// Nested fields in a repeater
		if(isset($field['parent'])){

			if(!is_array($value)){
				return null;
			}

			unset($field['parent']);

			$data = [];

			foreach ($value as $item){
				if(isset($item[$field['field']])){
					$data[] = $this->returnValue($field, $item[$field['field']]);
				}
			}

			return $this->renderList($data, $before, $after);
		}

		switch ($field['type']){

			// ADDRESS_TYPE
			case MetaFieldModel::ADDRESS_TYPE:

				if(is_array($value) and isset($value['address'])){
					return $before. $value['address'] . $after;
				}

				return null;

			// ADDRESS_MULTI_TYPE
			case MetaFieldModel::ADDRESS_MULTI_TYPE:

				$address = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $item){
						if(isset($item['address'])){
							$address[] = $item['address'];
						}
					}
				}

				return $this->renderList($address, $before, $after);

			// CHECKBOX_TYPE
			// LIST_TYPE
			// SELECT_MULTI_TYPE
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::LIST_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:
				return $this->renderList($value, $before, $after);

			// COUNTRY_TYPE
			case MetaFieldModel::COUNTRY_TYPE:

				if(is_array($value) and isset($value['value'])){
					return $before . $value['value'] . $after;
				}

				return null;

			// CURRENCY_TYPE
			case MetaFieldModel::CURRENCY_TYPE:

				if(
					is_array($value) and
					isset($value['amount']) and
					isset($value['unit'])
				){
                    $value['amount'] = str_replace([$before, $after], '', $value['amount']);

                    return $before . $value['amount'] . " " . $value['unit'] . $after;
				}

				return null;

            // DATE_TYPE
            case MetaFieldModel::DATE_TYPE:
            case MetaFieldModel::DATE_TIME_TYPE:
            case MetaFieldModel::TIME_TYPE:

                if(!isset($value['value'])){
                    return null;
                }

                if(!isset($value['object'])){
                    return null;
                }

                $dateTimeObject = $value['object'];
                $val = $value['value'];

                switch($field['type']){
                    default:
                    case MetaFieldModel::DATE_TYPE:
                        $defaultFormat = "Y-m-d";
                        break;

                    case MetaFieldModel::DATE_TIME_TYPE:
                        $defaultFormat = "Y-m-d H:i:s";
                        break;

                    case MetaFieldModel::TIME_TYPE:
                        $defaultFormat = "H:i:s";
                        break;
                }

                $format = $value['format'] ?? $defaultFormat;

                return $before . Date::format($format, $dateTimeObject) . $after;

			// DATE_RANGE_TYPE
			case MetaFieldModel::DATE_RANGE_TYPE:

                if(!isset($value['value'])){
                    return null;
                }

                if(!isset($value['object'])){
                    return null;
                }

                $values = $value['value'];
                $dateTimeObjects = $value['object'];
                $format = $value['format'] ?? "Y-m-d";

				if(is_array($values) and !empty($values) and count($values) === 2){

                    if($format !== null and $dateTimeObjects[0] instanceof \DateTime and $dateTimeObjects[1] instanceof \DateTime){
                        $from = Date::format($format, $dateTimeObjects[0]);
                        $to = Date::format($format, $dateTimeObjects[1]);

                        $return  = $before;
                        $return .= $from;
                        $return .= ' - ';
                        $return .= $to;
                        $return .= $after;

                        return $return;
                    }

					return null;
				}

				return null;

			// EMAIL_TYPE
			case MetaFieldModel::EMAIL_TYPE:

				if(is_string($value)){
					return $before . Email::sanitize($value) . $after;
				}

				return null;

			// FILE_TYPE
			case MetaFieldModel::FILE_TYPE:

				if(
					is_array($value) and
					isset($value['file']) and
                    $value['file'] instanceof WPAttachment
				){
					$label = (isset($value['label']) and !empty($value['label'])) ? $value['label'] : $value['file'];
					$src = $rawValue['file']->getSrc();

					$link = '<a href="'.$src.'" target="_blank">';
					$link .= $before;
					$link .= $label;
					$link .= $after;
					$link .= '</a>';

					return $link;
				}

				return null;

			// GALLERY_TYPE
			case MetaFieldModel::IMAGE_SLIDER_TYPE:
			case MetaFieldModel::GALLERY_TYPE:

				$images = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $img){
						if($img instanceof WPAttachment){
							$images[] = $img->render();
						}
					}
				}

				return $this->renderList($images, $before, $after);

			// IMAGE_TYPE
			case MetaFieldModel::IMAGE_TYPE:
				if($value instanceof WPAttachment and $value->isImage()){
					return $before . $value->render() . $after;
				}

				return null;

			// LENGTH_TYPE
			case MetaFieldModel::LENGTH_TYPE:

				if(
					is_array($value) and
					isset($value['length']) and
					isset($value['unit'])
				){
					return $before . $value['length'] . " " . $value['unit'] . $after;
				}

				return null;

			// PHONE_TYPE
			case MetaFieldModel::PHONE_TYPE:

				if(is_array($value)){
				    $phone = $value['value'];
				    $dial = $value['dial'];

					return $before . Phone::format($phone, $dial, Phone::FORMAT_INTERNATIONAL) . $after;
				}

				return null;

            // POST_OBJECT_TYPE
            case MetaFieldModel::POST_TYPE:

                if(is_array($value) and !empty($value)){

                    $data = [];

                    foreach ($value as $item){
                        if($item instanceof \WP_Post){
                            $data[] = $item->post_title;
                        } elseif($item instanceof \WP_Term){
                            $data[] = $item->name;
                        } elseif($item instanceof \WP_User){
                            $data[] = Users::getUserLabel($item);
                        }
                    }

                    return $this->renderList($data, $before, $after);
                }

                return null;

			// POST_OBJECT_TYPE
			case MetaFieldModel::POST_OBJECT_TYPE:

				if($value instanceof \WP_Post){
					return $before . $value->post_title . $after;
				}

				return null;

			// POST_OBJECT_TYPE
			case MetaFieldModel::POST_OBJECT_MULTI_TYPE:

				$posts = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $post){
						if($post instanceof \WP_Post){
							$posts[] = $post->post_title;
						}
					}

				}

				return $this->renderList($posts, $before, $after);

			// RATING_TYPE
			case MetaFieldModel::RATING_TYPE:

				if(is_numeric($value)){
					return $before . ($value/2) . "/5" . $after;
				}

				return null;

			// TABLE_TYPE
			case MetaFieldModel::TABLE_TYPE:

				if(is_string($value) and Strings::isJson($value)){
					$generator = new TableFieldGenerator($value);

					return $generator->generate();
				}

				return null;

			// TERM_OBJECT_TYPE
			case MetaFieldModel::TERM_OBJECT_TYPE:

				if($value instanceof \WP_Term){
					return $before . $value->name . $after;
				}

				return null;

			// TERM_OBJECT_MULTI_TYPE
			case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:

				$terms = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $term){
						if($term instanceof \WP_Term){
							$terms[] = $term->name;
						}
					}

				}

				return $this->renderList($terms, $before, $after);

			// TEXTAREA_TYPE
			case MetaFieldModel::EDITOR_TYPE:
			case MetaFieldModel::TEXTAREA_TYPE:

				if(is_string($value)){
					return $before . WPUtils::renderShortCode($value, true) . $after;
				}

				return null;

			// URL_TYPE
			case MetaFieldModel::URL_TYPE:

				if(!is_array($value)){
					return null;
				}

				if(!isset($value['url'])){
					return null;
				}

				return $before . Url::sanitize($value['url']) . $after;

			// USER_TYPE
			case MetaFieldModel::USER_TYPE:

				if($value instanceof \WP_User){
					return $before . Users::getUserLabel($value) . $after;
				}

				return null;

			// USER_MULTI_TYPE
			case MetaFieldModel::USER_MULTI_TYPE:

				$users = [];

				if(is_array($value) and !empty($value)){
					foreach ($value as $user){
						if($user instanceof \WP_User){
							$users[] = Users::getUserLabel($user);
						}
					}

				}

				return $this->renderList($users, $before, $after);

			// VIDEO_TYPE
			case MetaFieldModel::VIDEO_TYPE;

				if($value instanceof WPAttachment and $value->isVideo()){

                    return $before . $value->render([
                        'type' => 'video/mp4',
                    ]) . $after;
				}

				return null;

			// WEIGHT_TYPE
			case MetaFieldModel::WEIGHT_TYPE:

				if(
					is_array($value) and
					isset($value['weight']) and
					isset($value['unit'])
				){
					return $before . $value['weight'] . " " . $value['unit'] . $after;
				}

				return null;

			default:

			    if(is_string($value)){
                    return $before.$value.$after;
                }

				return $value;
		}
	}

    /**
     * @param      $items
     * @param null $before
     * @param null $after
     *
     * @return string|null
     */
	private function renderList($items, $before = null, $after = null)
	{
		if(!is_array($items)){
			return null;
		}

		if(empty($items)){
			return null;
		}

		$ul = '<ul>';

		foreach ($items as $item){
			$ul .= '<li>' . $before.$item.$after . '</li>';
		}

		$ul .= '</ul>';

		return $ul;
	}

	/**
	 * Return ACPT field value
	 *
	 * @param string  $output    Custom field output.
	 * @param string  $meta_type Type of object metadata is for.
	 * @param integer $object_id ID of the object metadata is for.
	 * @param string  $meta_key  Metadata key.
	 * @return mixed
	 */
	public function metadataValue($output, $meta_type, $object_id, $meta_key)
	{
		$field = explode( 'acpt_', $meta_key );

		if (empty($field[1])) {
			return false;
		}

		// @TODO repeater values ???

		return get_metadata( $meta_type, $object_id, $field[1], true);
	}
}
