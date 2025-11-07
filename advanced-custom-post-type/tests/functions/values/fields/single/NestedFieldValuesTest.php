<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class NestedFieldValuesTest extends AbstractTestCase
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
							'defaultValue' => "foo",
							'description' => "lorem ipsum dolor facium",
							'children' => [
								[
									'name' => 'nested_repeater',
									'type' => MetaFieldModel::REPEATER_TYPE,
									'isRequired' => false,
									'children' => [
										[
											'name' => 'text',
											'type' => MetaFieldModel::TEXT_TYPE,
											'isRequired' => false,
										],
										[
											'name' => 'email',
											'type' => MetaFieldModel::EMAIL_TYPE,
											'isRequired' => false,
										]
									],
								],
							]
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
						'nested_repeater' => [
							[
								[
									"text"  => "nested",
									"email" => "maurocassani1978@gmail.com",
								],
								[
									"text"  => "other",
									"email" => "mauretto1978@yahoo.it",
								],
								[
									"text"  => "pluto",
									"email" => "pluto@yahoo.it",
								]
							],
							[
								[
									"text"  => "other rep",
									"email" => "other_rep@gmail.com",
								]
							]
						],
					]
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

			$this->assertEquals($acpt_field[0]['nested_repeater'][0]['text'], 'nested');
			$this->assertEquals($acpt_field[0]['nested_repeater'][0]['email'], 'maurocassani1978@gmail.com');
			$this->assertEquals($acpt_field[0]['nested_repeater'][1]['text'], 'other');
			$this->assertEquals($acpt_field[0]['nested_repeater'][1]['email'], 'mauretto1978@yahoo.it');
			$this->assertEquals($acpt_field[0]['nested_repeater'][2]['text'], 'pluto');
			$this->assertEquals($acpt_field[0]['nested_repeater'][2]['email'], 'pluto@yahoo.it');
			$this->assertEquals($acpt_field[1]['nested_repeater'][0]['text'], 'other rep');
			$this->assertEquals($acpt_field[1]['nested_repeater'][0]['email'], 'other_rep@gmail.com');

			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater',
				'value' => [
					[
						'nested_repeater' => [
							[
								[
									"text"  => "nested",
									"email" => "maurocassani1978@gmail.com",
								],
								[
									"text"  => "other",
									"email" => "mauretto1978@yahoo.it",
								],
								[
									"text"  => "pluto",
									"email" => "pluto@yahoo.it",
								]
							],
							[
								[
									"text"  => "other rep",
									"email" => "other_rep@gmail.com",
								],
								[
									"text"  => "last rep",
									"email" => "last@gmail.com",
								]
							]
						],
					]
				],
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater',
			]);

			$this->assertEquals($acpt_field[0]['nested_repeater'][0]['text'], 'nested');
			$this->assertEquals($acpt_field[0]['nested_repeater'][0]['email'], 'maurocassani1978@gmail.com');
			$this->assertEquals($acpt_field[0]['nested_repeater'][1]['text'], 'other');
			$this->assertEquals($acpt_field[0]['nested_repeater'][1]['email'], 'mauretto1978@yahoo.it');
			$this->assertEquals($acpt_field[0]['nested_repeater'][2]['text'], 'pluto');
			$this->assertEquals($acpt_field[0]['nested_repeater'][2]['email'], 'pluto@yahoo.it');
			$this->assertEquals($acpt_field[1]['nested_repeater'][0]['text'], 'other rep');
			$this->assertEquals($acpt_field[1]['nested_repeater'][0]['email'], 'other_rep@gmail.com');
			$this->assertEquals($acpt_field[1]['nested_repeater'][1]['text'], 'last rep');
			$this->assertEquals($acpt_field[1]['nested_repeater'][1]['email'], 'last@gmail.com');

			$acpt_field_nested = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater.nested_repeater',
			]);

			$this->assertCount(2, $acpt_field_nested);

			$get_acpt_child_field = get_acpt_child_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'email',
				'parent_field_name' => 'repeater.nested_repeater',
				'index' => '0.0',
			]);

			$this->assertEquals($get_acpt_child_field, 'maurocassani1978@gmail.com');

			$get_acpt_child_field_null = get_acpt_child_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'email',
				'parent_field_name' => 'repeater.not_existing',
				'index' => '120.320',
			]);

			$this->assertNull($get_acpt_child_field_null);

			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater',
				'value' => [],
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$has_rows = acpt_field_has_rows([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'repeater',
			]);

			$this->assertFalse($has_rows);
		}
	}

	/**
	 * @test
	 */
	public function can_delete_acpt_meta_field_row_value()
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