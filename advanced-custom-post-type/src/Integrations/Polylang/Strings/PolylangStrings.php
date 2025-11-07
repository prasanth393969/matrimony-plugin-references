<?php

namespace ACPT\Integrations\Polylang\Strings;

use ACPT\Core\Models\CustomPostType\CustomPostTypeModel;
use ACPT\Core\Models\Dataset\DatasetModel;
use ACPT\Core\Models\Meta\MetaFieldOptionModel;
use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Models\Taxonomy\TaxonomyModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\DatasetRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Core\Repository\WooCommerceProductDataRepository;

class PolylangStrings
{
	/**
	 * @var CustomPostTypeModel[]
	 */
	private $postTypes;

	/**
	 * @var TaxonomyModel[]
	 */
	private $taxonomies;

	/**
	 * @var OptionPageModel[]
	 */
	private $optionPages;

	/**
	 * @var MetaFieldOptionModel[]
	 */
	private $options;

	/**
	 * @var DatasetModel[]
	 */
	private $dataSets;

	/**
	 * @var MetaFieldOptionModel[]
	 */
	private $wooCommerceOptions;

	/**
	 * PolylangStrings constructor.
	 * @throws \Exception
	 */
	public function __construct()
	{
		$this->postTypes = CustomPostTypeRepository::get([]);
		$this->taxonomies = TaxonomyRepository::get([]);
		$this->optionPages = OptionPageRepository::get([]);
		$this->dataSets = DatasetRepository::get([]);
		$this->options = MetaRepository::getAllOptions();
		$this->wooCommerceOptions = WooCommerceProductDataRepository::getAllOptions();
	}

	/**
	 * Add ACPT strings to Polylang settings
	 */
	public function register()
	{
		try {
			// Add ACPT post types and taxonomies to Polylang settings.
			add_filter( 'pll_get_post_types', array( $this, 'pllGetTypes' ), 10, 2 );
			add_filter( 'pll_get_taxonomies', array( $this, 'pllGetTypes' ), 10, 2 );

			// Custom Post Type labels
			foreach ($this->postTypes as $postType){
				if(!$postType->isNative()){

					$labels = $postType->getLabels();

					$this->registerString($postType->getName().'_singular', $postType->getSingular());
					$this->registerString($postType->getName().'_plural', $postType->getPlural());

					$labelsArray = [
						'menu_nam',
						'all_items',
						'add_new',
						'add_new_item',
						'edit_item',
						'new_item',
						'view_item',
						'view_items',
						'search_item',
						'not_found',
						'not_found_in_trash',
						'filter_items_list',
						'items_list_navigation',
						'items_list',
						'item_published',
						'item_published_privately',
						'item_reverted_to_draft',
						'item_scheduled',
						'item_updated',
					];

					foreach ($labelsArray as $label){
						if(isset($labels[$label])){
							$this->registerString($postType->getName().'_label_'.$label, $labels[$label]);
						}
					}
				}
			}

			// Taxonomies labels
			foreach ($this->taxonomies as $taxonomy){
				if(!$taxonomy->isNative()){

					$labels = $taxonomy->getLabels();

					$this->registerString($taxonomy->getSlug().'_singular', $taxonomy->getSingular());
					$this->registerString($taxonomy->getSlug().'_plural', $taxonomy->getPlural());

					$labelsArray = [
						'name',
						'singular_name',
						'search_items',
						'popular_items',
						'all_items',
						'parent_item',
						'parent_item_colon',
						'edit_item',
						'view_item',
						'update_item',
						'add_new_item',
						'new_item_name',
						'separate_items_with_commas',
						'add_or_remove_items',
						'choose_from_most_used',
						'not_found',
						'no_terms',
						'filter_by_item',
						'items_list_navigation',
						'items_list',
						'back_to_items',
						'item_link',
						'item_link_description',
						'menu_name',
						'name_admin_bar',
						'archives',
					];

					foreach ($labelsArray as $label){
						if(isset($labels[$label])){
							$this->registerString($taxonomy->getSlug().'_label_'.$label, $labels[$label]);
						}
					}
				}
			}

			// Option page labels
			foreach ($this->optionPages as $optionPage){
				$this->registerString($optionPage->getMenuSlug().'_menu_title', $optionPage->getMenuTitle());
				$this->registerString($optionPage->getMenuSlug().'_page_title', $optionPage->getPageTitle());
				$this->registerString($optionPage->getMenuSlug().'_description', $optionPage->getDescription());

				if(!empty($optionPage->getChildren())){
					foreach ($optionPage->getChildren() as $childPage){
						$this->registerString($childPage->getMenuSlug().'_menu_title', $childPage->getMenuTitle());
						$this->registerString($childPage->getMenuSlug().'_page_title', $childPage->getPageTitle());
						$this->registerString($childPage->getMenuSlug().'_description', $childPage->getDescription());
					}
				}
			}

			// Options
			foreach ($this->options as $option){
				$this->registerString($option->getValue().'_label', $option->getLabel());
				$this->registerString($option->getValue().'_value', $option->getValue());
			}

			// Dataset
			foreach ($this->dataSets as $dataSet){
				foreach ($dataSet->getItems() as $datasetItem){
					$this->registerString($datasetItem->getValue().'_label', $datasetItem->getLabel());
					$this->registerString($datasetItem->getValue().'_value', $datasetItem->getValue());
				}
			}

			// WooCommerce field options
			foreach ($this->wooCommerceOptions as $wooCommerceOption){
				$this->registerString($wooCommerceOption->getValue().'_label', $wooCommerceOption->getLabel());
				$this->registerString($wooCommerceOption->getValue().'_value', $wooCommerceOption->getValue());
			}

		} catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
	}

	/**
	 * Register a string in Polylang
	 *
	 * @param $name
	 * @param $string
	 * @param bool $multiline
	 */
	private function registerString($name, $string, $multiline = false)
	{
		if(function_exists('pll_register_string')){
			pll_register_string( $name, $string, 'ACPT', $multiline );
		}
	}

	/**
	 * Add ACPT post types and taxonomies to Polylang settings.
	 *
	 * @since 2.1
	 *
	 * @param string[] $types      List of post type or taxonomy names.
	 * @param bool     $isSettings True when displaying the list in Polylang settings.
	 * @return string[]
	 */
	public function pllGetTypes( $types, $isSettings )
	{
		if ( $isSettings ) {

			$type = substr( current_filter(), 8 );

			switch ($type){
				case "post_types":
					$postTypes = [];

					foreach ($this->postTypes as $postType) {
						if ( !$postType->isNative() ) {
							$postTypes[$postType->getName()] = $postType->getName();
						}
					}

					return array_merge( $types, $postTypes );


				case "taxonomies":
					$taxonomies = [];

					foreach ($this->taxonomies as $taxonomy) {
						if ( !$taxonomy->isNative() ) {
							$taxonomies[$taxonomy->getSlug()] = $taxonomy->getSlug();
						}
					}

					return array_merge( $types, $taxonomies );
			}
		}

		return $types;
	}
}