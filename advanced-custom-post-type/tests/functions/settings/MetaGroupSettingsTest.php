<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaGroupDisplay;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Includes\ACPT_DB;

class MetaGroupSettingsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function dont_register_a_new_group_with_missing_params()
	{
		ACPT_DB::flushCache();

		$new_group = save_acpt_meta_group([
			'not_valid' => 'new-group',
		]);

		$this->assertFalse($new_group);
	}


	/**
	 * @test
	 */
	public function can_register_edit_and_delete_a_simple_group()
	{
		$new_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group',
		]);

		$this->assertTrue($new_group);

		$group_object = get_acpt_meta_group_object('new-group');
		$group_object_id = $group_object->id;

		$this->assertNotNull($group_object_id);
		$this->assertEquals("advanced", $group_object->context);
		$this->assertEquals("default", $group_object->priority);

		$edit_group = save_acpt_meta_group([
			'name' => 'new-group',
			'new_name' => 'new-group-modified',
			'label' => 'New group modified',
			'context' => 'side',
			'priority' => 'low',
		]);

		$this->assertTrue($edit_group);

		$group_object_edit = get_acpt_meta_group_object('new-group-modified');

		$this->assertNotNull($group_object_edit);
		$this->assertEquals("side", $group_object_edit->context);
		$this->assertEquals("low", $group_object_edit->priority);

		$group_object_edit_id = $group_object_edit->id;

		$this->assertNotNull($group_object_edit_id);
		$this->assertEquals($group_object_edit_id, $group_object_id);
		$this->assertEquals($group_object_edit->name, 'new-group-modified');

		$delete_group = delete_acpt_meta_group('new-group-modified');

		$this->assertTrue($delete_group);
	}

	/**
	 * @test
	 */
	public function can_register_and_delete_a_more_complex_group()
	{
		$new_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group',
			'display' => MetaGroupDisplay::HORIZONTAL_TABS,
			'belongs' => [
				[
					'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
					'operator'  => "=",
					"find"      => "page"
				]
			],
		]);

		$this->assertTrue($new_group);

		$edit_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group modified',
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
				]
			],
		]);

		$this->assertTrue($edit_group);

		$delete_group = delete_acpt_meta_group('new-group');

		$this->assertTrue($delete_group);
	}

	/**
	 * @test
	 */
	public function can_register_and_delete_a_more_complex_group_with_boxes()
	{
		$new_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group',
			'belongs' => [
				[
					'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
					'operator'  => "=",
					"find"      => "page"
				]
			],
		]);

		$this->assertTrue($new_group);

		$edit_group = save_acpt_meta_group([
			'name' => 'new-group',
			'label' => 'New group modified',
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
				]
			],
			'boxes' => [
				[
					'name' => 'box_name',
					'label' => null,
					'fields' => [
						[
							'name' => 'advanced_field',
							'label' => 'advanced field',
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
						]
					]
				],
			],
		]);

		$this->assertTrue($edit_group);

		$delete_group = delete_acpt_meta_group('new-group');

		$this->assertTrue($delete_group);
	}
}