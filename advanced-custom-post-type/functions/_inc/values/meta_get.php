<?php

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Visibility;
use ACPT\Core\CQRS\Query\FetchMetaFieldValueQuery;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Validators\ArgumentsArrayValidator;
use ACPT\Utils\Checker\FieldVisibilityChecker;
use ACPT\Utils\Checker\MetaGroupVisibilityChecker;
use ACPT\Utils\Data\Meta;

if( !function_exists('get_acpt_fields') )
{
	/**
	 * Fetch all box field values
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function get_acpt_fields(array $args = [])
	{
		try {
			// validate array
			$mandatory_keys = [
				'post_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'term_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'user_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'comment_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'option_page' => [
					'required' => false,
					'type' => 'string',
				],
				'box_name' => [
					'required' => false,
					'type' => 'string',
				],
                'format' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => [
                        'complete',
                        'only_value',
                    ]
                ],
                'assoc' => [
                    'required' => false,
                    'type' => 'boolean',
                ],
                'with_context' => [
                    'required' => false,
                    'type' => 'boolean',
                ],
                'return' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => [
                        'raw',
                        'object',
                    ]
                ],
			];

			$validator = new ArgumentsArrayValidator();

			if(!$validator->validate($mandatory_keys, $args)){
				return [];
			}

			$assoc = $args['assoc'] ?? false;
			$boxName = $args['box_name'] ?? $args['boxName'] ?? null;
            $return = [];

            unset($args['assoc']);

            // get all box fields
			if($boxName !== null){
                $meta_box_model = MetaRepository::getMetaBoxByName($boxName);

                if($meta_box_model->getGroup()->isVisible($args)){
                    foreach ($meta_box_model->getFields() as $field_index => $field_model){

                        $new_args = array_merge($args, [
                            'field_name' => $field_model->getName(),
                            'format' => $args['format'] ?? 'only_value',
                            'with_context' => $args['with_context'] ?? false,
                            'return' => $args['return'] ?? 'object',
                        ]);
                        $get_acpt_field = get_acpt_field($new_args);

                        if($get_acpt_field !== null){
                            $index =  $assoc ? $field_model->getDbName() : $field_index;
                            $return[$index] = $get_acpt_field;
                        }
                    }
                }

            // get all fields
            } else {
                $meta_groups = MetaRepository::get([]);

                foreach ($meta_groups as $meta_group){
                    if($meta_group->isVisible($args)){
                        foreach ($meta_group->getBoxes() as $meta_box_model){
                            foreach ($meta_box_model->getFields() as $field_index => $field_model){

                                $new_args = array_merge($args, [
                                    'box_name' => $meta_box_model->getName(),
                                    'field_name' => $field_model->getName(),
                                    'format' => $args['format'] ?? 'only_value',
                                    'with_context' => $args['with_context'] ?? false,
                                    'return' => $args['return'] ?? 'object',
                                ]);
                                $get_acpt_field = get_acpt_field($new_args);

                                if($get_acpt_field !== null){
                                    $index =  $assoc ? $field_model->getDbName() : $field_index;
                                    $return[$index] = $get_acpt_field;
                                }
                            }
                        }
                    }
                }
            }

			return $return;

		} catch (\Exception $exception){
			return [];
		}
	}
}

if( !function_exists('acpt_field_has_rows') ){

    /**
     * Used to loop through a parent field's value.
     * Only usable for Repeater and List fields.
     *
     * @param array $args
     *
     * @return bool
     */
    function acpt_field_has_rows(array $args = [])
    {
        try {
            $get_acpt_field = get_acpt_field($args);
            $meta_field_model = MetaRepository::getMetaFieldByName([
                'boxName' => $args['box_name'] ?? $args['boxName'],
                'fieldName' => $args['field_name'] ?? $args['fieldName'],
            ]);

            if($meta_field_model === null){
	            return false;
            }

            if(
            	$meta_field_model->getType() !== MetaFieldModel::REPEATER_TYPE and
	            $meta_field_model->getType() !== MetaFieldModel::LIST_TYPE
            ){
                return false;
            }

            if(!is_array($get_acpt_field)){
                return false;
            }

            return !empty($get_acpt_field);

        } catch (\Exception $exception){
            return false;
        }
    }
}

