<?php

namespace ACPT\Tests;

class WooCommerceApiV1Test extends RestApiV1TestCase
{
    /**
     * @test
     */
    public function can_create_product_data()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/woocommerce/product-data', [
            "name" => "info",
            "icon" => [
                    "icon" => "ccv",
                    "value" => "\e604"
            ],
            "visibility" => [
                    "show_if_simple",
                    "show_if_variable",
                    "show_if_grouped"
            ],
            "showInUI" => true
        ]);

        $this->assertEquals(201, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['id']);

        return $response['id'];
    }

    /**
     * @test
     */
    public function can_fetch_all_product_data()
    {
        $response = $this->callAuthenticatedRestApi('GET', '/woocommerce/product-data', []);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertArrayHasKey("currentPage", $response);
        $this->assertArrayHasKey("prev", $response);
        $this->assertArrayHasKey("next", $response);
        $this->assertArrayHasKey("total", $response);
        $this->assertArrayHasKey("records", $response);
    }

    /**
     * @param string $productDataId
     *
     * @depends can_create_product_data
     * @test
     * @return string
     * @throws \Exception
     */
    public function can_fetch_product_data($productDataId)
    {
        $response = $this->callAuthenticatedRestApi('GET', '/woocommerce/product-data/'.$productDataId, []);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['id']);
        $this->assertEquals($productDataId, $response['id']);

        return $response['id'];
    }

    /**
     * @param string $productDataId
     *
     * @depends can_fetch_product_data
     * @test
     * @return string
     * @throws \Exception
     */
    public function can_modify_product_data($productDataId)
    {
        $response = $this->callAuthenticatedRestApi('PUT', '/woocommerce/product-data/' . $productDataId, [
                "name" => "info modified",
                "icon" => [
                        "icon" => "ccv",
                        "value" => "\e604"
                ],
                "visibility" => [
                        "show_if_simple",
                        "show_if_variable",
                        "show_if_grouped"
                ],
                "showInUI" => false
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['id']);

        return $response['id'];
    }

    /**
     * @param $productDataId
     *
     * @depends can_modify_product_data
     * @test
     * @return string
     * @throws \Exception
     */
    public function can_add_fields_to_product_data($productDataId)
    {
        $response = $this->callAuthenticatedRestApi('POST', '/woocommerce/product-data/' . $productDataId . '/fields', [
            [
                "name" => "string",
                "type" => "Text",
                "defaultValue" => "",
                "description" => "",
                "isRequired" => true,
                "options" => []
            ],
            [
                "name" => "string",
                "type" => "Select",
                "defaultValue" => "",
                "description" => "",
                "isRequired" => true,
                "options" => [
                    ["label" => "foo", "value" => 123],
                    ["label" => "foo2", "value" => 453],
                    ["label" => "foo3", "value" => "baz"],
                ],
            ],
        ]);

        $this->assertEquals(201, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['ids']);

        return $productDataId;
    }

    /**
     * @param $productDataId
     *
     * @depends can_add_fields_to_product_data
     * @test
     * @return string
     * @throws \Exception
     */
    public function can_fetch_fields_to_product_data($productDataId)
    {
        $response = $this->callAuthenticatedRestApi('GET', '/woocommerce/product-data/' . $productDataId . '/fields', []);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertCount(2, $response);
    }

    /**
     * @param $productDataId
     *
     * @depends can_add_fields_to_product_data
     * @test
     * @return string
     * @throws \Exception
     */
    public function can_edit_product_data_fields($productDataId)
    {
        $response = $this->callAuthenticatedRestApi('PUT', '/woocommerce/product-data/' . $productDataId . '/fields', [
                [
                        "name" => "string",
                        "type" => "Text",
                        "defaultValue" => "",
                        "description" => "",
                        "isRequired" => true,
                        "options" => []
                ],
                [
                        "name" => "text",
                        "type" => "Text",
                        "defaultValue" => "",
                        "description" => "",
                        "isRequired" => false,
                        "options" => []
                ],
                [
                        "name" => "string",
                        "type" => "Select",
                        "defaultValue" => "",
                        "description" => "",
                        "isRequired" => true,
                        "options" => [
                                ["label" => "foo", "value" => 123],
                                ["label" => "foo2", "value" => 453],
                                ["label" => "foo3", "value" => "baz"],
                        ],
                ],
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['ids']);

        return $response['ids'][0];
    }

    /**
     * @param array $productData
     *
     * @depends can_edit_product_data_fields
     * @test
     * @return array
     * @throws \Exception
     */
    public function can_fetch_product_data_fields(array $productData = [])
    {
        $response = $this->callAuthenticatedRestApi('GET', '/woocommerce/product-data/' . $productData['product_data_id'] . '/fields/'. $productData['field'], []);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertEquals($response['id'], $productData['field']);

        return $productData;
    }

    /**
     * @param array $productData
     *
     * @depends can_fetch_product_data_fields
     * @test
     * @return array
     * @throws \Exception
     */
    public function can_delete_product_data_fields(array $productData = [])
    {
        $response = $this->callAuthenticatedRestApi('DELETE', '/woocommerce/product-data/' . $productData['product_data_id'] . '/fields/'. $productData['field'], []);

        $this->assertEquals(200, $response['status']);

        return $productData;
    }

    /**
     * @param array $productData
     *
     * @depends can_delete_product_data_fields
     * @test
     * @return array
     * @throws \Exception
     */
    public function can_delete_all_product_data_fields(array $productData = [])
    {
        $response = $this->callAuthenticatedRestApi('DELETE', '/woocommerce/product-data/' . $productData['product_data_id'] . '/fields', []);

        $this->assertEquals(200, $response['status']);

        return $productData['product_data_id'];
    }

    /**
     * @param string $productDataId
     *
     * @depends can_delete_all_product_data_fields
     * @test
     * @throws \Exception
     */
    public function can_delete_all_product_data($productDataId)
    {
        $response = $this->callAuthenticatedRestApi('DELETE', '/woocommerce/product-data/' . $productDataId , []);

        $this->assertEquals(200, $response['status']);
    }
}
