<?php

namespace ACPT\Tests;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Includes\ACPT_DB;

class MetaFieldSettingsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function dont_register_a_new_box_if_group_does_not_exists()
	{
		ACPT_DB::flushCache();

		$new_box = save_acpt_meta_box([
			'groupName' => 'dsadsadsadsadsa',
			'name' => 'box_name',
			'label' => null,
			'fields' => []
		]);

		$this->assertFalse($new_box);
	}

	/**
	 * @test
	 */
	public function can_register_edit_and_delete_a_simple_box()
	{
		$new_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group',
		]);

		$this->assertTrue($new_group);

		$new_box = save_acpt_meta_box([
			'groupName' => 'new-group',
			'name' => 'box_name',
			'label' => null,
			'fields' => []
		]);

		$this->assertTrue($new_box);

		$group_object = get_acpt_meta_group_object('new-group');
		$box_object = get_acpt_box_object('box_name');

		$this->assertCount(1, $group_object->boxes);
		$this->assertEquals( 'box_name', $box_object->name);

		$edit_box = save_acpt_meta_box([
			'groupName' => 'new-group',
			'name' => 'box_name',
			'new_name' => 'new_box_name',
			'label' => null,
			'fields' => []
		]);

		$this->assertTrue($edit_box);

		$box_object = get_acpt_box_object( 'new_box_name');

		$this->assertNotNull($box_object);
		$this->assertEquals( 'new_box_name', $box_object->name);

		$delete_box = delete_acpt_meta_box('new-group', 'new_box_name');
		$group_object = get_acpt_meta_group_object('new-group');

		$this->assertTrue($delete_box);
		$this->assertCount(0, $group_object->boxes);

		$delete_group = delete_acpt_meta_group('new-group');

		$this->assertTrue($delete_group);
	}

	/**
	 * @test
	 */
	public function can_register_edit_and_delete_a_simple_field()
	{
		$new_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group',
		]);

		$this->assertTrue($new_group);

		$new_box = save_acpt_meta_box([
			'groupName' => 'new-group',
			'name' => 'box_name',
			'label' => null,
			'fields' => []
		]);

		$this->assertTrue($new_box);

		$new_field = save_acpt_meta_field([
			'groupName' => 'new-group',
			'boxName' => 'box_name',
			'name' => 'advanced_field',
			'type' => MetaFieldModel::TEXT_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'defaultValue' => "foo",
			'description' => "lorem ipsum dolor facium",
			'advancedOptions' => [
				[
					'value' => '</p>',
					'key' => 'after',
				],
				[
					'value' => '<p>',
					'key' => 'before',
				],
				[
					'value' => '平仮名',
					'key' => 'label',
				],
			]
		]);

		$this->assertTrue($new_field);

		$field_object = get_acpt_meta_field_object('box_name', 'advanced_field');

		$this->assertNotNull($field_object);
		$this->assertEquals( 'advanced_field', $field_object->name);

		$delete_field = delete_acpt_meta_field('new-group', 'box_name', 'advanced_field');

		$this->assertTrue($delete_field);

		$delete_group = delete_acpt_meta_group('new-group');

		$this->assertTrue($delete_group);
	}

	/**
	 * @test
	 */
	public function can_register_edit_and_delete_a_nested_repeater_field()
	{
		$new_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group',
		]);

		$this->assertTrue($new_group);

		$new_box = save_acpt_meta_box([
			'groupName' => 'new-group',
			'name' => 'box_name',
			'label' => null,
			'fields' => []
		]);

		$this->assertTrue($new_box);

		$new_field = save_acpt_meta_field([
			'groupName' => 'new-group',
			'boxName' => 'box_name',
			'name' => 'field',
			'type' => MetaFieldModel::REPEATER_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'defaultValue' => "foo",
			'description' => "lorem ipsum dolor facium",
		]);

		$this->assertTrue($new_field);

		$new_child_field = save_acpt_meta_field([
			'groupName' => 'new-group',
			'boxName' => 'box_name',
			'parentName' => 'field',
			'name' => 'child_field',
			'type' => MetaFieldModel::TEXT_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'defaultValue' => null,
			'description' => null,
		]);

		$this->assertTrue($new_child_field);

		$field_object = get_acpt_meta_field_object('box_name', 'field');

		$this->assertCount(1, $field_object->children);

		$modify_name = save_acpt_meta_field([
			'groupName' => 'new-group',
			'boxName' => 'box_name',
			'name' => 'field',
			'new_name' => 'field_repeater',
			'type' => MetaFieldModel::REPEATER_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'defaultValue' => "foo",
			'description' => "lorem ipsum dolor facium",
		]);

		$this->assertTrue($modify_name);

		$field_object = get_acpt_meta_field_object( 'box_name', 'field_repeater');
		$this->assertNotNull($field_object);

		$new_child_field = save_acpt_meta_field([
			'groupName' => 'new-group',
			'boxName' => 'box_name',
			'parentName' => 'field_repeater',
			'name' => 'child_field',
			'new_name' => 'child_new_field',
			'type' => MetaFieldModel::TEXT_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'defaultValue' => "foo",
			'description' => "lorem ipsum dolor facium",
		]);

		$this->assertTrue($new_child_field);

		$field_object = get_acpt_meta_field_object( 'box_name', 'field_repeater');

		$this->assertNotNull($field_object);
		$this->assertEquals($field_object->children[0]->name, 'child_new_field');

		$delete_field = delete_acpt_meta_field('new-group', 'box_name', 'child_new_field');

		$this->assertTrue($delete_field);

		$field_object = get_acpt_meta_field_object('box_name', 'child_new_field');

		$this->assertNull($field_object);

		$delete_group = delete_acpt_meta_group('new-group');

		$this->assertTrue($delete_group);
	}
}