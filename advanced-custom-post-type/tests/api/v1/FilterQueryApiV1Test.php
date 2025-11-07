<?php

namespace ACPT\Tests;

class FilterQueryApiV1Test extends RestApiV1TestCase
{
    /**
     * @test
     */
    public function no_filter()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/post/filter/query', []);

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
    public function basic_author_filter()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/post/filter/query', [
            'params' => [
                'author' => 34432432432432
            ]
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNull($response['prev']);
        $this->assertNull($response['next']);
        $this->assertEquals($response['total'], 0);
        $this->assertEmpty($response['records']);

        $response = $this->callAuthenticatedRestApi('POST', '/post/filter/query', [
            'params' => [
                'author' => 1
            ]
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertGreaterThan(0, $response['total']);
        $this->assertNotEmpty($response['records']);
    }

    /**
     * @test
     */
    public function basic_cat_filter()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/post/filter/query', [
            'params' => [
                'cat' => 34432432432432
            ]
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNull($response['prev']);
        $this->assertNull($response['next']);
        $this->assertEquals($response['total'], 0);
        $this->assertEmpty($response['records']);

        $response = $this->callAuthenticatedRestApi('POST', '/post/filter/query', [
            'params' => [
                'cat' => 1
            ]
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertGreaterThan(0, $response['total']);
        $this->assertNotEmpty($response['records']);
    }
}