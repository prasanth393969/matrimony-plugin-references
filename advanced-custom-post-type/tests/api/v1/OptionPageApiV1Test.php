<?php

namespace ACPT\Tests;

class OptionPageApiV1Test extends RestApiV1TestCase
{
	/**
	 * @test
	 */
	public function can_fetch_page_definitions()
	{
		$response = $this->callAuthenticatedRestApi('GET', '/option-page');

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);

		$this->assertArrayHasKey("currentPage", $response);
		$this->assertArrayHasKey("prev", $response);
		$this->assertArrayHasKey("next", $response);
		$this->assertArrayHasKey("total", $response);
		$this->assertArrayHasKey("records", $response);
	}

	/**
	 * @test
	 */
	public function return_error_with_invalid_payload()
	{
		$response = $this->callAuthenticatedRestApi('POST', '/option-page', [
			'foo' => 'bar'
		]);

		$this->assertEquals(500, $response['status']);
	}

	/**
	 * @test
	 */
	public function can_create_option_page_definition()
	{
        $response = $this->callAuthenticatedRestApi('DELETE', '/option-page/page-from-api');

		$payload = [
			'pageTitle' => "Page from API",
			'menuTitle' => "Page from API",
			'capability' => "manage_options",
			'menuSlug' => "page-from-api",
			'position' => 77,
			'icon' => "menu",
			'description' => null,
			'parentId' => null,
		];

		$response = $this->callAuthenticatedRestApi('POST', '/option-page', $payload);

		$this->assertEquals(201, $response['status']);
	}

	/**
	 * @test
	 */
	public function can_fetch_option_page_definition()
	{
		$response = $this->callAuthenticatedRestApi('GET', '/option-page/page-from-api');

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);

		$this->assertEquals("page-from-api", $response['menuSlug']);
		$this->assertEquals("Page from API", $response['menuTitle']);
		$this->assertEquals("Page from API", $response['pageTitle']);
	}

	/**
	 * @test
	 */
	public function can_edit_option_page_definition()
	{
		$payload = [
			'pageTitle' => "Page from API",
			'menuTitle' => "Page from API",
			'capability' => "manage_options",
			'menuSlug' => "page-from-api",
			'position' => 44,
			'icon' => "dashboard",
			'description' => 'Lorem ipsum dolor facium',
			'parentId' => null,
		];

		$response = $this->callAuthenticatedRestApi('PUT', '/option-page/page-from-api', $payload);

		$this->assertEquals(200, $response['status']);
	}

	/**
	 * @test
	 */
	public function can_fetch_modified_option_page_definition()
	{
		$response = $this->callAuthenticatedRestApi('GET', '/option-page/page-from-api');

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);

		$this->assertEquals("page-from-api", $response['menuSlug']);
		$this->assertEquals("Page from API", $response['menuTitle']);
		$this->assertEquals("Page from API", $response['pageTitle']);
		$this->assertEquals("Lorem ipsum dolor facium", $response['description']);
	}

	/**
	 * @test
	 */
	public function can_delete_option_page_definition()
	{
		$response = $this->callAuthenticatedRestApi('DELETE', '/option-page/page-from-api');

		$this->assertEquals(200, $response['status']);

		$response = $this->callAuthenticatedRestApi('GET', '/option-page/page-from-api');

		$this->assertEquals(404, $response['status']);
	}
}