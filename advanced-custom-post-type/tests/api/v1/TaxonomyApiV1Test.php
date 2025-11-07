<?php

namespace ACPT\Tests;

class TaxonomyApiV1Test extends RestApiV1TestCase
{
    /**
     * @test
     */
    public function can_fetch_taxonomy_definitions()
    {
        $response = $this->callAuthenticatedRestApi('GET', '/taxonomy');

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
        $response = $this->callAuthenticatedRestApi('POST', '/taxonomy', [
                'foo' => 'bar'
        ]);

        $this->assertEquals(500, $response['status']);
    }

    /**
     * @test
     */
    public function can_create_taxonomy_definition()
    {
        $payload = [
            "slug" => "from-api",
            "singular" => "From Api",
            "plural" => "From Api",
            "labels" => [
                "name" => "From Api",
                "singular_name" => "string",
                "search_items" => "string",
                "popular_items" => "string",
                "all_items" => "string",
                "parent_item" => "string",
                "parent_item_colon" => "string",
                "edit_item" => "string",
                "view_item" => "string",
                "update_item" => "string",
                "add_new_item" => "string",
                "new_item_name" => "string",
                "separate_items_with_commas" => "string",
                "add_or_remove_items" => "string",
                "choose_from_most_used" => "string",
                "not_found" => "string",
                "no_terms" => "string",
                "filter_by_item" => "string",
                "items_list_navigation" => "string",
                "items_list" => "string",
                "most_used" => "string",
                "back_to_items" => "string"
            ],
            "settings" => [
                "public" => true,
                "publicly_queryable" => "string",
                "hierarchical" => true,
                "show_ui" => true,
                "show_in_menu" => true,
                "show_in_nav_menus" => true,
                "show_in_rest" => true,
                "rest_base" => "string",
                "rest_controller_class" => "string",
                "show_tagcloud" => true,
                "show_in_quick_edit" => true,
                "show_admin_column" => true,
                "capabilities" => [
                    "manage_terms",
                    "edit_terms",
                    "delete_terms",
                    "assign_terms"
                ],
                "rewrite" => "string",
                "custom_rewrite" => "string",
                "query_var" => "string",
                "custom_query_var" => "string",
                "default_term" => "string",
                "sort" => "string"
            ]
        ];

        $response = $this->callAuthenticatedRestApi('POST', '/taxonomy', $payload);

        $this->assertEquals(201, $response['status']);
    }


    /**
     * @test
     */
    public function can_fetch_taxonomy_definition()
    {
        $response = $this->callAuthenticatedRestApi('GET', '/taxonomy/from-api');

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertEquals("from-api", $response['slug']);
        $this->assertEquals("From Api", $response['singular']);
        $this->assertEquals("From Api", $response['plural']);
    }

    /**
     * @test
     */
    public function can_edit_taxonomy_definition()
    {
        $payload = [
            "slug" => "from-api",
            "singular" => "From Api modified",
            "plural" => "From Api modified",
            "labels" => [
                "name" => "From Api modified",
                "singular_name" => "string",
                "search_items" => "string",
                "popular_items" => "string",
                "all_items" => "string",
                "parent_item" => "string",
                "parent_item_colon" => "string",
                "edit_item" => "string",
                "view_item" => "string",
                "update_item" => "string",
                "add_new_item" => "string",
                "new_item_name" => "string",
                "separate_items_with_commas" => "string",
                "add_or_remove_items" => "string",
                "choose_from_most_used" => "string",
                "not_found" => "string",
                "no_terms" => "string",
                "filter_by_item" => "string",
                "items_list_navigation" => "string",
                "items_list" => "string",
                "most_used" => "string",
                "back_to_items" => "string"
            ],
            "settings" => [
                "public" => true,
                "publicly_queryable" => "string",
                "hierarchical" => true,
                "show_ui" => true,
                "show_in_menu" => true,
                "show_in_nav_menus" => true,
                "show_in_rest" => true,
                "rest_base" => "string",
                "rest_controller_class" => "string",
                "show_tagcloud" => true,
                "show_in_quick_edit" => true,
                "show_admin_column" => true,
                "capabilities" => [
                    "manage_terms",
                    "edit_terms",
                    "delete_terms",
                    "assign_terms"
                ],
                "rewrite" => "string",
                "custom_rewrite" => "string",
                "query_var" => "string",
                "custom_query_var" => "string",
                "default_term" => "string",
                "sort" => "string"
            ]
        ];

        $response = $this->callAuthenticatedRestApi('PUT', '/taxonomy/from-api', $payload);

        $this->assertEquals(200, $response['status']);
    }

    /**
     * @test
     */
    public function can_fetch_modified_taxonomy_definition()
    {
        $response = $this->callAuthenticatedRestApi('GET', '/taxonomy/from-api');

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertEquals("from-api", $response['slug']);
        $this->assertEquals("From Api modified", $response['singular']);
        $this->assertEquals("From Api modified", $response['plural']);
    }

    /**
     * @test
     */
    public function can_assoc_taxonomy_with_cpt()
    {
        $response = $this->callAuthenticatedRestApi('POST', '/taxonomy/assoc/from-api/page');

        $this->assertEquals(200, $response['status']);
    }

    /**
     * @test
     */
    public function can_delete_taxonomy_definition()
    {
        $response = $this->callAuthenticatedRestApi('DELETE', '/taxonomy/from-api');

        $this->assertEquals(200, $response['status']);

        $response = $this->callAuthenticatedRestApi('GET', '/taxonomy/from-api');

        $this->assertEquals(404, $response['status']);
    }
}