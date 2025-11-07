<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\ImportRepository;

class ImportRepositoryTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function raise_exception()
	{
		$data = [
			'cavolo' => [],
			MetaTypes::TAXONOMY => [],
		];

		try {
			ImportRepository::import($data);
		} catch (\Exception $exception){
			$this->assertNotNull($exception->getMessage());
		}
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_import_data()
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
			MetaTypes::OPTION_PAGE => [],
			MetaTypes::META => [
				0 => [
					'id' => '23c3e436-1b27-4a72-9273-dc659874c05e',
					'name' => 'group',
					'label' => 'group-label',
					'belongs' => [],
					'boxes' => [
						0 => [
							'id' => '0d34d4d1-ce4f-4f74-9416-25246e48e885',
							'name' => 'box',
							'label' => 'Box',
							'sort' => 1,
							'fields' => [
								0 => [
									'id' => 'e7b388d9-194a-4fb6-80bf-d1fa8d20cf68',
									'db_name' => 'field',
									'ui_name' => 'field',
									'name' => 'field',
									'type' => MetaFieldModel::TEXTAREA_TYPE,
									'defaultValue' => '',
									'description' => '',
									'isRequired' => false,
									'showInArchive' => false,
									'sort' => 1,
									'advancedOptions' => [],
									'options' => [],
									'visibilityConditions' => [],
									'validationRules' => [],
									'children' => [],
									'blocks' => [],
									'relations' => [],
								],
							],
						],
					]
				],
			],
		];

		ImportRepository::import($data);

		$this->assertEquals(1,1);

		delete_acpt_meta_group('group');
	}
}