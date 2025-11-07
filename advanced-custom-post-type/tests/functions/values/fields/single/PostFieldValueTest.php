<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class PostFieldValueTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_add_acpt_meta_field_value()
	{
		$new_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group',
			'belongs' => [
				[
					'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
					'operator'  => "=",
					"find"      => "page",
					"logic"     => "OR"
				]
			],
			'boxes' => [
				[
					'name' => 'box_name',
					'label' => null,
					'fields' => [
						[
							'name' => 'post',
							'label' => 'post',
							'type' => MetaFieldModel::POST_OBJECT_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => "foo",
							'description' => "lorem ipsum dolor facium",
						]
					]
				],
			],
		]);

		$new_page = register_acpt_option_page([
			'menu_slug' => 'new-page',
			'page_title' => 'New page',
			'menu_title' => 'New page menu title',
			'icon' => 'admin-appearance',
			'capability' => 'manage_options',
			'description' => 'lorem ipsum',
			'position' => 77,
		]);

		$this->assertTrue($new_group);
		$this->assertTrue($new_page);

		foreach ($this->dataProvider() as $key => $value){
			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'post',
				'value' => $this->oldest_post_id,
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'post',
			]);

			$this->assertInstanceOf(\WP_Post::class, $acpt_field);
			$this->assertEquals($acpt_field->ID, $this->oldest_post_id);
		}
	}

	/**
	 * @depends can_add_acpt_meta_field_value
	 * @test
	 */
	public function can_edit_acpt_meta_field_value()
	{
		foreach ($this->dataProvider() as $key => $value){
			$edit_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'post',
				'value' => $this->second_oldest_page_id,
			]);

			$this->assertTrue($edit_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'post',
			]);

			$this->assertInstanceOf(\WP_Post::class, $acpt_field);
			$this->assertEquals($acpt_field->ID, $this->second_oldest_page_id);
		}
	}

	/**
	 * @depends can_edit_acpt_meta_field_value
	 * @test
	 */
	public function can_delete_acpt_meta_field_value()
	{
		foreach ($this->dataProvider() as $key => $value){
			$delete_acpt_meta_field_value = delete_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'post',
			]);

			$this->assertTrue($delete_acpt_meta_field_value);
		}

		$delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

		$this->assertTrue($delete_acpt_meta_box);

		foreach ($this->dataProvider() as $key => $value){
			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'post',
			]);

			$this->assertNull($acpt_field);
		}

		$delete_group = delete_acpt_meta_group('new-group');
		$delete_acpt_option_page = delete_acpt_option_page('new-page', true);

		$this->assertTrue($delete_group);
		$this->assertTrue($delete_acpt_option_page);
	}
}