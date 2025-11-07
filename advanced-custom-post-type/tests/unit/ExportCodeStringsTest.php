<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Utils\ExportCode\ExportCodeStrings;

class ExportCodeStringsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_raise_expeption_with_wrong_meta_type()
	{
		try {
			ExportCodeStrings::export('not-allowed', 'new-cpt');
		} catch (\Exception $exception){
			$this->assertEquals($exception->getMessage(), 'not-allowed is not valid `belongsTo` param.');
		}
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_generate_code_strings_for_custom_post_type()
	{
		// create a cpt
		register_acpt_post_type([
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
				'menu_name' => 'New CPT Menu',
			],
			'settings' => [
				'capability_type' => 'post',
			],
		]);

		$codeStrings = ExportCodeStrings::export(MetaTypes::CUSTOM_POST_TYPE, 'new-cpt');

		// delete cpt
		delete_acpt_post_type('new-cpt');

		$exptectedWordressCodeString = '<php
register_post_type(\'new-cpt\', [
           \'label\' => \'New CPTs\',
           \'singular_name\' => \'New-cpt\',
        \'labels\' => [
           \'menu_name\' => \'New CPT Menu\',
        ],
           \'public\' => true,
           \'publicly_queryable\' => true,
           \'query_var\' => true,
           \'menu_icon\' => \'dashicons-admin-appearance\',
           \'rewrite\' => true,
           \'capability_type\' => \'post\',
           \'hierarchical\' => false,
           \'menu_position\' => null,
        \'supports\' => [
           \'title\',
           \'editor\',
           \'comments\',
           \'revisions\',
           \'trackbacks\',
           \'author\',
           \'excerpt\',
        ],
           \'has_archive\' => false,
           \'show_in_rest\' => true,

]);';

		$exptectedACPTCodeString = '<?php
register_acpt_post_type([
        \'post_name\' => \'new-cpt\',
        \'singular_label\' => \'New CPT\',
        \'plural_label\' => \'New CPTs\',
        \'icon\' => \'admin-appearance\',
        \'supports\' => [
           \'title\',
           \'editor\',
           \'comments\',
           \'revisions\',
           \'trackbacks\',
           \'author\',
           \'excerpt\',
       ],
        \'labels\' => [
            \'menu_name\' => \'New CPT Menu\',
       ],
        \'settings\' => [
            \'capability_type\' => \'post\',
       ],
]);';

		$this->assertEquals($this->trimAllSpaces($exptectedACPTCodeString), $this->trimAllSpaces($codeStrings['acpt']));
		$this->assertEquals($this->trimAllSpaces($exptectedWordressCodeString), $this->trimAllSpaces($codeStrings['wordpress']));
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_generate_code_strings_for_option_page()
	{
		// create option page
		register_acpt_option_page([
			'menu_slug' => 'new-page',
			'page_title' => 'New page',
			'menu_title' => 'New page menu title',
			'icon' => 'admin-appearance',
			'capability' => 'manage_options',
			'description' => 'lorem ipsum',
			'position' => 77,
		]);

		$codeStrings = ExportCodeStrings::export(MetaTypes::OPTION_PAGE, 'new-page');

		// delete option page
		delete_acpt_option_page('new-page');

		$exptectedACPTCodeString = '<?php 
		register_acpt_option_page([
			\'menu_slug\' => \'new-page\',
			\'page_title\' => \'New page\',
			\'menu_title\' => \'New page menu title\',
			\'icon\' => \'admin-appearance\',
			\'capability\' => \'manage_options\',
			\'description\' => \'lorem ipsum\',
			\'position\' => 77,
		]);';

		$exptectedWordressCodeString = '<?php
		add_menu_page(
			\'Newpage\',
			\'Newpagemenutitle\',
			\'manage_options\',
			\'new-page\',
			function(){
				//write your own code here
			},
			\'dashicons-admin-appearance\',
			77
		);';

		$this->assertEquals($this->trimAllSpaces($exptectedACPTCodeString), $this->trimAllSpaces($codeStrings['acpt']));
		$this->assertEquals($this->trimAllSpaces($exptectedWordressCodeString), $this->trimAllSpaces($codeStrings['wordpress']));
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_generate_code_strings_for_taxonomy()
	{
		// create taxonomy
		register_acpt_taxonomy([
			'slug' => 'new-tax',
			'singular_label' => 'New Taxonomy',
			'plural_label' => 'New Taxonomies',
			'labels' => [],
			'settings' => [],
			'post_types' => [
				'page'
			]
		]);

		$codeStrings = ExportCodeStrings::export(MetaTypes::TAXONOMY, 'new-tax');

		// delete taxonomy
		delete_acpt_taxonomy('new-tax');

		$exptectedACPTCodeString = '<?php
		register_acpt_taxonomy([
			\'slug\' => \'new-tax\',
			\'singular_label\' => \'New Taxonomy\',
			\'plural_label\' => \'New Taxonomies\',
			\'labels\' => [],
			\'settings\' => [],
			\'post_types\' => [
				\'page\',
			]
		]);
		';
		$exptectedWordressCodeString = '<?php
		register_taxonomy(
			\'new-tax\',
			[\'page\',],
			[
				\'hierarchical\'=>true,
				\'label\'=>\'NewTaxonomies\',
				\'singular_label\'=>\'NewTaxonomy\',
				\'show_ui\'=>true,
				\'query_var\'=>true,
				\'show_admin_column\'=>true,
				\'show_in_rest\'=>true,
				\'rewrite\'=>[\'slug\'=>\'new-tax\',],
				\'labels\'=>[]
			,]);';

		$this->assertEquals($this->trimAllSpaces($exptectedACPTCodeString), $this->trimAllSpaces($codeStrings['acpt']));
		$this->assertEquals($this->trimAllSpaces($exptectedWordressCodeString), $this->trimAllSpaces($codeStrings['wordpress']));
	}

	/**
	 * @param $string
	 *
	 * @return string
	 */
	private function trimAllSpaces($string)
	{
		return preg_replace('/\s+/', '', $string);
	}
}