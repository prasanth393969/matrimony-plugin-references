<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Validators\ImportFileValidator;

class ImportFileValidatorTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function empty_file()
	{
		$data = [];

		$this->assertFalse(ImportFileValidator::validate($data));
	}

	/**
	 * @test
	 */
	public function invalid()
	{
		$data = [
			'cavolo' => [],
			MetaTypes::TAXONOMY => [],
			MetaTypes::USER => [],
		];

		$this->assertFalse(ImportFileValidator::validate($data));
	}

	/**
	 * @test
	 */
	public function minimum_valid()
	{
		$data = [
			MetaTypes::CUSTOM_POST_TYPE => [],
			MetaTypes::TAXONOMY => [],
			MetaTypes::META => [],
		];

		$this->assertTrue(ImportFileValidator::validate($data));
	}

	/**
	 * @test
	 */
	public function invalid_keys()
	{
		$data = [
			MetaTypes::CUSTOM_POST_TYPE => [
				['invalid' => 123],
			],
			MetaTypes::TAXONOMY => [],
			MetaTypes::USER => [],
		];

		$this->assertFalse(ImportFileValidator::validate($data));
	}

	/**
	 * @test
	 */
	public function valid()
	{
		$data = [
			MetaTypes::CUSTOM_POST_TYPE => [
				0 => [
					'id' => '10f9e6c0-8372-4049-b7a3-b272f0f25268',
					'name' => 'post',
					'singular' => 'Post',
					'plural' => 'Posts',
					'icon' => 'admin-post',
					'postCount' => '1',
					'supports' => [
					],
					'labels' => [
					],
					'settings' => [
					],
					'taxonomies' => [
						0 => [
							'id' => '074b1929-87b4-4945-9b1b-5cb84d1fb158',
							'slug' => 'category',
							'singular' => 'Category',
							'plural' => 'Categories',
							'labels' => [
							],
							'settings' => [
								'hierarchical' => true,
							],
							'postCount' => NULL,
						],
						1 => [
							'id' => '841145af-837e-427f-b512-d2c64c02b485',
							'slug' => 'post_tag',
							'singular' => 'Tag',
							'plural' => 'Tags',
							'labels' => [
							],
							'settings' => [
								'hierarchical' => true,
							],
							'postCount' => NULL,
						],
					],
				],
				1 => [
					'id' => 'a12d54f4-2922-4ad8-a4e2-72f12e65db08',
					'name' => 'page',
					'singular' => 'Page',
					'plural' => 'Pages',
					'icon' => 'admin-page',
					'postCount' => '3',
					'supports' => [
					],
					'labels' => [
					],
					'settings' => [
					],
					'taxonomies' => [
					],
				],
				2 => [
					'id' => '71f3840f-cb96-4983-9c85-b5e93eee1ef9',
					'name' => 'movie',
					'singular' => 'Movie',
					'plural' => 'Movies',
					'icon' => 'admin-comments',
					'postCount' => '3',
					'supports' => [
						0 => 'title',
						1 => 'editor',
						2 => 'thumbnail',
						3 => 'excerpt',
					],
					'labels' => [
						'menu_name' => 'Movie',
						'all_items' => 'All Movies',
						'add_new' => 'Add Movie',
						'add_new_item' => 'Add Movie',
						'edit_item' => 'Edit Movie',
						'new_item' => 'New Movie',
						'view_item' => 'View Movie',
						'view_items' => 'View Movies',
						'search_item' => 'Search Movies',
						'not_found' => 'No Movie found',
						'not_found_in_trash' => 'No Movie found',
						'parent_item_colon' => 'Parent item',
						'featured_image' => 'Featured image',
						'set_featured_image' => 'Set featured image',
						'remove_featured_image' => 'Remove featured image',
						'use_featured_image' => 'Use featured image',
						'archives' => 'Archives',
						'insert_into_item' => 'Insert',
						'uploaded_to_this_item' => 'Upload',
						'filter_items_list' => 'Filter Movies list',
						'items_list_navigation' => 'Navigation list Movies',
						'items_list' => 'List Movies',
						'filter_by_date' => 'Filter by date',
						'item_published' => 'Movie published',
						'item_published_privately' => 'Movie published privately',
						'item_reverted_to_draft' => 'Movie reverted to draft',
						'item_scheduled' => 'Movie scheduled',
						'item_updated' => 'Movie updated',
					],
					'settings' => [
						'public' => true,
						'publicly_queryable' => NULL,
						'show_ui' => true,
						'show_in_menu' => true,
						'show_in_nav_menus' => true,
						'show_in_admin_bar' => true,
						'show_in_rest' => true,
						'rest_base' => NULL,
						'menu_position' => NULL,
						'capability_type' => 'post',
						'has_archive' => true,
						'rewrite' => NULL,
						'custom_rewrite' => NULL,
						'query_var' => NULL,
						'custom_query_var' => NULL,
						'show_in_graphql' => true,
						'graphql_single_name' => 'Movie',
						'graphql_plural_name' => 'Movies',
					],
					'taxonomies' => [
					],
				],
			],
			MetaTypes::TAXONOMY => [
				0 => [
					'id' => '074b1929-87b4-4945-9b1b-5cb84d1fb158',
					'slug' => 'category',
					'singular' => 'Category',
					'plural' => 'Categories',
					'postCount' => '1',
					'isNative' => true,
					'labels' => [
					],
					'settings' => [
						'hierarchical' => true,
					],
					'customPostTypes' => [
						0 => [
							'id' => '10f9e6c0-8372-4049-b7a3-b272f0f25268',
							'name' => 'post',
							'singular' => 'Post',
							'plural' => 'Posts',
							'icon' => 'admin-post',
							'supports' => [
							],
							'labels' => [
							],
							'settings' => [
							],
						],
					],
				],
				1 => [
					'id' => '76b78b72-11b0-4c86-bb58-42942bf4770c',
					'slug' => 'dsadsadsa',
					'singular' => 'dd',
					'plural' => 'dd',
					'postCount' => 0,
					'isNative' => false,
					'labels' => [
						'name' => 'dsadsadsa',
						'singular_name' => 'dd',
						'search_items' => 'Search dd',
						'popular_items' => 'Popular dd',
						'all_items' => 'All dd',
						'parent_item' => 'Parent dd',
						'parent_item_colon' => 'Parent item',
						'edit_item' => 'Edit',
						'view_item' => 'View',
						'update_item' => 'Update dd',
						'add_new_item' => 'Add new dd',
						'new_item_name' => 'New dd',
						'separate_items_with_commas' => 'Separate dd with commas',
						'add_or_remove_items' => 'Add or remove dd',
						'choose_from_most_used' => 'Choose from most used dd',
						'not_found' => 'No dd found',
						'no_terms' => 'No dd',
						'filter_by_item' => 'Filter by dd',
						'items_list_navigation' => 'Navigation list dd',
						'items_list' => 'List dd',
						'most_used' => 'Most used dd',
						'back_to_items' => 'Back to dd',
					],
					'settings' => [
						'public' => true,
						'publicly_queryable' => NULL,
						'hierarchical' => true,
						'show_ui' => true,
						'show_in_menu' => true,
						'show_in_nav_menus' => true,
						'show_in_rest' => true,
						'rest_base' => NULL,
						'rest_controller_class' => NULL,
						'show_tagcloud' => true,
						'show_in_quick_edit' => true,
						'show_admin_column' => true,
						'capabilities' => [
							0 => 'manage_terms',
							1 => 'edit_terms',
							2 => 'delete_terms',
							3 => 'assign_terms',
						],
						'rewrite' => NULL,
						'custom_rewrite' => NULL,
						'query_var' => NULL,
						'custom_query_var' => NULL,
						'default_term' => NULL,
						'sort' => NULL,
					],
					'customPostTypes' => [
					],
				],
				2 => [
					'id' => '841145af-837e-427f-b512-d2c64c02b485',
					'slug' => 'post_tag',
					'singular' => 'Tag',
					'plural' => 'Tags',
					'postCount' => 0,
					'isNative' => true,
					'labels' => [
					],
					'settings' => [
						'hierarchical' => true,
					],
					'customPostTypes' => [
						0 => [
							'id' => '10f9e6c0-8372-4049-b7a3-b272f0f25268',
							'name' => 'post',
							'singular' => 'Post',
							'plural' => 'Posts',
							'icon' => 'admin-post',
							'supports' => [
							],
							'labels' => [
							],
							'settings' => [
							],
						],
					],
				],
			],
		];

		$this->assertTrue(ImportFileValidator::validate($data));
	}

	/**
	 * @test
	 */
	public function valid20()
	{
		$data20 = [
			"customPostType" => [
			],
			"taxonomy" => [
			],
			"optionPage" => [
			],
			MetaTypes::META => [
				[
					"id" => "94baf0e9-9a89-4d0a-922c-10427c4e361e",
					"name" => "fdsfdsfds",
					"label" => "fdsfdsfds",
					"UIName" => "fdsfdsfds",
					"belongs" => [
						[
							"id" => "f79a12b2-b953-4289-8d4f-0d09ed51cad8",
		                    "belongsTo" => "taxonomy",
		                    "operator" => "=",
		                    "logic" => null,
		                    "find" => "category",
						]
					],
					"fieldsCount" => 2,
					"boxes" => [
						[
							"id" => "207834d2-b5cc-490b-bad5-022fc5002611",
							"name" => "meta_box_title",
							"label" => "",
							"UIName" => "meta_box_title",
							"sort" => 1,
							"fields" => [
								[
									"id" => "4d01f327-f4d1-4e7a-ac05-b20e31e5c6ab",
									"name" => "repe",
									"type" => "Repeater",
									"defaultValue" => "",
									"description" => "",
									"showInArchive" => false,
									"isRequired" => false,
									"sort" => 1,
									"options" => [
									],
									"relations" => [
									],
									"visibilityConditions" => [
									],
									"validationRules" => [
									],
									"advancedOptions" => [
									],
									"children" => [
										[
											"id" => "a5d3b98f-003a-403f-b26e-bc00ffe6ee7b",
											"name" => "fdsfdsfds",
											"type" => "Text",
											"defaultValue" => "",
											"description" => "",
											"showInArchive" => false,
											"isRequired" => false,
											"sort" => 1,
											"options" => [
											],
											"relations" => [
											],
											"visibilityConditions" => [
											],
											"validationRules" => [
											],
											"advancedOptions" => [
											],
											"children" => [
											],
											"blocks" => [
											]
										]
									],
									"blocks" => [
									]
								],
								[
									"id" => "5b426915-44c3-43aa-a309-0c093c7d726f",
									"name" => "flex",
									"type" => "FlexibleContent",
									"defaultValue" => "",
									"description" => "",
									"showInArchive" => false,
									"isRequired" => false,
									"sort" => 2,
									"options" => [
									],
									"relations" => [
									],
									"visibilityConditions" => [
									],
									"validationRules" => [
									],
									"advancedOptions" => [
									],
									"children" => [
									],
									"blocks" => [
										[
											"id" => "37f5eb59-8517-47b7-8f65-625222c04738",
											"boxId" => "207834d2-b5cc-490b-bad5-022fc5002611",
											"fieldId" => "5b426915-44c3-43aa-a309-0c093c7d726f",
											"name" => "new_block",
											"label" => "fdsfdsfds",
											"sort" => 1,
											"fields" => [
												[
													"id" => "a5d3b98f-003a-403f-b26e-bc00ffe6ee7b",
													"name" => "fdsfdsfds",
													"type" => "Text",
													"defaultValue" => "",
													"description" => "",
													"showInArchive" => false,
													"isRequired" => false,
													"sort" => 1,
													"options" => [
													],
													"relations" => [
													],
													"visibilityConditions" => [
													],
													"validationRules" => [
													],
													"advancedOptions" => [
													],
													"children" => [
													],
													"blocks" => [
													]
												]
											]
										]
									]
								]
							]
						]
					]
				]
			]
		];

		$this->assertTrue(ImportFileValidator::validate($data20));
	}
}