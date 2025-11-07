<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class CountryFieldValuesTest extends AbstractTestCase
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
							'name' => 'country',
							'label' => 'country',
							'type' => MetaFieldModel::COUNTRY_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => null,
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
				'field_name' => 'country',
				'value' => [
					'value' => 'Italy (Italia)',
					'country' => 'it',
				],
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'country',
			]);

			$this->assertEquals([
				'value' => 'Italy (Italia)',
				'country' => 'it',
			], $acpt_field);
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
				'field_name' => 'country',
				'value' => [
					'value' => 'Spain (España)',
					'country' => 'es',
				],
			]);

			$this->assertTrue($edit_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'country',
			]);

			$this->assertEquals([
				'value' => 'Spain (España)',
				'country' => 'es',
			], $acpt_field);
		}
	}

	/**
	 * @depends can_edit_acpt_meta_field_value
	 * @test
	 */
	public function can_display_acpt_meta_field()
	{
		foreach ($this->dataProvider() as $key => $value){
			$acpt_field = acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'country',
			]);

			$this->assertEquals('Spain (España)', $acpt_field);
		}

	}

	/**
	 * @depends can_edit_acpt_meta_field_value
	 * @test
	 */
	public function can_delete_acpt_meta_field_value()
	{
		$delete_acpt_meta_field_value = delete_acpt_meta_field_value([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'box_name',
			'field_name' => 'country',
		]);

		$this->assertTrue($delete_acpt_meta_field_value);

		$delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

		$this->assertTrue($delete_acpt_meta_box);

		$acpt_field = get_acpt_field([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'box_name',
			'field_name' => 'country',
		]);

		$this->assertNull($acpt_field);
	}
}
