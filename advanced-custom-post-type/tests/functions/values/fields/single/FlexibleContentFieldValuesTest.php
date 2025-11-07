<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Includes\ACPT_DB;

class FlexibleContentFieldValuesTest extends AbstractTestCase
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
											'name' => 'Text',
											'type' => MetaFieldModel::TEXT_TYPE,
											'isRequired' => true,
										],
										[
											'name' => 'Url',
											'type' => MetaFieldModel::URL_TYPE,
											'isRequired' => false,
										],
										[
											'name' => 'Indirizzo',
											'type' => MetaFieldModel::ADDRESS_TYPE,
											'isRequired' => false,
										],
										[
											'name' => 'Moneta',
											'type' => MetaFieldModel::CURRENCY_TYPE,
											'isRequired' => false,
										],
										[
											'name' => 'Lunghezza',
											'type' => MetaFieldModel::LENGTH_TYPE,
											'isRequired' => false,
										],
										[
											'name' => 'Peso',
											'type' => MetaFieldModel::WEIGHT_TYPE,
											'isRequired' => false,
										],
										[
											'name' => 'Select',
											'type' => MetaFieldModel::SELECT_TYPE,
											'isRequired' => false,
											'options' => [
												[
													'value' => 'foo',
													'label' => 'Label foo',
													'isDefault' => false,
												],
												[
													'value' => 'bar',
													'label' => 'Label bar',
													'isDefault' => false,
												],
												[
													'value' => 'fuzz',
													'label' => 'Label fuzz',
													'isDefault' => false,
												],
											]
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
									"Text" => "text",
									"Url" => [
										'url' => 'https://acpt.io',
										'label' => 'url label',
									],
									"Select" => "foo",
									"Indirizzo" => "Via Latina 94 00179 Roma",
									"Moneta" => [
										'amount' => 32,
										'unit' => 'EUR'
									],
									"Lunghezza" => [
										'length' => 32,
										'unit' => 'KILOMETER'
									],
									"Peso" => [
										'weight' => 32,
										'unit' => 'GRAM'
									],
								]
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

			$this->assertEquals($acpt_field['blocks'][0]['block']['Text'][0], 'text');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Url'][0]['url'], 'https://acpt.io');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Url'][0]['label'], 'url label');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Select'][0], 'foo');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Indirizzo'][0]['address'], 'Via Latina 94 00179 Roma');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Moneta'][0]['amount'], 32);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Moneta'][0]['unit'], 'EUR');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Peso'][0]['weight'], 32);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Peso'][0]['unit'], 'GRAM');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Lunghezza'][0]['length'], 32);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Lunghezza'][0]['unit'], 'KILOMETER');

			$get_acpt_block = get_acpt_block([
				$key => $value,
				'box_name' => 'box_name',
				'parent_field_name' => 'flexible',
				'block_name' => 'block',
			]);

			$this->assertCount(1, $get_acpt_block);
			$this->assertEquals($get_acpt_block[0]['block']['Peso'][0]['weight'], 32);

			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
				'value' => [
					"blocks" => [
						0 => [
							'block' => [
								[
									"Text" => "text",
									"Url" => [
										'url' => 'https://acpt.io',
										'label' => 'url label',
									],
									"Select" => "foo",
									"Indirizzo" => "Via Latina 94 00179 Roma",
									"Moneta" => [
										'amount' => 32,
										'unit' => 'EUR'
									],
									"Lunghezza" => [
										'length' => 32,
										'unit' => 'KILOMETER'
									],
									"Peso" => [
										'weight' => 32,
										'unit' => 'GRAM'
									],
								],
								[
									"Text" => "Second text",
									"Url" => [
										'url' => 'https://appsumo.com',
										'label' => 'AppSumo',
									],
									"Select" => "bar",
									"Indirizzo" => "Via Macedonia 11 00179 Roma",
									"Moneta" => [
										'amount' => 132,
										'unit' => 'EUR'
									],
									"Lunghezza" => [
										'length' => 132,
										'unit' => 'KILOMETER'
									],
									"Peso" => [
										'weight' => 132,
										'unit' => 'GRAM'
									],
								],
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

			$this->assertEquals($acpt_field['blocks'][0]['block']['Text'][1], 'Second text');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Url'][1]['url'], 'https://appsumo.com');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Url'][1]['label'], 'AppSumo');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Select'][1], 'bar');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Indirizzo'][1]['address'], 'Via Macedonia 11 00179 Roma');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Moneta'][1]['amount'], 132);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Moneta'][1]['unit'], 'EUR');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Peso'][1]['weight'], 132);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Peso'][1]['unit'], 'GRAM');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Lunghezza'][1]['length'], 132);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Lunghezza'][1]['unit'], 'KILOMETER');
		}
	}

	/**
	 * @depends can_add_acpt_meta_field_row_value
	 * @test
	 */
	public function can_edit_acpt_meta_field_row_value()
	{
		foreach ($this->dataProvider() as $key => $value){
			$edit_acpt_meta_block_field_row_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
				'value' => [
					"blocks" => [
						0 => [
							'block' => [
								[
									"Text" => "Another text",
									"Url" => [
										'url' => 'https://google.com',
										'label' => 'Google',
									],
									"Select" => "fuzz",
									"Indirizzo" => "Via Macedonia 11 00179 Roma",
									"Moneta" => [
										'amount' => 132,
										'unit' => 'EUR'
									],
									"Lunghezza" => [
										'length' => 132,
										'unit' => 'KILOMETER'
									],
									"Peso" => [
										'weight' => 132,
										'unit' => 'GRAM'
									],
								],
								[
									"Text" => "text",
									"Url" => [
										'url' => 'https://acpt.io',
										'label' => 'url label',
									],
									"Select" => "foo",
									"Indirizzo" => "Via Latina 94 00179 Roma",
									"Moneta" => [
										'amount' => 32,
										'unit' => 'EUR'
									],
									"Lunghezza" => [
										'length' => 32,
										'unit' => 'KILOMETER'
									],
									"Peso" => [
										'weight' => 32,
										'unit' => 'GRAM'
									],
								]
							],
						],
					],
				]
			]);

			$this->assertTrue($edit_acpt_meta_block_field_row_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'flexible',
			]);

			$this->assertEquals($acpt_field['blocks'][0]['block']['Text'][0], 'Another text');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Url'][0]['url'], 'https://google.com');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Url'][0]['label'], 'Google');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Select'][0], 'fuzz');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Indirizzo'][0]['address'], 'Via Macedonia 11 00179 Roma');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Moneta'][0]['amount'], 132);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Moneta'][0]['unit'], 'EUR');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Peso'][0]['weight'], 132);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Peso'][0]['unit'], 'GRAM');
			$this->assertEquals($acpt_field['blocks'][0]['block']['Lunghezza'][0]['length'], 132);
			$this->assertEquals($acpt_field['blocks'][0]['block']['Lunghezza'][0]['unit'], 'KILOMETER');
		}
	}

	/**
	 * @depends can_edit_acpt_meta_field_row_value
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