<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;

class MetaApiV1Test extends RestApiV1TestCase
{
    /**
     * @test
     */
    public function raise_error_with_wrong_payload()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/meta',  [
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
    public function can_add_a_very_simple_meta()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/meta',  [
	        "name" => "group_name",
	        "label" => "group label",
	        "belongs" => [
		        [
			        "belongsTo" => MetaTypes::CUSTOM_POST_TYPE,
			        "operator"  => Operator::EQUALS,
			        "find"      => 'page'
		        ]
	        ],
	        "boxes" => [
		        [
			        "name" => 'box_name',
			        "label" => 'box label',
			        "sort" => 1,
			        "fields" => [
				        [
					        "name" => "string",
					        "type" => "Text",
					        "defaultValue" => "string",
					        "description" => "string",
					        "isRequired" => true,
					        "showInArchive" => true,
					        "options" => [],
					        "advancedOptions" => [
						        [
							        "key" => "max",
							        "value" => "123"
						        ],
						        [
							        "key" => "min",
							        "value" => "2"
						        ]
					        ],
					        "visibilityConditions" => [],
					        "relations" => [],
					        "hasChildren" => false,
					        "children" => [],
					        "blocks" => [],
				        ],
				        [
					        "name" => "select",
					        "type" => "Select",
					        "defaultValue" => "foo",
					        "description" => "bla bla",
					        "isRequired" => false,
					        "showInArchive" => false,
					        "options" => [
						        [
						        	"label" => "foo",
							        "value" => 123,
						            "isDefault" => false
						        ],
						        [
						        	"label" => "foo2",
							        "value" => 453,
						            "isDefault" => false
						        ],
						        [
						        	"label" => "foo3",
							        "value" => "baz",
						            "isDefault" => false
						        ],
					        ],
					        "visibilityConditions" => [],
					        "relations" => [],
					        "hasChildren" => false,
					        "children" => [],
					        "blocks" => [],
				        ],
				        [
					        "name" => "Flex",
					        "type" => "FlexibleContent",
					        "defaultValue" => null,
					        "description" => "bla bla",
					        "isRequired" => false,
					        "showInArchive" => false,
					        "options" => [],
					        "visibilityConditions" => [],
					        "relations" => [],
					        "hasChildren" => false,
					        "children" => [],
					        "blocks" => [
						        [
							        "name" => "block",
							        "label" => "Block label",
							        "fields" => [
								        [
									        "name" => "Nested",
									        "type" => "Text",
									        "defaultValue" => "string",
									        "description" => "string",
									        "isRequired" => true,
									        "options" => [],
									        "advancedOptions" => [],
									        "visibilityConditions" => [],
								        ],
							        ]
						        ],
						        [
							        "name" => "second_block",
							        "label" => "Second block",
							        "fields" => [
								        [
									        "name" => "Nested",
									        "type" => "Text",
									        "defaultValue" => "string",
									        "description" => "string",
									        "isRequired" => true,
									        "options" => [],
									        "advancedOptions" => [],
									        "visibilityConditions" => [],
								        ],
							        ]
						        ]
					        ],
				        ]
			        ]
		        ]
	        ],
        ]);

        $this->assertEquals(201, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['id']);

        return $response['id'];
    }

	/**
	 * @depends can_add_a_very_simple_meta
	 * @test
	 *
	 * @param $id
	 *
	 * @return string
	 * @throws \Exception
	 */
    public function can_update_a_very_simple_meta($id)
    {
        $response = $this->callAuthenticatedRestApi('PUT', '/meta/'.$id,  [
	        "name" => "group_name",
	        "label" => "group label",
	        "belongs" => [
		        [
			        "belongsTo" => MetaTypes::CUSTOM_POST_TYPE,
			        "operator"  => Operator::EQUALS,
			        "find"      => 'page'
		        ]
	        ],
	        "boxes" => [
		       [
			       "name" => 'box_name',
			       "label" => 'box label',
			       "sort" => 1,
			       "fields" => [
				       [
					       "name" => "string",
					       "type" => "Text",
					       "defaultValue" => "string",
					       "description" => "string",
					       "isRequired" => true,
					       "showInArchive" => true,
					       "options" => [],
					       "visibilityConditions" => [],
					       "relations" => [],
					       "hasChildren" => false,
					       "children" => []
				       ],
				       [
					       "name" => "select",
					       "type" => "Select",
					       "defaultValue" => "foo",
					       "description" => "bla bla",
					       "isRequired" => false,
					       "showInArchive" => false,
					       "options" => [
								[
									"label" => "foo",
									"value" => 123,
									"isDefault" => false
								],
								[
									"label" => "foo2",
									"value" => 453,
									"isDefault" => false
								],
								[
									"label" => "foo3",
									"value" => "baz",
									"isDefault" => false
								],
								[
									"label" => "foo4",
									"value" => "baz45",
									"isDefault" => false
								],
								[
									"label" => "foo5",
									"value" => "baz3232",
									"isDefault" => false
								],
					       ],
					       "visibilityConditions" => [
						       [
							       "type" => [
								       "type" => "VALUE",
								       "value" => "VALUE",
							       ],
							       "operator" => "!=",
							       "value" => 453,
						       ],
					       ],
					       "relations" => [],
					       "hasChildren" => false,
					       "children" => []
				       ],
				       [
					       "name" => "url",
					       "type" => "Url",
					       "defaultValue" => "https://acpt.io",
					       "description" => "",
					       "isRequired" => true,
					       "showInArchive" => true,
					       "options" => [],
					       "visibilityConditions" => [],
					       "relations" => [],
					       "hasChildren" => false,
					       "children" => []
				       ],
			       ]
		       ]
	        ],
        ]);

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertNotEmpty($response['id']);

	    return $response['id'];
    }

	/**
	 * @depends can_update_a_very_simple_meta
	 * @test
	 *
	 * @param $id
	 *
	 * @throws \Exception
	 */
	public function can_fetch_and_then_delete_single_meta($id)
	{
		$response = $this->callAuthenticatedRestApi('GET', '/meta/'.$id,  []);

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);
		$id = $response['id'];

		$this->assertNotEmpty($id);

		$response = $this->callAuthenticatedRestApi('GET', '/meta/'.$id,  []);

		$this->assertEquals(200, $response['status']);

		$response = json_decode($response['response'], true);

		$this->assertEquals($id, $response['id']);

		$response = $this->callAuthenticatedRestApi('DELETE', '/meta/'.$id,  []);

		$this->assertEquals(200, $response['status']);
	}
}