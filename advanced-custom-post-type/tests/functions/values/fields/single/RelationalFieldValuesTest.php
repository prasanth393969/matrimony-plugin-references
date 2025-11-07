<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Relationships;
use ACPT\Core\Models\Meta\MetaFieldModel;

class RelationalFieldValuesTest extends AbstractTestCase
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
							'name' => 'relational',
							'label' => 'relational',
							'type' => MetaFieldModel::POST_TYPE,
							'showInArchive' => false,
							'isRequired' => false,
							'defaultValue' => null,
							'description' => "lorem ipsum dolor facium",
							'relations' => [
								[
									'from' => [
										'type' => MetaTypes::CUSTOM_POST_TYPE,
										'value' => 'page',
									],
									'to' => [
										'type' => MetaTypes::CUSTOM_POST_TYPE,
										'value' => 'post',
									],
									'relationship' => Relationships::ONE_TO_ONE_UNI,
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

        $add_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'relational',
            'value' => $this->oldest_post_id,
        ]);

        $this->assertTrue($add_acpt_meta_field_value);

        $acpt_field = get_acpt_field([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'relational',
        ]);

        $this->assertInstanceOf(\WP_Post::class, $acpt_field[0]);
        $this->assertEquals($acpt_field[0]->ID, $this->oldest_post_id);
	}

	/**
	 * @depends can_add_acpt_meta_field_value
	 * @test
	 */
	public function can_edit_acpt_meta_field_value()
	{
        $edit_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'relational',
            'value' => $this->getPostId('post', 1),
        ]);

        $this->assertTrue($edit_acpt_meta_field_value);

        $acpt_field = get_acpt_field([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'relational',
        ]);

        $this->assertInstanceOf(\WP_Post::class, $acpt_field[0]);
        $this->assertEquals($acpt_field[0]->ID, $this->getPostId('post', 1));
	}

	/**
	 * @depends can_edit_acpt_meta_field_value
	 * @test
	 */
	public function can_delete_acpt_meta_field_value()
	{
        $delete_acpt_meta_field_value = delete_acpt_meta_field_value([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'relational',
        ]);

        $this->assertTrue($delete_acpt_meta_field_value);

		$delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

		$this->assertTrue($delete_acpt_meta_box);

        $acpt_field = get_acpt_field([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'relational',
        ]);

        $this->assertNull($acpt_field);

		$delete_group = delete_acpt_meta_group('new-group');
		$delete_acpt_option_page = delete_acpt_option_page('new-page', true);

		$this->assertTrue($delete_group);
		$this->assertTrue($delete_acpt_option_page);
	}
}