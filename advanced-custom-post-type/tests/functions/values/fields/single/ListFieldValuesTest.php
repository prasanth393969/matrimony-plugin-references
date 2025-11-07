<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class ListFieldValuesTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_add_acpt_meta_field_row_value()
    {
	    foreach ($this->dataProvider() as $key => $value){
		    $deleteFieldValue = delete_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $deleteMetaBox = delete_acpt_meta_box('new-group', 'box_name');

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertNull($acpt_field);
	    }

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
						    'name' => 'list',
						    'label' => 'list',
						    'type' => MetaFieldModel::LIST_TYPE,
						    'showInArchive' => false,
						    'isRequired' => false,
						    'defaultValue' => null,
						    'description' => "lorem ipsum dolor facium",
						    'advancedOptions' => [
							    [
								    'key' => 'before',
								    'value' => '<p>',
							    ],
							    [
								    'key' => 'after',
								    'value' => '</p>',
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
			    'field_name' => 'list',
			    'value' => [
				    "text text",
				    "bla bla",
			    ],
		    ]);

		    $this->assertTrue($add_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertEquals($acpt_field[0], '<p>text text</p>');
		    $this->assertEquals($acpt_field[1], '<p>bla bla</p>');

		    $has_rows = acpt_field_has_rows([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertTrue($has_rows);
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
			    'field_name' => 'list',
			    'value' => [
				    "other value",
				    "bla bla",
			    ],
		    ]);

		    $this->assertTrue($edit_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertEquals($acpt_field[0], '<p>other value</p>');
		    $this->assertEquals($acpt_field[1], '<p>bla bla</p>');
	    }
    }

    /**
     * @depends can_edit_acpt_meta_field_row_value
     * @test
     */
    public function can_display_acpt_meta_field()
    {
	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertStringContainsString('other value', $acpt_field);
		    $this->assertStringContainsString('bla bla', $acpt_field);
	    }
    }

    /**
     * @depends can_display_acpt_meta_field
     * @test
     */
    public function can_delete_acpt_meta_field_row_value()
    {
	    foreach ($this->dataProvider() as $key => $value){
		    $delete_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
			    'value' => [
				    "bla bla",
			    ],
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertEquals("<p>bla bla</p>", $acpt_field[0]);

		    // delete another row
		    $delete_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
			    'value' => []
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertEmpty($acpt_field);
	    }

	    $get_acpt_user_field_object = get_acpt_meta_field_object('box_name', 'list');

	    $this->assertNotEmpty($get_acpt_user_field_object);

        $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

        $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'list',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}
