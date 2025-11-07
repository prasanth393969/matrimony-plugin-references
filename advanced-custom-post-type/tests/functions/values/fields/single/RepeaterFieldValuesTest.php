<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class RepeaterFieldValuesTest extends AbstractTestCase
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
		    ]);

		    $this->assertTrue($add_acpt_meta_field_value);

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'repeater',
			    'value' => [
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

		    $this->assertEquals($acpt_field[0]['Text'], 'text');
		    $this->assertEquals($acpt_field[0]['Url']['url'], 'https://acpt.io');
		    $this->assertEquals($acpt_field[0]['Url']['label'], 'url label');
		    $this->assertEquals($acpt_field[0]['Select'], 'foo');
		    $this->assertEquals($acpt_field[0]['Select'], 'foo');
		    $this->assertEquals($acpt_field[0]['Indirizzo']['address'], 'Via Latina 94 00179 Roma');
		    $this->assertEquals($acpt_field[0]['Moneta']['amount'], 32);
		    $this->assertEquals($acpt_field[0]['Moneta']['unit'], 'EUR');
		    $this->assertEquals($acpt_field[0]['Peso']['weight'], 32);
		    $this->assertEquals($acpt_field[0]['Peso']['unit'], 'GRAM');
		    $this->assertEquals($acpt_field[0]['Lunghezza']['length'], 32);
		    $this->assertEquals($acpt_field[0]['Lunghezza']['unit'], 'KILOMETER');
		    $this->assertEquals($acpt_field[1]['Text'], 'Second text');
		    $this->assertEquals($acpt_field[1]['Url']['url'], 'https://appsumo.com');
		    $this->assertEquals($acpt_field[1]['Url']['label'], 'AppSumo');
		    $this->assertEquals($acpt_field[1]['Select'], 'bar');
		    $this->assertEquals($acpt_field[1]['Indirizzo']['address'], 'Via Macedonia 11 00179 Roma');
		    $this->assertEquals($acpt_field[1]['Moneta']['amount'], 132);
		    $this->assertEquals($acpt_field[1]['Moneta']['unit'], 'EUR');
		    $this->assertEquals($acpt_field[1]['Peso']['weight'], 132);
		    $this->assertEquals($acpt_field[1]['Peso']['unit'], 'GRAM');
		    $this->assertEquals($acpt_field[1]['Lunghezza']['length'], 132);
		    $this->assertEquals($acpt_field[1]['Lunghezza']['unit'], 'KILOMETER');

		    $get_acpt_child_field = get_acpt_child_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'Peso',
			    'parent_field_name' => 'repeater',
			    'index' => 0,
		    ]);

		    $this->assertEquals($get_acpt_child_field['weight'], 32);
		    $this->assertEquals($get_acpt_child_field['unit'], 'GRAM');
	    }
    }

    /**
     * @depends can_add_acpt_meta_field_row_value
     * @test
     */
    public function can_edit_acpt_meta_field_row_value()
    {
	    foreach ($this->dataProvider() as $key => $value){
		    $edit_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'repeater',
			    'value' => [
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
				    ]
			    ],
		    ]);

		    $this->assertTrue($edit_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    'post_id' => $this->oldest_page_id,
			    'box_name' => 'box_name',
			    'field_name' => 'repeater',
		    ]);

		    $this->assertEquals($acpt_field[0]['Text'], 'Another text');
		    $this->assertEquals($acpt_field[0]['Url']['url'], 'https://google.com');
		    $this->assertEquals($acpt_field[0]['Url']['label'], 'Google');
		    $this->assertEquals($acpt_field[0]['Select'], 'fuzz');
		    $this->assertEquals($acpt_field[0]['Indirizzo']['address'], 'Via Macedonia 11 00179 Roma');
		    $this->assertEquals($acpt_field[0]['Moneta']['amount'], 132);
		    $this->assertEquals($acpt_field[0]['Moneta']['unit'], 'EUR');
		    $this->assertEquals($acpt_field[0]['Peso']['weight'], 132);
		    $this->assertEquals($acpt_field[0]['Peso']['unit'], 'GRAM');
		    $this->assertEquals($acpt_field[0]['Lunghezza']['length'], 132);
		    $this->assertEquals($acpt_field[0]['Lunghezza']['unit'], 'KILOMETER');

		    // get_acpt_child_field
	    }
    }

    /**
     * @depends can_edit_acpt_meta_field_row_value
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