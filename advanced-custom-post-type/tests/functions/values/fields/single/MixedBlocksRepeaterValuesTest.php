<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class MixedBlocksRepeaterValuesTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_add_acpt_meta_field_row_value()
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
							'name' => 'Text',
							'type' => MetaFieldModel::TEXT_TYPE,
							'isRequired' => true,
						],
						[
							'name' => 'flexible',
							'label' => 'flexible',
							'type' => MetaFieldModel::FLEXIBLE_CONTENT_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => null,
							'description' => "lorem ipsum dolor facium",
							'blocks' => [
								[
									'name' => 'block',
									'label' => 'block',
									'fields' => [
										[
											'name' => 'repeater',
											'type' => MetaFieldModel::REPEATER_TYPE,
											'children' => [
												[
													'name' => 'Text',
													'type' => MetaFieldModel::TEXT_TYPE,
													'isRequired' => true,
												],
											],
										],
									],
								],
							],
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
				'field_name' => 'flexible',
				'value' => [
					"blocks" => [
						0 => [
							'block' => [
								[
									"repeater" => [
										[
											"Text" => "text",
										],
										[
											"Text" => "text 222",
										],
									],
								],
								[
									"repeater" => [
										[
											"Text" => "777 text",
										],
										[
											"Text" => "888 text",
										],
									],
								],
							],
						],
						1 => [
							'block' => [
								[
									"repeater" => [
										[
											"Text" => "text 333",
										],
										[
											"Text" => "text 444",
										],
										[
											"Text" => "text 555",
										],
									],
								],
							],
						],
					],
				],
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$has_rows = acpt_field_has_blocks([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
			]);

			$this->assertTrue($has_rows);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
			]);

			$this->assertCount(2, $acpt_field['blocks']);
			$this->assertEquals($acpt_field['blocks'][0]['block']['repeater'][0][0]['Text'], "text");
			$this->assertEquals($acpt_field['blocks'][0]['block']['repeater'][0][1]['Text'], "text 222");
			$this->assertEquals($acpt_field['blocks'][0]['block']['repeater'][1][0]['Text'], "777 text");
			$this->assertEquals($acpt_field['blocks'][0]['block']['repeater'][1][1]['Text'], "888 text");
			$this->assertEquals($acpt_field['blocks'][1]['block']['repeater'][0][0]['Text'], "text 333");
			$this->assertEquals($acpt_field['blocks'][1]['block']['repeater'][0][1]['Text'], "text 444");
			$this->assertEquals($acpt_field['blocks'][1]['block']['repeater'][0][2]['Text'], "text 555");

			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
				'value' => [
					"blocks" => [
						0 => [
							'block' => [
								[
									"repeater" => [
										[
											"Text" => "text modified",
										]
									],
								]
							],
						],
					],
				],
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
			]);

			$this->assertCount(1, $acpt_field['blocks']);
			$this->assertEquals($acpt_field['blocks'][0]['block']['repeater'][0][0]['Text'], "text modified");
		}
	}

	/**
	 * @depends can_add_acpt_meta_field_row_value
	 * @test
	 */
	public function can_delete_acpt_meta_field_value()
	{
		foreach ($this->dataProvider() as $key => $value){
			$delete_acpt_meta_field_value = delete_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
			]);

			$this->assertTrue($delete_acpt_meta_field_value);
		}

		$delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

		$this->assertTrue($delete_acpt_meta_box);

		foreach ($this->dataProvider() as $key => $value){
			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
			]);

			$this->assertNull($acpt_field);
		}

		$delete_group = delete_acpt_meta_group('new-group');
		$delete_acpt_option_page = delete_acpt_option_page('new-page', true);

		$this->assertTrue($delete_group);
		$this->assertTrue($delete_acpt_option_page);
	}
}