<?php

namespace ACPT\Tests;

class SchemaApiV1Test extends RestApiV1TestCase
{
    /**
     * @test
     */
    public function can_fetch_the_schema_definition()
    {
        $response = $this->callRestApi('GET', '/schema');

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertEquals("2.0", $response['swagger']);
        $this->assertEquals("/wp-json/acpt/v1", $response['basePath']);
        $this->assertArrayHasKey("tags", $response);
        $this->assertArrayHasKey("schemes", $response);
        $this->assertArrayHasKey("paths", $response);
        $this->assertArrayHasKey("definitions", $response);
        $this->assertArrayHasKey("securityDefinitions", $response);
        $this->assertArrayHasKey("security", $response);
        $this->assertArrayHasKey("externalDocs", $response);
    }
}
