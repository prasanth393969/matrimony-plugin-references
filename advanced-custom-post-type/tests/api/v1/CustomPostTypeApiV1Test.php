<?php

namespace ACPT\Tests;

class CustomPostTypeApiV1Test extends RestApiV1TestCase
{
//    /**
//     * @test
//     */
//    public function can_fetch_cpt_definitions()
//    {
//        $response = $this->callAuthenticatedRestApi('GET', '/cpt');
//
//        $this->assertEquals(200, $response['status']);
//
//        $response = json_decode($response['response'], true);
//
//        $this->assertArrayHasKey("currentPage", $response);
//        $this->assertArrayHasKey("prev", $response);
//        $this->assertArrayHasKey("next", $response);
//        $this->assertArrayHasKey("total", $response);
//        $this->assertArrayHasKey("records", $response);
//    }
//
//    /**
//     * @test
//     */
//    public function return_error_with_invalid_payload()
//    {
//        $response = $this->callAuthenticatedRestApi('POST', '/cpt', [
//            'foo' => 'bar'
//        ]);
//
//        $this->assertEquals(500, $response['status']);
//    }

    /**
     * @test
     */
    public function can_create_cpt_definition()
    {
        $payload = [
                "post_name" => "from-api",
                "singular_label" => "From Api",
                "plural_label" => "From Api",
                "icon" => "admin-multisite",
                "supports" => [
                        "title",
                        "editor",
                        "thumbnail"
                ],
                "labels" => [
                        "menu_name" => "From Api",
                        "all_items" => "string",
                        "add_new" => "string",
                        "add_new_item" => "string",
                        "edit_item" => "string",
                        "new_item" => "string",
                        "view_item" => "string",
                        "view_items" => "string",
                        "search_item" => "string",
                        "not_found" => "string",
                        "not_found_in_trash" => "string",
                        "parent_item_colon" => "string",
                        "featured_image" => "string",
                        "set_featured_image" => "string",
                        "remove_featured_image" => "string",
                        "use_featured_image" => "string",
                        "archives" => "string",
                        "insert_into_item" => "string",
                        "uploaded_to_this_item" => "string",
                        "filter_items_list" => "string",
                        "items_list_navigation" => "string",
                        "items_list" => "string",
                        "filter_by_date" => "string",
                        "item_published" => "string",
                        "item_published_privately" => "string",
                        "item_reverted_to_draft" => "string",
                        "item_scheduled" => "string",
                        "item_updated" => "string"
                ],
                "settings" => [
                        "public" => true,
                        "publicly_queryable" => "string",
                        "show_ui" => true,
                        "show_in_menu" => true,
                        "show_in_nav_menus" => true,
                        "show_in_admin_bar" => true,
                        "show_in_rest" => true,
                        "rest_base" => "string",
                        "menu_position" => "string",
                        "capability_type" => "string",
                        "has_archive" => true,
                        "rewrite" => "string",
                        "custom_rewrite" => "string",
                        "query_var" => "string",
                        "custom_query_var" => "string"
                ]
        ];

        $response = $this->callAuthenticatedRestApi('POST', '/cpt', $payload);

        $this->assertEquals(201, $response['status']);
    }

    /**
     * @test
     */
    public function can_fetch_cpt_definition()
    {
        $response = $this->callAuthenticatedRestApi('GET', '/cpt/from-api');

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertEquals("from-api", $response['name']);
        $this->assertEquals("From Api", $response['singular']);
        $this->assertEquals("From Api", $response['plural']);
    }

    /**
     * @test
     */
    public function can_edit_cpt_definition()
    {
        $payload = [
                "post_name" => "from-api",
                "singular_label" => "From Api modified",
                "plural_label" => "From Api modified",
                "icon" => "admin-multisite",
                "supports" => [
                        "title",
                        "editor",
                        "thumbnail"
                ],
                "labels" => [
                        "menu_name" => "From Api modified",
                        "all_items" => "string",
                        "add_new" => "string",
                        "add_new_item" => "string",
                        "edit_item" => "string",
                        "new_item" => "string",
                        "view_item" => "string",
                        "view_items" => "string",
                        "search_item" => "string",
                        "not_found" => "string",
                        "not_found_in_trash" => "string",
                        "parent_item_colon" => "string",
                        "featured_image" => "string",
                        "set_featured_image" => "string",
                        "remove_featured_image" => "string",
                        "use_featured_image" => "string",
                        "archives" => "string",
                        "insert_into_item" => "string",
                        "uploaded_to_this_item" => "string",
                        "filter_items_list" => "string",
                        "items_list_navigation" => "string",
                        "items_list" => "string",
                        "filter_by_date" => "string",
                        "item_published" => "string",
                        "item_published_privately" => "string",
                        "item_reverted_to_draft" => "string",
                        "item_scheduled" => "string",
                        "item_updated" => "string"
                ],
                "settings" => [
                        "public" => true,
                        "publicly_queryable" => "string",
                        "show_ui" => true,
                        "show_in_menu" => true,
                        "show_in_nav_menus" => true,
                        "show_in_admin_bar" => true,
                        "show_in_rest" => true,
                        "rest_base" => "string",
                        "menu_position" => "string",
                        "capability_type" => "string",
                        "has_archive" => true,
                        "rewrite" => "string",
                        "custom_rewrite" => "string",
                        "query_var" => "string",
                        "custom_query_var" => "string"
                ]
        ];

        $response = $this->callAuthenticatedRestApi('PUT', '/cpt/from-api', $payload);

        $this->assertEquals(200, $response['status']);
    }

    /**
     * @depends can_edit_cpt_definition
     * @test
     */
    public function can_fetch_modified_cpt_definition()
    {
        $response = $this->callAuthenticatedRestApi('GET', '/cpt/from-api');

        $this->assertEquals(200, $response['status']);

        $response = json_decode($response['response'], true);

        $this->assertEquals("from-api", $response['name']);
        $this->assertEquals("From Api modified", $response['singular']);
        $this->assertEquals("From Api modified", $response['plural']);
    }

    /**
     * @test
     */
    public function can_delete_cpt_definition()
    {
        $response = $this->callAuthenticatedRestApi('DELETE', '/cpt/from-api');

        $this->assertEquals(200, $response['status']);

        $response = $this->callAuthenticatedRestApi('GET', '/cpt/from-api');

        $this->assertEquals(404, $response['status']);
    }
}
