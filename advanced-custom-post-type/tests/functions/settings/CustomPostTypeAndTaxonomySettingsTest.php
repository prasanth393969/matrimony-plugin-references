<?php

namespace ACPT\Tests;

class CustomPostTypeAndTaxonomySettingsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function dont_register_a_new_custom_post_type_with_missing_params()
	{
		$new_custom_post_type = register_acpt_post_type([
			'post_name' => 'new-cpt',
		]);

		$this->assertNull($new_custom_post_type);
		$this->assertFalse(post_type_exists('new-cpt'));
	}

	/**
	 * @test
	 */
	public function can_register_a_new_custom_post_type()
	{
		$new_custom_post_type = register_acpt_post_type([
			'post_name' => 'new-cpt',
			'singular_label' => 'New CPT',
			'plural_label' => 'New CPTs',
			'icon' => 'admin-appearance',
			'supports' => [
				'title',
				'editor',
				'comments',
				'revisions',
				'trackbacks',
				'author',
				'excerpt',
			],
			'labels' => [
                'menu_name' => 'This is the menu name'
            ],
			'settings' => [],
			'permissions' => [
				[
					'user_role' => 'editor',
					'permissions' => [
						'edit_s' => true,
						'edit_private_s' => true,
						'edit_published_s' => true,
						'edit_others_s' => true,
						'publish_s' => true,
						'read_private_s' => true,
						'delete_s' => true,
						'delete_private_s' => true,
						'delete_published_s' => true,
						'delete_others_s' => true,
					],
				],
				[
					'user_role' => 'subscriber',
					'permissions' => [
						'edit_s' => false,
						'edit_private_s' => false,
						'edit_published_s' => false,
						'edit_others_s' => false,
						'publish_s' => false,
						'read_private_s' => true,
						'delete_s' => true,
						'delete_private_s' => true,
						'delete_published_s' => true,
						'delete_others_s' => false,
					],
				]
			],
		]);

		$this->assertEquals($new_custom_post_type->name, 'new-cpt');
		$this->assertTrue(post_type_exists('new-cpt'));
	}

	/**
	 * @test
	 */
	public function can_throw_an_error_when_register_a_new_taxonomy()
	{
		$new_taxonomy = register_acpt_taxonomy([
			'slug' => 'new-tax',
		]);

		$this->assertFalse($new_taxonomy);
		$this->assertFalse(taxonomy_exists('new-tax'));
	}

	/**
	 * @test
	 */
	public function can_register_a_new_taxonomy()
	{
		$new_taxonomy = register_acpt_taxonomy([
			'slug' => 'new-tax',
			'singularLabel' => 'New Taxonomy',
			'pluralLabel' => 'New Taxonomies',
			'labels' => [],
			'settings' => [],
			'post_types' => [
				'new-cpt'
			],
			'permissions' => [
				[
					'user_role' => 'editor',
					'permissions' => [
						'edit' => true,
						'manage' => true,
						'assign' => true,
						'delete' => true
					],
				],
				[
					'user_role' => 'subscriber',
					'permissions' => [
						'edit' => false,
						'manage' => false,
						'assign' => true,
						'delete' => true
					],
				]
			],
		]);

		$this->assertTrue($new_taxonomy);
		$this->assertTrue(taxonomy_exists('new-tax'));

		$taxonomies = get_object_taxonomies([
			'post_type' => 'new-cpt'
		]);

		$this->assertTrue(in_array('new-tax', array_values($taxonomies)));
	}

	/**
	 * @test
	 */
	public function can_assoc_a_taxonomy_with_post_type()
	{
		assoc_acpt_taxonomy_to_acpt_post('new-tax', 'page');

		$taxonomies = get_object_taxonomies([
			'post_type' => 'page'
		]);

		$this->assertTrue(in_array('new-tax', array_values($taxonomies)));
	}

	/**
	 * @test
	 */
	public function can_remove_a_taxonomy_from_post_type()
	{
		remove_assoc_acpt_taxonomy_from_acpt_post('new-tax', 'new-cpt');
		remove_assoc_acpt_taxonomy_from_acpt_post('new-tax', 'page');

		$taxonomies = get_object_taxonomies([
			'post_type' => 'new-cpt'
		]);

		$this->assertFalse(in_array('new-tax', array_values($taxonomies)));

		$taxonomies = get_object_taxonomies([
			'post_type' => 'page'
		]);

		$this->assertFalse(in_array('new-tax', array_values($taxonomies)));
	}

	/**
	 * @test
	 */
	public function can_delete_a_custom_post_type()
	{
		$this->assertTrue(delete_acpt_post_type('new-cpt', true));
		$this->assertFalse(post_type_exists('new-cpt'));
	}

	/**
	 * @test
	 */
	public function can_delete_a_taxonomy()
	{
		$this->assertTrue(delete_acpt_taxonomy('new-tax'));
		$this->assertFalse(taxonomy_exists('new-tax'));
	}
}