<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class UrlFieldValuesTest extends AbstractTestCase
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
							'name' => 'url',
							'label' => 'url',
							'type' => MetaFieldModel::URL_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => [
								'url' => 'https://acpt.io',
								'urlLabel' => 'ACPT'
							],
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

			$delete_field = delete_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'url',
			]);

			$this->assertTrue($delete_field);

			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'url',
				'value' => [
					'url' => 'https://acpt.io',
					'label' => 'url label',
				],
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'url',
			]);

			$settings = get_acpt_meta_field_object( 'box_name', 'url');

			$this->assertEquals('https://acpt.io', $settings->defaultValue->url);
			$this->assertEquals('ACPT', $settings->defaultValue->urlLabel);
			$this->assertEquals([
				'url' => 'https://acpt.io',
				'label' => 'url label',
				'after' => null,
				'before' => null,
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
				'field_name' => 'url',
				'value' => [
					'url' => 'https://google.com',
					'label' => 'Google',
				],
			]);

			$this->assertTrue($edit_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'url',
			]);

			$this->assertEquals([
				'url' => 'https://google.com',
				'label' => 'Google',
				'after' => null,
				'before' => null,
			], $acpt_field);
		}
	}

	/**
	 * @depends can_add_acpt_meta_field_value
	 * @test
	 */
	public function can_display_acpt_meta_field()
	{
		foreach ($this->dataProvider() as $key => $value){
			$acpt_field = acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'url',
			]);

			$this->assertEquals('<a href="https://google.com" target="_blank">Google</a>', $acpt_field);
		}
	}

	/**
	 * @depends can_add_acpt_meta_field_value
	 * @test
	 */
	public function can_delete_acpt_meta_field_value()
	{
		$delete_acpt_meta_field_value = delete_acpt_meta_field_value([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'box_name',
			'field_name' => 'url',
		]);

		$this->assertTrue($delete_acpt_meta_field_value);

		$delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

		$this->assertTrue($delete_acpt_meta_box);

		$acpt_field = get_acpt_field([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'box_name',
			'field_name' => 'url',
		]);

		$this->assertNull($acpt_field);
	}
}
