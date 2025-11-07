<?php

namespace ACPT\Tests;

use ACPT\Constants\FormAction;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Includes\ACPT_DB;

class FormSettingsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_register_edit_and_delete_a_simple_form()
	{
		ACPT_DB::flushCache();

		$new_form = save_acpt_form([
			'name' => 'new-form',
			'label' => 'new form',
			'key' => '12345678',
			'action' => FormAction::CUSTOM,
			'fields' => [],
			'meta' => [],
		]);

		$this->assertTrue($new_form);

		$form_object = get_acpt_form_object('new-form');

		$this->assertNotNull($form_object);
		$this->assertEquals($form_object->name, 'new-form');
		$this->assertEquals($form_object->label, 'new form');
		$this->assertEquals($form_object->key, '12345678');
		$this->assertEquals($form_object->action, FormAction::CUSTOM);

		$edit_form = save_acpt_form([
			'name' => 'new-form',
			'label' => 'edit form',
			'key' => '87654321',
			'action' => FormAction::PHP,
			'fields' => [],
			'meta' => [],
		]);

		$this->assertTrue($edit_form);

		$form_object2 = get_acpt_form_object('new-form');

		$this->assertNotNull($form_object2);
		$this->assertEquals($form_object2->name, 'new-form');
		$this->assertEquals($form_object2->label, 'edit form');
		$this->assertEquals($form_object2->key, '87654321');
		$this->assertEquals($form_object2->action, FormAction::PHP);

		$this->assertEquals($form_object2->id, $form_object->id);

		$new_field = save_acpt_form_field([
			'form_name' => 'new-form',
			'key' => '1234567',
			'group' => 'Standard fields',
			'name' => 'email',
			'label' => 'Email',
			'type' => FormFieldModel::EMAIL_TYPE,
			'description' => 'description',
			'isRequired' => false,
			'extra' => [],
			'settings' => [],
		]);

		$this->assertTrue($new_field);

		$field_object = get_acpt_form_field_object([
			'formName' => 'new-form',
			'fieldName' => 'email',
		]);

		$this->assertNotNull($field_object);
		$this->assertEquals($field_object->name, 'email');
		$this->assertEquals($field_object->label, 'Email');

		$delete_field = delete_acpt_form_field([
			'formName' => 'new-form',
			'fieldName' => 'email',
		]);

		$this->assertTrue($delete_field);

		$field_object2 = get_acpt_form_field_object([
			'formName' => 'new-form',
			'fieldName' => 'email',
		]);

		$this->assertNull($field_object2);

		$delete_form = delete_acpt_form('new-form');

		$this->assertTrue($delete_form);
	}
}