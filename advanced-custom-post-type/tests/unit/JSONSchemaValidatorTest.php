<?php

namespace ACPT\Tests;

use ACPT\Core\JSON\QueryFilterSchema;
use ACPT\Utils\Data\JSONSchemaValidator;

class JSONSchemaValidatorTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_validate_json_schema()
    {
        try {
            $validation = new JSONSchemaValidator( new QueryFilterSchema() );

            $validSchema = [
                "params" => [
                    "author_name" => "string",
                    "category_name" => "string",
                ]
            ];

            $invalidSchema = [
                "params" => [
                    "author_name" => "string",
                    "category_name" => "string",
                    "sort2222" => "asc"
                ]
            ];

            $validation->validate($validSchema);
            $validation->validate($invalidSchema);
        } catch (\Exception $exception){
            $this->assertEquals('Additional properties not allowed: sort2222', $exception->getMessage());
        }
    }
}