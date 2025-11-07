<?php

namespace ACPT\Tests;

use ACPT\Utils\Data\Formatter\Formatter;

class FormatterTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_format_and_parse_json()
	{
		$data = $this->data();
		$data20 = $this->data20();

		$json = Formatter::format("json", $data);
		$array = Formatter::toArray("json", $json);

		$this->assertEquals($array, $data);

		$json = Formatter::format("json", $data20);
		$array = Formatter::toArray("json", $json);

		$this->assertEquals($array, $data20);
	}

	/**
	 * @test
	 */
	public function can_format_and_parse_yaml()
	{
		$data = $this->data();
		$data20 = $this->data20();

		$yaml = Formatter::format("yaml", $data);
		$array = Formatter::toArray("yaml", $yaml);

		$this->assertEquals($array, $data);

		$yaml = Formatter::format("yaml", $data20);
		$array = Formatter::toArray("yaml", $yaml);

		$this->assertEquals($array, $data20);
	}

	/**
	 * @test
	 */
	public function can_format_and_parse_xml()
	{
		$data = $this->data();

		$xml = Formatter::format("xml", $data);
		$array = Formatter::toArray("xml", $xml);

		$this->assertEquals($array, $data);

		$data20 = $this->data20();

		$xml = Formatter::format("xml", $data20);
		$array = Formatter::toArray("xml", $xml);

		$this->assertEquals($array, $data20);
	}

	/**
	 * @test
	 */
	public function format_labels_in_xml()
	{
		$xml = '
			<labels>
				<menu_name>Casa</menu_name>
				<all_items>All Casa</all_items>
				<add_new>Add Casa</add_new>
				<add_new_item>Add new Casa</add_new_item>
				<edit_item>Edit Casa</edit_item>
				<new_item>New Casa</new_item>
				<view_item>View Casa</view_item>
				<view_items>View Case</view_items>
				<search_item>Search Case</search_item>
				<not_found>No Casa found</not_found>
				<not_found_in_trash>No Casa found</not_found_in_trash>
				<parent_item_colon>Parent item</parent_item_colon>
				<featured_image>Featured image</featured_image>
				<set_featured_image>Set featured image</set_featured_image>
				<remove_featured_image>Remove featured image</remove_featured_image>
				<use_featured_image>Use featured image</use_featured_image>
				<archives>Archives</archives>
				<insert_into_item>Insert</insert_into_item>
				<uploaded_to_this_item>Upload</uploaded_to_this_item>
				<filter_items_list>Filter Case list</filter_items_list>
				<items_list_navigation>Navigation list Case</items_list_navigation>
				<items_list>List Case</items_list>
				<filter_by_date>Filter by date</filter_by_date>
				<item_published>Casa published</item_published>
				<item_published_privately>Casa published privately</item_published_privately>
				<item_reverted_to_draft>Casa reverted to draft</item_reverted_to_draft>
				<item_scheduled>Casa scheduled</item_scheduled>
				<item_updated>Casa updated</item_updated>
			</labels>
		';

		$array = Formatter::toArray("xml", $xml);

		$this->assertTrue(isset($array['not_found_in_trash']));
		$this->assertTrue(isset($array['parent_item_colon']));
		$this->assertTrue(isset($array['item_published']));
		$this->assertTrue(isset($array['item_published_privately']));
		$this->assertTrue(isset($array['item_reverted_to_draft']));
		$this->assertTrue(isset($array['item_scheduled']));
		$this->assertTrue(isset($array['item_updated']));
		$this->assertCount(28, $array);
	}

	/**
	 * @test
	 */
	public function format_settings_in_xml()
	{
		$xml = '
			<settings>
		        <public value="true">1</public>
		        <publicly_queryable value="true">1</publicly_queryable>
		        <show_ui value="true">1</show_ui>
		        <show_in_menu value="true">1</show_in_menu>
		        <show_in_nav_menus value="true">1</show_in_nav_menus>
		        <show_in_admin_bar value="true">1</show_in_admin_bar>
		        <show_in_rest value="true">1</show_in_rest>
		        <rest_base>NULL</rest_base>
		        <menu_position>NULL</menu_position>
		        <capability_type>post</capability_type>
		        <has_archive value="true">1</has_archive>
		        <rewrite value="false">0</rewrite>
		        <custom_rewrite>NULL</custom_rewrite>
		        <query_var value="true">1</query_var>
		        <custom_query_var>NULL</custom_query_var>
		        <show_in_graphql value="true">1</show_in_graphql>
		        <graphql_single_name>Casa</graphql_single_name>
		        <graphql_plural_name>Case</graphql_plural_name>
			</settings>
		';

		$array = Formatter::toArray("xml", $xml);

		$this->assertCount(18, $array);
		$this->assertTrue($array['has_archive']);
		$this->assertTrue($array['show_in_nav_menus']);
		$this->assertTrue($array['show_in_admin_bar']);
		$this->assertFalse($array['rewrite']);
	}

	/**
	 * @return array
	 */
	private function data()
	{
		return [
			"customPostType" => [
				[
					"id" => "3f7312ce-bb97-4e2b-9194-06194baf9fcc",
					"name" => "events",
					"singular" => "מפגש",
					"plural" => "מפגשים",
					"icon" => "dashicons-calendar-alt",
					"postCount" => "44",
					"supports" => [
						"title",
						"thumbnail"
					],
					"templates" => [
					],
					"labels" => [
						"back_to_items" => "&larr; Go to Tags",
					],
					"existsArchivePageInTheme" => false,
					"existsSinglePageInTheme" => false
				]
			],
			"taxonomy" => [
			],
			"optionPage" => [
			],
			"user" => [
			]
		];
	}

	/**
	 * @return array
	 */
	private function data20()
	{
		return [
			"customPostType" => [],
			"taxonomy" => [],
			"optionPage" => [],
			"meta" => [
				[
					"id" => "ad495d51-1c31-4942-acb8-4ad497ee8225",
					"name" => "cacaca",
					"label" => "cacaca",
					"UIName" => "cacaca",
					"belongs" => [
						[
							"id" => "d0f1ac3c-50bd-4a96-9d1f-a7dfbf6342c8",
							"belongsTo" => "customPostType",
							"operator" => "=",
							"logic" => "OR",
							"find" => "page",
						],
						[
							"id" => "9493126b-86da-4bf1-a971-77d3ddaabe21",
							"belongsTo" => "customPostType",
							"operator" => "=",
							"logic" => "OR",
							"find" => "post",
						],
						[
							"id" => "79fe5107-8afb-4ea9-99b2-3a54909ea81a",
							"belongsTo" => "taxonomy",
							"operator" => "IN",
							"logic" => null,
							"find" => "category,industry-category",
						],
					],
					"fieldsCount" => 0,
					"boxes" => [
						[
							"id" => "9b29545b-0dd4-476e-ac08-a72f1ff351cb",
							"name" => "cavolo",
							"label" => "ciaooooooo",
							"UIName" => "ciaooooooo",
							"sort" => 1,
							"fields" => [],
						],
					],
				],
			],
		];
	}
}