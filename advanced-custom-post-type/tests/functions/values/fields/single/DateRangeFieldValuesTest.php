<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class DateRangeFieldValuesTest extends AbstractTestCase
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
						    'name' => 'date-range',
						    'label' => 'date-range',
						    'type' => MetaFieldModel::DATE_RANGE_TYPE,
						    'showInArchive' => false,
						    'isRequired' => false,
						    'defaultValue' => [
						    	'from' => '2024-02-05',
						    	'to' => '2024-02-15'
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
			    'field_name' => 'date-range',
		    ]);

		    $this->assertTrue($delete_field);

		    // from > to, return false
            $add_acpt_meta_field_value = save_acpt_meta_field_value([
                    $key => $value,
                    'box_name' => 'box_name',
                    'field_name' => 'date-range',
                    'value' => [
                        "2022-07-30",
                        "2022-06-30",
                    ],
            ]);

            $this->assertFalse($add_acpt_meta_field_value);

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'date-range',
			    'value' => [
				    "2022-06-30",
				    "2022-07-30",
			    ],
		    ]);

		    $this->assertTrue($add_acpt_meta_field_value, $key ."=>". $value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'date-range',
		    ]);

		    $settings = get_acpt_meta_field_object( 'box_name', 'date-range');

		    $this->assertDateIsEqualsTo('2024-02-05', $settings->defaultValue->from);
		    $this->assertDateIsEqualsTo('2024-02-15', $settings->defaultValue->to);

		    $this->assertIsArray($acpt_field);
		    $this->assertCount(2, $acpt_field['value']);

		    $from = new \DateTime($acpt_field['value'][0]);
		    $to = new \DateTime($acpt_field['value'][1]);
		    $expectedFrom = new \DateTime("2022-06-30");
		    $expectedTo   = new \DateTime("2022-07-30");

		    $this->assertEquals($from, $expectedFrom);
		    $this->assertEquals($to, $expectedTo);
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
			    'field_name' => 'date-range',
			    'value' => [
				    "2022-06-30",
				    "2023-01-31",
			    ],
		    ]);

		    $this->assertTrue($edit_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'date-range',
		    ]);

            $from = new \DateTime($acpt_field['value'][0]);
            $to = new \DateTime($acpt_field['value'][1]);
            $expectedFrom = new \DateTime("2022-06-30");
            $expectedTo   = new \DateTime("2023-01-31");

            $this->assertEquals($from, $expectedFrom);
            $this->assertEquals($to, $expectedTo);
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
			    'field_name' => 'date-range',
			    'date_format' => 'd/m/Y',
		    ]);

		    $this->assertEquals('30/06/2022 - 31/01/2023', $acpt_field);

		    $acpt_field = acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'date-range',
			    'date_format' => 'Y-m-d'
		    ]);

		    $this->assertEquals('2022-06-30 - 2023-01-31', $acpt_field);
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
			    'field_name' => 'date-range',
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);
	    }

	    $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

	    $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'date-range',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}