if( !function_exists('acpt_field_has_blocks') )
{
	function acpt_field_has_blocks($args = [])
	{
		try {
			$get_acpt_field = get_acpt_field($args);
			$meta_field_model = MetaRepository::getMetaFieldByName([
				'boxName' => $args['box_name'] ?? $args['boxName'],
				'fieldName' => $args['field_name'] ?? $args['fieldName'],
			]);

			if($meta_field_model->getType() !== MetaFieldModel::FLEXIBLE_CONTENT_TYPE){
				return false;
			}

			if(!is_array($get_acpt_field)){
				return false;
			}

			if(!isset($get_acpt_field['blocks'])){
				return false;
			}

			return !empty($get_acpt_field['blocks']);

		} catch (\Exception $exception){
			return false;
		}
	}
}

if( !function_exists('get_acpt_field') )
{
	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_acpt_field($args = [])
	{
		try {
			// validate array
			$mandatory_keys = [
				'post_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'term_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'user_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'comment_id' => [
					'required' => false,
					'type' => 'integer',
				],
				'option_page' => [
					'required' => false,
					'type' => 'string',
				],
				'box_name' => [
					'required' => true,
					'type' => 'string',
				],
				'field_name' => [
					'required' => true,
					'type' => 'string',
				],
				'with_context' => [
					'required' => false,
					'type' => 'boolean',
				],
                'format' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => [
                        'complete',
                        'only_value',
                    ]
                ],
                'return' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => [
                        'raw',
                        'object',
                    ]
                ],
			];

			$validator = new ArgumentsArrayValidator();

			if(!$validator->validate($mandatory_keys, $args)){
				return null;
			}

			if(empty($args['return'])){
                $args['return'] = 'object';
            }

			$box_name = $args['box_name'] ?? $args['boxName'];
			$field_name = explode(".", $args['field_name']);

			$meta_field_model = MetaRepository::getMetaFieldByName([
				'boxName' => $box_name,
				'fieldName' => $field_name[0],
			]);

            // check for saved field id. Used by cloned fields
			if($meta_field_model === null){

                if(isset($args['post_id'])){
                    $belongs_to = MetaTypes::CUSTOM_POST_TYPE;
                    $location = $args['post_id'];
                } elseif (isset($args['term_id'])){
                    $belongs_to = MetaTypes::TAXONOMY;
                    $location = $args['term_id'];
                } elseif (isset($args['user_id'])){
                    $belongs_to = MetaTypes::USER;
                    $location = $args['user_id'];
                } elseif (isset($args['comment_id'])){
                    $belongs_to = MetaTypes::COMMENT;
                    $location = $args['comment_id'];
                } elseif (isset($args['option_page'])){
                    $belongs_to = MetaTypes::OPTION_PAGE;
                    $location = $args['option_page'];
                }

                if(empty($location) and empty($belongs_to)){
                    return null;
                }

                $key = $box_name."_".$field_name[0]."_id";
                $forged_by_key = $box_name."_".$field_name[0]."_forged_by";
			    $field_id = Meta::fetch($location, $belongs_to, $key);

			    if(empty($field_id)){
                    return null;
                }

                $meta_field_model = MetaRepository::getMetaFieldById($field_id);

			    if($meta_field_model === null){
                    return null;
                }

                if($forged_by_key !== null){
                    $forged_by_id = Meta::fetch($location, $belongs_to, $forged_by_key);

                    if($forged_by_id !== null){
                        $forged_by_field = MetaRepository::getMetaFieldById($forged_by_id);

                        if($forged_by_field !== null){
                            $meta_field_model->forgeBy($forged_by_field);
                        }
                    }
                }
            }

			$query = new FetchMetaFieldValueQuery($meta_field_model, $args);
			$result = $query->execute();
			$format = $args['format'] ?? 'only_value';
            $field_object = $meta_field_model->toStdObject();

			if(is_array($result) and count($field_name) === 2){
				$r = [];

				foreach ($result as $item){
					if(isset($item[$field_name[1]])){
						$r[] = Meta::format($field_object, $item[$field_name[1]], $format);
					}
				}

				return $r;
			}

			if(is_array($result) and count($field_name) === 3){
				$r = [];

				foreach ($result as $item){
					if(isset($item[$field_name[1]])){
						foreach ($item[$field_name[1]] as $nested_item){
							if(isset($nested_item[$field_name[2]])){
								$r[] = Meta::format($field_object, $nested_item[$field_name[2]], $format);
							}
						}
					}
				}

				return $r;
			}

			return Meta::format($field_object, $result, $format);

		} catch (\Exception $exception){
			return null;
		}
	}
}

