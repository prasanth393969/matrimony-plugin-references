<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Includes\ACPT_DB;

class NestedBlocksValuesTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_add_acpt_meta_field_row_value()
	{
		ACPT_DB::flushCache();

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
											'name' => 'nested_flex',
											'type' => MetaFieldModel::FLEXIBLE_CONTENT_TYPE,
											'blocks' => [
												[
													'name' => 'nested_block',
													'label' => 'nested block',
													'fields' => [
														[
															'name' => 'Text',
															'type' => MetaFieldModel::TEXT_TYPE,
															'isRequired' => true,
														],
													]
												]
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
								"blocks" => [
									0 => [
										"nested_block" => [
											[
												"Text" => "text",
											],
											[
												"Text" => "text 22",
											]
										]
									],
									1 => [
										"nested_block" => [
											[
												"Text" => "text 3333",
											]
										]
									],
								]
							],
						],
						1 => [
							'block' => [
								"blocks" => [
									0 => [
										"nested_block" => [
											[
												"Text" => "text 55555",
											],
										]
									]
								]
							]
						]
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
			$this->assertEquals($acpt_field['blocks'][0]['block'][0]['blocks'][0]['nested_block'][0]['blocks'][0]['nested_block']['Text'][0], "text");
			$this->assertEquals($acpt_field['blocks'][0]['block'][0]['blocks'][0]['nested_block'][0]['blocks'][0]['nested_block']['Text'][1], "text 22");
			$this->assertEquals($acpt_field['blocks'][0]['block'][0]['blocks'][1]['nested_block'][0]['blocks'][0]['nested_block']['Text'][0], "text 3333");
			$this->assertEquals($acpt_field['blocks'][1]['block'][0]['blocks'][0]['nested_block'][0]['blocks'][0]['nested_block']['Text'][0], "text 55555");

			$get_acpt_block = get_acpt_block([
				$key => $value,
				'box_name' => 'box_name',
				'parent_field_name' => 'flexible',
				'block_name' => 'block.nested_block',
			]);

			$this->assertCount(3, $get_acpt_block);

			$get_acpt_block_child_field = get_acpt_block_child_field([
				$key => $value,
				'box_name' => 'box_name',
				'parent_field_name' => 'flexible',
				'field_name' => 'Text',
				'index' => '0',
				'block_name' => 'block.nested_block',
				'block_index' => '0.0',
			]);

			$this->assertEquals('text', $get_acpt_block_child_field);

			$get_acpt_block_child_field = get_acpt_block_child_field([
				$key => $value,
				'box_name' => 'box_name',
				'parent_field_name' => 'flexible',
				'field_name' => 'Text',
				'index' => '1',
				'block_name' => 'block.nested_block',
				'block_index' => '0.0',
			]);

			$this->assertEquals('text 22', $get_acpt_block_child_field);

			$get_acpt_block_child_field = get_acpt_block_child_field([
				$key => $value,
				'box_name' => 'box_name',
				'parent_field_name' => 'flexible',
				'field_name' => 'Text',
				'index' => '0',
				'block_name' => 'block.nested_block',
				'block_index' => '1.0',
			]);

			$this->assertEquals('text 55555', $get_acpt_block_child_field);
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