<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class TableFieldValuesTest extends AbstractTestCase
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
							'name' => 'table',
							'label' => 'table',
							'type' => MetaFieldModel::TABLE_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => '',
							'description' => "lorem ipsum dolor facium",
							'advancedOptions' => []
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

		$data = [
			'settings' =>
				[
					'layout' => 'horizontal',
					'css' => '',
					'alignment' => 'left',
					'border' =>	[
						'thickness' => '1',
						'style' => 'solid',
						'color' => '#cccccc',
					],
					'background' =>	[
						'color' => '#ffffff',
						'zebra' => '#ffffff',
					],
					'color' => '#777777',
					'header' => true,
					'footer' => false,
					'columns' => '2',
					'rows' => '1',
				],
			'data' => [
				[
					[
						'value' => 'name',
						'settings' => [],
					],
					[
						'value' => 'email',
						'settings' => [],
					],
				],
				[
					[
						'value' => 'Mauro',
						'settings' => [],
					],
					['value' => 'mauro@acpt.io',
					 'settings' => [],

					],
				],
			],
		];

		foreach ($this->dataProvider() as $key => $value){

			$delete_field = delete_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'table',
			]);

			$this->assertTrue($delete_field);

			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'table',
				'value' => $data,
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'table',
			]);

			$this->assertEquals($data, $acpt_field);
		}
	}

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
	public function can_edit_acpt_meta_field_value()
	{
		$data = [
			'settings' =>
				[
					'layout' => 'horizontal',
					'css' => '',
					'alignment' => 'left',
					'border' =>	[
						'thickness' => '1',
						'style' => 'solid',
						'color' => '#cccccc',
					],
					'background' =>	[
						'color' => '#ffffff',
						'zebra' => '#ffffff',
					],
					'color' => '#777777',
					'header' => true,
					'footer' => false,
					'columns' => '2',
					'rows' => '2',
				],
			'data' => [
				[
					[
						'value' => 'name',
						'settings' => [],
					],
					[
						'value' => 'email',
						'settings' => [],
					],
				],
				[
					[
						'value' => 'Mauro',
						'settings' => [],
					],
					['value' => 'mauro@acpt.io',
					 'settings' => [],

					],
				],
				[
					[
						'value' => 'Pepper',
						'settings' => [],
					],
					[
						'value' => 'pepper@acpt.io',
					    'settings' => [],
					],
				],
			],
		];

		foreach ($this->dataProvider() as $key => $value){
			$edit_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'table',
				'value' => $data,
			]);

			$this->assertTrue($edit_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'table',
			]);

			$this->assertEquals($data, $acpt_field);
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
			    'boxName' => 'box_name',
			    'field_name' => 'table',
		    ]);

		    $expected = '<table class="acpt-table "><thead><tr data-row-id="0"><th data-row-id="0" data-col-id="0" style="text-align: left;color: #777777;border: 1px solid #cccccc;background: #ffffff;">name</th><th data-row-id="0" data-col-id="1" style="text-align: left;color: #777777;border: 1px solid #cccccc;background: #ffffff;">email</th></tr></thead><tbody><tr data-row-id="1"><td data-row-id="1" data-col-id="0" style="text-align: left;color: #777777;border: 1px solid #cccccc;background: #ffffff;">Mauro</td><td data-row-id="1" data-col-id="1" style="text-align: left;color: #777777;border: 1px solid #cccccc;background: #ffffff;">mauro@acpt.io</td></tr><tr data-row-id="2"><td data-row-id="2" data-col-id="0" style="text-align: left;color: #777777;border: 1px solid #cccccc;background: #ffffff;">Pepper</td><td data-row-id="2" data-col-id="1" style="text-align: left;color: #777777;border: 1px solid #cccccc;background: #ffffff;">pepper@acpt.io</td></tr></tbody></table>';

		    $this->assertEquals($expected, $acpt_field);
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
			    'field_name' => 'table',
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);
	    }

        $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

        $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'table',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}
