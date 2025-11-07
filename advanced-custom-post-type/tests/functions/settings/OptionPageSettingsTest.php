<?php

namespace ACPT\Tests;

class OptionPageSettingsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function dont_register_a_new_option_page_with_missing_params()
	{
		$new_page = register_acpt_option_page([
			'menu_slug' => 'new-page',
		]);

		$this->assertFalse($new_page);
	}

	/**
	 * @test
	 */
	public function can_register_a_new_option_page()
	{
		$new_page = register_acpt_option_page([
			'menu_slug' => 'new-page',
			'page_title' => 'New page',
			'menu_title' => 'New page menu title',
			'icon' => 'admin-appearance',
			'capability' => 'manage_options',
			'description' => 'lorem ipsum',
			'position' => 77,
			'permissions' => [
				[
					'user_role' => 'editor',
					'permissions' => [
						'edit' => false,
						'read' => true,
					],
				],
				[
					'user_role' => 'subscriber',
					'permissions' => [
						'edit' => false,
						'read' => false,
					],
				]
			],
		]);

		$this->assertTrue($new_page);
	}

	/**
	 * @test
	 */
	public function cant_register_a_new_child_option_page_with_not_existent_parent()
	{
		$new_child_page = register_acpt_option_page([
			'menu_slug' => 'new-child-page',
			'page_title' => 'New child page',
			'menu_title' => 'New child page menu title',
			'parent' => 'not_existent_parent',
			'capability' => 'manage_options',
			'description' => 'lorem ipsum',
			'position' => 999,
		]);

		$this->assertFalse($new_child_page);
	}

	/**
	 * @test
	 */
	public function can_register_a_new_child_option_page()
	{
		$new_child_page = register_acpt_option_page([
			'menu_slug' => 'new-child-page',
			'page_title' => 'New child page',
			'menu_title' => 'New child page menu title',
			'parent' => 'new-page',
			'capability' => 'manage_options',
			'description' => 'lorem ipsum',
			'position' => 999,
		]);

		$this->assertTrue($new_child_page);
	}

	/**
	 * @test
	 */
	public function can_delete_option_pages()
	{
		$this->assertTrue(delete_acpt_option_page('new-page', true));
	}
}