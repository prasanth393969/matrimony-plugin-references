<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class GetFieldsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_fetch_acpt_meta_field_values()
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
				],
                [
                    'belongsTo' => MetaTypes::TAXONOMY,
                    'operator'  => "=",
                    "find"      => "category",
                    "logic"     => "OR"
                ],
                [
                    'belongsTo' => MetaTypes::OPTION_PAGE,
                    'operator'  => "=",
                    "find"      => "new-page",
                    "logic"     => "OR"
                ],
                [
                    'belongsTo' => MetaTypes::USER,
                    'operator'  => "="
                ]
			],
			'boxes' => [
				[
					'name' => 'box_name',
					'label' => null,
					'fields' => [
						[
							'name' => 'textarea',
							'label' => 'textarea',
							'type' => MetaFieldModel::TEXTAREA_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => "foo",
							'description' => "lorem ipsum dolor facium",
							'permissions' => [
								[
									'user_role' => 'editor',
									'permissions' => [
										'read' => true,
										'edit' => true,
									]
								]
							]
						],
						[
							'name' => 'select',
							'label' => 'select',
							'type' => MetaFieldModel::SELECT_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => "foo",
							'description' => "lorem ipsum dolor facium",
							'permissions' => [
								[
									'user_role' => 'editor',
									'permissions' => [
										'read' => true,
										'edit' => false,
									]
								]
							],
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
						[
							'name' => 'checkbox',
							'label' => 'checkbox',
							'type' => MetaFieldModel::CHECKBOX_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => null,
							'description' => "lorem ipsum dolor facium",
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

			save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'textarea',
				'value' => "text text",
			]);

			save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'select',
				'value' => "foo",
			]);

			save_acpt_meta_field_value([
				$key => $value,
				'box_name' => 'box_name',
				'field_name' => 'checkbox',
				'value' => ["bar"],
			]);

			// check if acpt_meta_field_values can fetch all the fields
			$get_acpt_fields = get_acpt_fields([
				$key => $value,
				'box_name' => 'box_name'
			]);

			$message = $key . "==>" . $value ;
			$this->assertCount(3, $get_acpt_fields, $message);
			$this->assertEquals("text text", $get_acpt_fields[0]);
			$this->assertEquals("foo", $get_acpt_fields[1]);
			$this->assertEquals(["bar"], $get_acpt_fields[2]);

            // check if acpt_meta_field_values can fetch all the fields
            $get_acpt_fields = get_acpt_fields([
                $key => $value,
                'box_name' => 'box_name',
                'assoc' => true
            ]);

            $this->assertCount(3, $get_acpt_fields);
            $this->assertEquals("text text", $get_acpt_fields['box_name_textarea']);
            $this->assertEquals("foo", $get_acpt_fields['box_name_select']);
            $this->assertEquals(["bar"], $get_acpt_fields['box_name_checkbox']);
		}

		$delete_group = delete_acpt_meta_group('new-group');
		$delete_acpt_option_page = delete_acpt_option_page('new-page', true);

		$this->assertTrue($delete_group);
		$this->assertTrue($delete_acpt_option_page);
	}
}