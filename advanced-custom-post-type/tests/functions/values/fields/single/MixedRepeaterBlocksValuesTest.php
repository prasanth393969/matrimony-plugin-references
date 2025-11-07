<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class MixedRepeaterBlocksValuesTest extends AbstractTestCase
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
							'name' => 'repeater',
							'label' => 'repeater',
							'type' => MetaFieldModel::REPEATER_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => null,
							'description' => "lorem ipsum dolor facium",
							'children' => [
								[
									'name' => 'flex',
									'type' => MetaFieldModel::FLEXIBLE_CONTENT_TYPE,
									'blocks' => [
										[
											'name' => 'block',
											'label' => 'block',
											'fields' => [
												[
													'name' => 'text',
													'type' => MetaFieldModel::TEXT_TYPE,
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
				'field_name' => 'repeater',
				'value' => [
					[
						"flex" => [
							'blocks' => [
								0 => [
									'block' => [
										[
											"Text" => "text",
										],
									]
								]
							]
						],
					],
					[
						"flex" => [
							'blocks' => [
								0 => [
									'block' => [
										[
											"Text" => "text 222",
										],
										[
											"Text" => "text 333",
										],
									]
								]
							]
						],
					],
				],
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$has_rows = acpt_field_has_rows([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater',
			]);

			$this->assertTrue($has_rows);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater',
			]);

			$this->assertCount(2, $acpt_field);
//			$this->assertEquals($acpt_field[0]['flex'][0]['blocks'][0]['block']['text'][0], "text");
//			$this->assertEquals($acpt_field[1]['flex'][0]['blocks'][0]['block']['text'][0], "text 222");
//			$this->assertEquals($acpt_field[1]['flex'][0]['blocks'][0]['block']['text'][1], "text 333");
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
				'field_name' => 'repeater',
			]);

			$this->assertTrue($delete_acpt_meta_field_value);
		}

		$delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

		$this->assertTrue($delete_acpt_meta_box);

		foreach ($this->dataProvider() as $key => $value){
			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater',
			]);

			$this->assertNull($acpt_field);
		}

		$delete_group = delete_acpt_meta_group('new-group');
		$delete_acpt_option_page = delete_acpt_option_page('new-page', true);

		$this->assertTrue($delete_group);
		$this->assertTrue($delete_acpt_option_page);
	}
}