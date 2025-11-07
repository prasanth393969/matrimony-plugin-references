<?php

namespace ACPT\Tests;

use ACPT\Constants\FormAction;
use ACPT\Core\Models\Form\FormFieldModel;
use ACPT\Includes\ACPT_DB;

class FormApiV1Test extends RestApiV1TestCase
{
	/**
	 * @test
	 */
	public function raise_error_with_wrong_payload()
	{
		$response = $this->callAuthenticatedRestApi('POST', '/form',  [
			[
				"title" => "box",
			]
		]);

		$this->assertEquals(500, $response['status']);
	}

	/**
	 * @test
	 */
	public function can_add_a_very_simple_form()
	{
		ACPT_DB::flushCache();

		$response = $this->callAuthenticatedRestApi('POST', '/form',  [
			"name" => "form",
			"label" => "Form",
			"action" => FormAction::PHP,
			"key" => "12345678",
		]);

		$this->assertEquals(201, $response['status']);

		$response = json_decode($response['response'], true);

		$this->assertNotEmpty($response['id']);

		return $response['id'];
	}

	/**
	 * @depends can_add_a_very_simple_form
	 * @test
	 *
	 * @param $id
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function can_update_a_very_simple_form($id)
	{
		$response = $this->callAuthenticatedRestApi('PUT', '/form/'.$id,  [
			"name" => "modified-form",
			"label" => "Modified form",
			"action" => FormAction::PHP,
			"key" => "12345678",
			"meta" => [
				[
					'key' => "key",
					'value' => "value"
				],
				[
					'key' => "key2",
					'value' => "value2"
				]
			],
			"fields" => [
				[
					"group" => "Standard fields",
					"name" => "field",
					"key" => "75676534543",
					"label" => "Field",
					"type" => FormFieldModel::EMAIL_TYPE,
					'isRequired' => false
				],
				[
					"group" => "Standard fields",
					"name" => "textarea",
					"key" => "978665243432",
					"label" => "Textarea",
					"type" => FormFieldModel::TEXTAREA_TYPE,
					'isRequired' => false
				]
			]
		]);

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);

		$this->assertNotEmpty($response['id']);

		return $response['id'];
	}

	/**
	 * @depends can_update_a_very_simple_form
	 * @test
	 *
	 * @param $id
	 *
	 * @throws \Exception
	 */
	public function can_fetch_and_then_delete_form($id)
	{
		$response = $this->callAuthenticatedRestApi('GET', '/form/'.$id,  []);

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);
		$id = $response['id'];

		$this->assertNotEmpty($id);

		$response = $this->callAuthenticatedRestApi('GET', '/form/'.$id,  []);

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);

		$this->assertEquals($id, $response['id']);
		$this->assertEquals("modified-form", $response['name']);
		$this->assertEquals("Modified form", $response['label']);
		$this->assertCount(2, $response['meta']);
		$this->assertCount(2, $response['fields']);

		$response = $this->callAuthenticatedRestApi('DELETE', '/form/'.$id,  []);

		$this->assertEquals(200, $response['status']);
	}
}