<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Id;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Meta\MetaFieldModel;

class IDFieldValuesTest extends AbstractTestCase
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
							'name' => 'id_field',
							'label' => 'id field',
							'type' => MetaFieldModel::ID_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => null,
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

		foreach ($this->dataProvider() as $key => $value){

		    $uuid = Uuid::v1();

			$add_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'id_field',
				'value' => $uuid,
			]);

			$this->assertTrue($add_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'id_field',
			]);

			$this->assertEquals($uuid, $acpt_field);
		}
	}

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
	public function can_edit_acpt_meta_field_value()
	{
		foreach ($this->dataProvider() as $key => $value){
            $uuid = Uuid::v1();

			$edit_acpt_meta_field_value = save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'id_field',
				'value' => $uuid,
			]);

			$this->assertTrue($edit_acpt_meta_field_value);

			$acpt_field = get_acpt_field([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'id_field',
			]);

			$this->assertEquals($uuid, $acpt_field);
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
			    'field_name' => 'id_field',
		    ]);

		    $this->assertTrue(Id::isValid($acpt_field, Id::UUID_V1));
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
			    'field_name' => 'id_field',
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);
	    }

        $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

        $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'id_field',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}