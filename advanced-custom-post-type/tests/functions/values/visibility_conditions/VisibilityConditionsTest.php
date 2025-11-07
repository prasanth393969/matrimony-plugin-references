<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class VisibilityConditionsTest extends AbstractTestCase
{
	/**
	 * @test
	 * @throws \Exception
	 */
	public function a_complete_test_for_cpts()
	{
		$groupName = 'page';

		$save_meta_group = save_acpt_meta_group([
			'name' => $groupName,
			'belongs' => [
				[
					'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
					'operator'  => "=",
					"find"      => "page",
				],
			],
		]);

		$this->assertTrue($save_meta_group);

		$save_meta_box = save_acpt_meta_box([
			'groupName' => $groupName,
			'name' => 'new_box',
			'label' => 'new box',
			'fields' => []
		]);

		$this->assertTrue($save_meta_box);

		$add_meta_field = save_acpt_meta_field(
			[
				'groupName' => $groupName,
				'boxName' => 'new_box',
				'name' => 'field_with_visibility_conditions',
				'type' => MetaFieldModel::TEXT_TYPE,
				'isRequired' => false,
				'showInArchive' => false,
				'visibilityConditions' => [
					[
						'type' => [
							'type' => 'VALUE',
							'value' => 'VALUE',
						],
						'value' => 'ciao',
						"operator" => "="
					],
				]
			]
		);

		$this->assertTrue($add_meta_field);

		$add_acpt_meta_field_value = save_acpt_meta_field_value([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
			'value' => "text text",
		]);

		$this->assertTrue($add_acpt_meta_field_value);

		$is_acpt_field_visible = is_acpt_field_visible([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertFalse($is_acpt_field_visible);

		$add_acpt_meta_field_value2 = save_acpt_meta_field_value([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
			'value' => "ciao",
		]);

		$this->assertTrue($add_acpt_meta_field_value2);

		$is_acpt_field_visible = is_acpt_field_visible([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertTrue($is_acpt_field_visible);

		$delete_acpt_meta_field_value = delete_acpt_meta_field_value([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertTrue($delete_acpt_meta_field_value);

		$delete_acpt_meta_box = delete_acpt_meta_box('page', 'new_box');

		$this->assertTrue($delete_acpt_meta_box);

		$delete_acpt_meta_group = delete_acpt_meta_group('page');

		$this->assertTrue($delete_acpt_meta_group);
	}

	/**
	 * @test
	 */
	public function a_complete_test_for_taxonomies()
	{
		$groupName = 'category';

		$save_meta_group = save_acpt_meta_group([
			'name' => $groupName,
			'belongs' => [
				[
					'belongsTo' => MetaTypes::TAXONOMY,
					'operator'  => "=",
					"find"      => "category",
				],
			],
		]);

		$this->assertTrue($save_meta_group);

		$save_meta_box = save_acpt_meta_box([
			'groupName' => $groupName,
			'name' => 'new_box',
			'label' => 'new box',
			'fields' => []
		]);

		$this->assertTrue($save_meta_box);

		$add_meta_field = save_acpt_meta_field(
			[
				'groupName' => $groupName,
				'boxName' => 'new_box',
				'name' => 'field_with_visibility_conditions',
				'type' => MetaFieldModel::TEXT_TYPE,
				'isRequired' => false,
				'showInArchive' => false,
				'visibilityConditions' => [
					[
						'type' => [
							'type' => 'VALUE',
							'value' => 'VALUE',
						],
						'value' => 'ciao',
						"operator" => "="
					],
				]
			]
		);

		$this->assertTrue($add_meta_field);

		$add_acpt_tax_meta_field_value = save_acpt_meta_field_value([
			'term_id' => $this->oldest_category_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
			'value' => "text text",
		]);

		$this->assertTrue($add_acpt_tax_meta_field_value);

		$is_acpt_tax_field_visible = is_acpt_field_visible([
			'term_id' => $this->oldest_category_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertFalse($is_acpt_tax_field_visible);

		$add_acpt_tax_meta_field_value2 = save_acpt_meta_field_value([
			'term_id' => $this->oldest_category_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
			'value' => "ciao",
		]);

		$this->assertTrue($add_acpt_tax_meta_field_value2);

		$is_acpt_tax_field_visible = is_acpt_field_visible([
			'term_id' => $this->oldest_category_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertTrue($is_acpt_tax_field_visible);

		$delete_acpt_tax_meta_field_value = delete_acpt_meta_field_value([
			'term_id' => $this->oldest_category_id,
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertTrue($delete_acpt_tax_meta_field_value);

		$delete_acpt_meta_box = delete_acpt_meta_box('category', 'new_box');

		$this->assertTrue($delete_acpt_meta_box);

		$delete_acpt_meta_group = delete_acpt_meta_group('category');

		$this->assertTrue($delete_acpt_meta_group);
	}

	/**
	 * @test
	 */
	public function a_complete_test_for_option_pages()
	{
		$new_page = register_acpt_option_page([
			'menu_slug' => 'new-page',
			'page_title' => 'New page',
			'menu_title' => 'New page menu title',
			'icon' => 'admin-appearance',
			'capability' => 'manage_options',
			'description' => 'lorem ipsum',
			'position' => 77,
		]);

		$this->assertTrue($new_page);

		$groupName = 'new-page';

		$save_meta_group = save_acpt_meta_group([
			'name' => $groupName,
			'belongs' => [
				[
					'belongsTo' => MetaTypes::OPTION_PAGE,
					'operator'  => "=",
					"find"      => "new-page",
				],
			],
		]);

		$this->assertTrue($save_meta_group);

		$save_meta_box = save_acpt_meta_box([
			'groupName' => $groupName,
			'name' => 'new_box',
			'label' => 'new box',
			'fields' => []
		]);

		$this->assertTrue($save_meta_box);


		$add_meta_field = save_acpt_meta_field(
			[
				'groupName' => $groupName,
				'boxName' => 'new_box',
				'name' => 'field_with_visibility_conditions',
				'type' => MetaFieldModel::TEXT_TYPE,
				'isRequired' => false,
				'showInArchive' => false,
				'visibilityConditions' => [
					[
						'type' => [
							'type' => 'VALUE',
							'value' => 'VALUE',
						],
						'value' => 'ciao',
						"operator" => "="
					],
				]
			]
		);

		$this->assertTrue($add_meta_field);

		$add_acpt_option_page_meta_field_value = save_acpt_meta_field_value([
			'option_page' => 'new-page',
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
			'value' => "text text",
		]);

		$this->assertTrue($add_acpt_option_page_meta_field_value);

		$is_acpt_option_page_field_visible = is_acpt_field_visible([
			'option_page' => 'new-page',
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertFalse($is_acpt_option_page_field_visible);

		$add_acpt_option_page_meta_field_value2 = save_acpt_meta_field_value([
			'option_page' => 'new-page',
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
			'value' => "ciao",
		]);

		$this->assertTrue($add_acpt_option_page_meta_field_value2);

		$is_acpt_option_page_field_visible = is_acpt_field_visible([
			'option_page' => 'new-page',
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertTrue($is_acpt_option_page_field_visible);

		$delete_acpt_option_page_meta_field_value = delete_acpt_meta_field_value([
			'option_page' => 'new-page',
			'box_name' => 'new_box',
			'field_name' => 'field_with_visibility_conditions',
		]);

		$this->assertTrue($delete_acpt_option_page_meta_field_value);

		$delete_acpt_meta_box = delete_acpt_meta_box('new-page', 'new_box');

		$this->assertTrue($delete_acpt_meta_box);

		$delete_acpt_meta_group = delete_acpt_meta_group('new-page');

		$this->assertTrue($delete_acpt_meta_group);

		$this->assertTrue(delete_acpt_option_page('new-page', true));
	}
}