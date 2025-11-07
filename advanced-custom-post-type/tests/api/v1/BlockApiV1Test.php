<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;

class BlockApiV1Test extends RestApiV1TestCase
{
    /**
     * @test
     */
    public function raise_error_with_wrong_payload()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/block',  [
            [
                "title" => "box",
                "postType" => "post",
                "fields" => [
                    [
                        "name" => "string",
                        "type" => "foo",
                        "defaultValue" => "string",
                        "description" => "string",
                        "isRequired" => true,
                        "showInArchive" => true,
                        "options" => [],
                        "visibilityConditions" => [],
                        "relations" => [],
                        "hasChildren" => true,
                        "children" => [],
                        "blocks" => [],
                    ]
                ]
            ]
        ]);

        $this->assertEquals(500, $response['status']);
    }

    /**
     * @test
     */
    public function can_add_a_very_simple_block()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/block',  [
            "name" => "block name",
            "title" => "block-label",
            "category" => "design",
            "icon" => "admin-site-alt3",
            "css" => "body {color:red}",
            "callback" => "<div>test</div>",
            "keywords" => [
                "test",
                "acpt",
            ],
            "postTypes" => [
                "page",
                "post",
            ],
            "controls" => []
        ]);

        $this->assertEquals(201, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['id']);

        return $response['id'];
    }

    /**
     * @depends can_add_a_very_simple_block
     * @test
     *
     * @param $id
     *
     * @return string
     * @throws \Exception
     */
    public function can_update_a_very_simple_block($id)
    {
        $response = $this->callAuthenticatedRestApi('PUT', '/block/'.$id,  [
            "name" => "block name",
            "title" => "block-label",
            "category" => "design",
            "icon" => "admin-site-alt3",
            "css" => "body {color:blue}",
            "callback" => "<div>test test test test</div>",
            "keywords" => [
                "test",
                "acpt",
            ],
            "postTypes" => [
                "post",
            ],
            "controls" => []
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['id']);

        return $response['id'];
    }

    /**
     * @depends can_update_a_very_simple_block
     * @test
     *
     * @param $id
     *
     * @throws \Exception
     */
    public function can_fetch_and_then_delete_single_block($id)
    {
        $response = $this->callAuthenticatedRestApi('GET', '/block/'.$id,  []);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);
        $id = $response['id'];

        $this->assertNotEmpty($id);

        $response = $this->callAuthenticatedRestApi('GET', '/block/'.$id,  []);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertEquals($id, $response['id']);

        $response = $this->callAuthenticatedRestApi('DELETE', '/block/'.$id,  []);

        $this->assertEquals(200, $response['status']);
    }
}