if( !function_exists('get_acpt_child_field') )
{
	/**
	 * @param array $args
	 *
	 * @return mixed|null
	 */
	function get_acpt_child_field(array $args = [])
	{
		$mandatory_keys = [
			'post_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'term_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'user_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'comment_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'option_page' => [
				'required' => false,
				'type' => 'string',
			],
			'box_name' => [
				'required' => true,
				'type' => 'string',
			],
			'field_name' => [
				'required' => true,
				'type' => 'string',
			],
			'parent_field_name' => [
				'required' => true,
				'type' => 'string',
			],
			'index' => [
				'required' => true,
				'type' => 'string|integer',
			],
			'with_context' => [
				'required' => false,
				'type' => 'boolean',
			],
            'format' => [
                'required' => false,
                'type' => 'string',
                'enum' => [
                    'complete',
                    'only_value',
                ]
            ],
            'return' => [
                'required' => false,
                'type' => 'string',
                'enum' => [
                    'raw',
                    'object',
                ]
            ],
		];

		$validator = new ArgumentsArrayValidator();

		if(!$validator->validate($mandatory_keys, $args)){
			return null;
		}

		$index = explode(".",$args['index']);

		$parentFieldName = $args['parent_field_name'] ?? $args['parentFieldName'];
		$parent_field_name = explode(".", $parentFieldName);

		$parent_field = null;

		if(isset($args['post_id'])){
			$parent_field = get_acpt_field([
				'post_id' => $args['post_id'] ?? $args['postId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
				'with_context' => $args['with_context'] ?? false,
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['term_id'])){
			$parent_field = get_acpt_field([
				'term_id' => $args['term_id'] ?? $args['termId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
				'with_context' => $args['with_context'] ?? false,
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['user_id'])){
			$parent_field = get_acpt_field([
				'user_id' => $args['user_id'] ?? $args['userId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
				'with_context' => $args['with_context'] ?? false,
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['option_page'])){
			$parent_field = get_acpt_field([
				'option_page' => $args['option_page'] ?? $args['optionPage'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
				'with_context' => $args['with_context'] ?? false,
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		}

		if($parent_field === null){
			return null;
		}

		if(!isset($parent_field[$index[0]])){
			return null;
		}

		$fieldName = $args['field_name'] ?? $args['fieldName'];

		if(count($index) === 1){
			return (isset($parent_field[$index[0]][$fieldName])) ? $parent_field[$index[0]][$fieldName] : null;
		}

		if(count($index) === 2){
			return $parent_field[$index[0]][$parent_field_name[1]][$index[1]][$fieldName] ?? null;
		}

		if(count($index) === 3){
			return $parent_field[$index[0]][$parent_field_name[1]][$index[1]][$parent_field_name[2]][$index[2]][$fieldName] ?? null;
		}
	}
}

if( !function_exists('get_acpt_block') ){

	function get_acpt_block(array $args = [])
	{
		// validate array
		$mandatory_keys = [
			'post_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'term_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'user_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'comment_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'option_page' => [
				'required' => false,
				'type' => 'string',
			],
			'box_name' => [
				'required' => true,
				'type' => 'string',
			],
			'parent_field_name' => [
				'required' => true,
				'type' => 'string',
			],
			'block_name' => [
				'required' => true,
				'type' => 'string',
			],
            'format' => [
                'required' => false,
                'type' => 'string',
                'enum' => [
                    'complete',
                    'only_value',
                ]
            ],
            'return' => [
                'required' => false,
                'type' => 'string',
                'enum' => [
                    'raw',
                    'object',
                ]
            ],
		];

		$validator = new ArgumentsArrayValidator();

		if(!$validator->validate($mandatory_keys, $args)){
			return null;
		}

		$parent_field = null;
		$parentFieldName = $args['parent_field_name'] ?? $args['parentFieldName'];
		$blockName = $args['block_name'] ?? $args['block_name'];
		$parent_field_name = explode(".", $parentFieldName);
		$block_name = explode(".", $blockName);

		if(isset($args['post_id'])){
			$parent_field = get_acpt_field([
				'post_id' => $args['post_id'] ?? $args['postId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['term_id'])){
			$parent_field = get_acpt_field([
				'term_id' => $args['term_id'] ?? $args['termId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['user_id'])){
			$parent_field = get_acpt_field([
				'user_id' => $args['user_id'] ?? $args['userId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['option_page'])){
			$parent_field = get_acpt_field([
				'option_page' => $args['option_page'] ?? $args['optionPage'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $parent_field_name[0],
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		}

		if($parent_field === null){
			return null;
		}

		if(!isset($parent_field['blocks'])){
			return null;
		}

		$blocks = [];

		foreach ($parent_field['blocks'] as $block){
			foreach (array_keys($block) as $blockName){

				if($blockName === $block_name[0]){

					if(count($block_name) === 1){
						$blocks[] = $block;
					}

					if(count($block_name) === 2){

						foreach ($block as $block_index => $nested_blocks){
							foreach ($nested_blocks as $nested_block){
								if(isset($nested_block['blocks'])){
									foreach ($nested_block['blocks'] as $nested_inside_block){
										foreach (array_keys($nested_inside_block) as $nestedBlockName){
											if($nestedBlockName === $block_name[1]){
												$blocks[] = $nested_inside_block;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $blocks;
	}
}

if( !function_exists('get_acpt_block_child_field') )
{
	function get_acpt_block_child_field($args = [])
	{
		// validate array
		$mandatory_keys = [
			'post_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'term_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'user_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'comment_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'option_page' => [
				'required' => false,
				'type' => 'string',
			],
			'box_name' => [
				'required' => true,
				'type' => 'string',
			],
			'field_name' => [
				'required' => true,
				'type' => 'string',
			],
			'parent_field_name' => [
				'required' => true,
				'type' => 'string',
			],
			'index' => [
				'required' => true,
				'type' => 'string|integer',
			],
			'block_name' => [
				'required' => false,
				'type' => 'string',
			],
			'block_index' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'with_context' => [
				'required' => false,
				'type' => 'boolean',
			],
            'format' => [
                'required' => false,
                'type' => 'string',
                'enum' => [
                    'complete',
                    'only_value',
                ]
            ],
            'return' => [
                'required' => false,
                'type' => 'string',
                'enum' => [
                    'raw',
                    'object',
                ]
            ],
		];

		$validator = new ArgumentsArrayValidator();

		if(!$validator->validate($mandatory_keys, $args)){
			return null;
		}

		$parent_field = null;
		$blockIndex = $args['block_index'] ?? $args['blockIndex'];
		$blockName = $args['block_name'] ?? $args['blockName'];
		$block_index = explode(".", $blockIndex);
		$block_name = explode(".", $blockName);

		if(isset($args['post_id'])){
			$parent_field = get_acpt_field([
				'post_id' => $args['post_id'] ?? $args['postId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $args['parent_field_name'] ?? $args['parentFieldName'],
				'with_context' => $args['with_context'] ?? false,
				'format' => $args['format'] ?? 'only_value',
				'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['term_id'])){
			$parent_field = get_acpt_field([
				'term_id' => $args['term_id'] ?? $args['termId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $args['parent_field_name'] ?? $args['parentFieldName'],
				'with_context' => $args['with_context'] ?? false,
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['user_id'])){
			$parent_field = get_acpt_field([
				'user_id' => $args['user_id'] ?? $args['userId'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $args['parent_field_name'] ?? $args['parentFieldName'],
				'with_context' => $args['with_context'] ?? false,
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		} elseif(isset($args['option_page'])){
			$parent_field = get_acpt_field([
				'option_page' => $args['option_page'] ?? $args['optionPage'],
				'box_name' => $args['box_name'] ?? $args['boxName'],
				'field_name' => $args['parent_field_name'] ?? $args['parentFieldName'],
				'with_context' => $args['with_context'] ?? false,
                'format' => $args['format'] ?? 'only_value',
                'return' => $args['return'] ?? 'object',
			]);
		}

		if($parent_field === null){
			return null;
		}

		if(!isset($parent_field['blocks'])){
			return null;
		}

		if(!isset($parent_field['blocks'][$block_index[0]])){
			return null;
		}

		$fieldName = $args['field_name'] ?? $args['fieldName'];

		if(count($block_index) === 1){
			return $parent_field['blocks'][$block_index[0]][$block_name[0]][$fieldName][$args['index']] ?? null;
		}

		if(count($block_index) === 2){
			return $parent_field['blocks'][$block_index[0]][$block_name[0]][0]['blocks'][$block_index[1]][$block_name[1]][0]['blocks'][0][$block_name[1]][$fieldName][$args['index']];
		}

//		if(count($block_index) === 3){
//			return $parent_field['blocks'][$block_index[0]][$block_name[0]]['blocks'][$block_index[1]][$block_name[1]]['blocks'][$block_index[2]][$block_name[2]][$args['field_name']][$args['index']] ?? null;
//		}
	}
}

if( !function_exists('acpt_field') )
{
	/**
	 * @param array $args
	 *
	 * @return string|null
	 */
	function acpt_field($args = [])
	{
		// validate array
		$mandatory_keys = [
			'post_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'term_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'user_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'comment_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'option_page' => [
				'required' => false,
				'type' => 'string',
			],
			'box_name' => [
				'required' => true,
				'type' => 'string',
			],
			'field_name' => [
				'required' => true,
				'type' => 'string',
			],
			'date_format' => [
				'required' => false,
				'type' => 'string',
			],
			'width' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'height' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'target' => [
				'required' => false,
				'type' => 'string',
				'enum' => [
					'_blank',
					'_self',
					'_parent',
					'_top',
				],
			],
			'sort' => [
				'required' => false,
				'type' => 'string',
				'enum' => [
					'asc',
					'desc',
					'rand',
				],
			],
			'elements' => [
				'required' => false,
				'type' => 'integer|string',
			],
			'render' => [
				'required' => false,
				'type' => 'string',
			],
			'date-format' => [
				'required' => false,
				'type' => 'string',
			],
			'time-format' => [
				'required' => false,
				'type' => 'string',
			],
		];

		$validator = new ArgumentsArrayValidator();

		if(!$validator->validate($mandatory_keys, $args)){
			return null;
		}

		$shortCode = null;
		$postId = $args['post_id'] ?? $args['postId'] ?? null;
		$termId = $args['term_id'] ?? $args['termId'] ?? null;
		$userId = $args['user_id'] ?? $args['userId'] ?? null;
		$optionPage = $args['option_page'] ?? $args['optionPage'] ?? null;
		$boxName = $args['box_name'] ?? $args['boxName'];
		$fieldName = $args['field_name'] ?? $args['fieldName'];
		$dateFormat = $args['date_format'] ?? $args['dateFormat'] ?? null;
		$timeFormat = $args['time_format'] ?? $args['timeFormat'] ?? null;

		if($postId !== null){
			$shortCode = '[acpt pid="'.$postId .'"';
		} elseif($termId !== null){
			$shortCode = '[acpt_tax tid="'.$termId .'"';
		} elseif($userId !== null){
			$shortCode = '[acpt_user uid="'.$userId.'"';
		} elseif($optionPage !== null){
			$shortCode = '[acpt_option page="'.$optionPage.'"';
		}

		if($shortCode === null){
			return null;
		}

		$shortCode .= ' box="'.$boxName.'" field="'.$fieldName.'"';

		if($dateFormat !== null){
			$shortCode .= ' date-format="'.$dateFormat.'"';
		}

		if($timeFormat !== null){
			$shortCode .= ' time-format="'.$timeFormat.'"';
		}

		if(isset($args['width'])){
			$shortCode .= ' width="'.$args['width'].'"';
		}

		if(isset($args['height'])){
			$shortCode .= ' height="'.$args['height'].'"';
		}

		if(isset($args['target'])){
			$shortCode .= ' target="'.$args['target'].'"';
		}

		if(isset($args['elements'])){
			$shortCode .= ' elements="'.$args['elements'].'"';
		}

		if(isset($args['render'])){
			$shortCode .= ' render="'.$args['render'].'"';
		}

        if(isset($args['sort'])){
            $shortCode .= ' sort="'.$args['sort'].'"';
        }

		$shortCode .= ']';

		return do_shortcode($shortCode);
	}
}

if( !function_exists('is_acpt_field_visible') )
{
	function is_acpt_field_visible($args = [])
	{
		$mandatory_keys = [
			'post_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'term_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'user_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'comment_id' => [
				'required' => false,
				'type' => 'integer',
			],
			'option_page' => [
				'required' => false,
				'type' => 'string',
			],
			'box_name' => [
				'required' => true,
				'type' => 'string',
			],
			'field_name' => [
				'required' => true,
				'type' => 'string',
			],
			'parent_field_name' => [
				'required' => false,
				'type' => 'string',
			],
			'index' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'block_name' => [
				'required' => false,
				'type' => 'string',
			],
			'block_index' => [
				'required' => false,
				'type' => 'string|integer',
			],
			'with_context' => [
				'required' => false,
				'type' => 'boolean',
			],
		];

		$validator = new ArgumentsArrayValidator();

		if(!$validator->validate($mandatory_keys, $args)){
			return false;
		}

		$meta_field_model = null;

		try {

			$parentFieldName = $args['parent_field_name'] ?? $args['parentFieldName'] ?? null;
			$blockName = $args['block_name'] ?? $args['blockName'] ?? null;
			$blockIndex = $args['block_index'] ?? $args['blockIndex'] ?? null;
			$boxName = $args['box_name'] ?? $args['boxName'];
			$fieldName = $args['field_name'] ?? $args['fieldName'];

            $index = $args['index'] ?? null;
            $block_name = $args['block_name'] ?? $args['blockName'] ?? null;
            $block_index = $args['block_index'] ?? $args['blockIndex'] ?? null;
            $postId = $args['post_id'] ?? $args['postId'] ?? null;
            $termId = $args['term_id'] ?? $args['termId'] ?? null;
            $userId = $args['user_id'] ?? $args['userId'] ?? null;
            $optionPage = $args['option_page'] ?? $args['optionPage'] ?? null;

            $id = null;
            $belongsTo = null;

            if($postId !== null){
                $id = $postId;
                $belongsTo = MetaTypes::CUSTOM_POST_TYPE;
            } elseif($termId !== null){
                $id = $termId;
                $belongsTo = MetaTypes::TAXONOMY;
            } elseif($userId !== null){
                $id = $userId;
                $belongsTo = MetaTypes::USER;
            } elseif($optionPage !== null){
                $id = $optionPage;
                $belongsTo = MetaTypes::OPTION_PAGE;
            }

            if($id === null){
                return true;
            }

			// fields nested in a Flexible
			if($parentFieldName !== null and $blockName !== null and $blockIndex !== null){
				$parent_meta_field_model = MetaRepository::getMetaFieldByName([
					'boxName' => $boxName,
					'fieldName' => $parentFieldName,
				]);

                if($parent_meta_field_model === null){

                    $key = $boxName."_".$parentFieldName."_id";
                    $forged_by_key = $boxName."_".$parentFieldName."_forged_by";
                    $field_id = Meta::fetch($id, $belongsTo, $key);

                    if(empty($field_id)){
                        return false;
                    }

                    $parent_meta_field_model = MetaRepository::getMetaFieldById($field_id);

                    if($forged_by_key !== null){
                        $forged_by_id = Meta::fetch($id, $belongsTo, $forged_by_key);

                        if($forged_by_id !== null){
                            $forged_by_field = MetaRepository::getMetaFieldById($forged_by_id);

                            if($forged_by_field !== null){
                                $parent_meta_field_model->forgeBy($forged_by_field);
                            }
                        }
                    }
                }

				if($parent_meta_field_model !== null){
					foreach ($parent_meta_field_model->getBlocks() as $block_model){
						foreach ($block_model->getFields() as $nested_field_model){
							if($nested_field_model->getName() ===  $fieldName){
								$meta_field_model = $nested_field_model;
							}
						}
					}
				}

			} elseif($parentFieldName !== null and isset($args['index'])){ // fields nested in a Repeater
				$parent_meta_field_model = MetaRepository::getMetaFieldByName([
					'boxName' => $boxName,
					'fieldName' => $parentFieldName,
				]);

                if($parent_meta_field_model === null){

                    $key = $boxName."_".$parentFieldName."_id";
                    $forged_by_key = $boxName."_".$parentFieldName."_forged_by";
                    $field_id = Meta::fetch($id, $belongsTo, $key);

                    if(empty($field_id)){
                        return false;
                    }

                    $parent_meta_field_model = MetaRepository::getMetaFieldById($field_id);

                    if($forged_by_key !== null){
                        $forged_by_id = Meta::fetch($id, $belongsTo, $forged_by_key);

                        if($forged_by_id !== null){
                            $forged_by_field = MetaRepository::getMetaFieldById($forged_by_id);

                            if($forged_by_field !== null){
                                $parent_meta_field_model->forgeBy($forged_by_field);
                            }
                        }
                    }
                }

				if($parent_meta_field_model !== null){
					foreach ($parent_meta_field_model->getChildren() as $child_field_model){
						if($child_field_model->getName() ===  $fieldName){
							$meta_field_model = $child_field_model;
						}
					}
				}

			} else {
				$meta_field_model = MetaRepository::getMetaFieldByName([
					'boxName' => $boxName,
					'fieldName' => $fieldName,
				]);

                if($meta_field_model === null){

                    $key = $boxName."_".$fieldName."_id";
                    $forged_by_key = $boxName."_".$fieldName."_forged_by";
                    $field_id = Meta::fetch($id, $belongsTo, $key);

                    if(empty($field_id)){
                        return false;
                    }

                    $meta_field_model = MetaRepository::getMetaFieldById($field_id);

                    if($forged_by_key !== null){
                        $forged_by_id = Meta::fetch($id, $belongsTo, $forged_by_key);

                        if($forged_by_id !== null){
                            $forged_by_field = MetaRepository::getMetaFieldById($forged_by_id);

                            if($forged_by_field !== null){
                                $meta_field_model->forgeBy($forged_by_field);
                            }
                        }
                    }
                }
			}

			if($meta_field_model === null){

                $key = $boxName."_".$fieldName."_id";
                $forged_by_key = $boxName."_".$fieldName."_forged_by";
                $field_id = Meta::fetch($id, $belongsTo, $key);

                if(empty($field_id)){
                    return false;
                }

                $meta_field_model = MetaRepository::getMetaFieldById($field_id);

                if($meta_field_model === null){
                    return false;
                }

                if($forged_by_key !== null){
                    $forged_by_id = Meta::fetch($id, $belongsTo, $forged_by_key);

                    if($forged_by_id !== null){
                        $forged_by_field = MetaRepository::getMetaFieldById($forged_by_id);

                        if($forged_by_field !== null){
                            $meta_field_model->forgeBy($forged_by_field);
                        }
                    }
                }
			}

			$meta_field_model->setBelongsToLabel($belongsTo);
			$meta_field_model->setFindLabel($id);

			return FieldVisibilityChecker::check(
				Visibility::IS_FRONTEND,
				$id,
				$belongsTo,
				$meta_field_model,
				[],
				$index,
				$block_name,
				$block_index
			);

		} catch (\Exception $exception){
			return true;
		}
	}
}
