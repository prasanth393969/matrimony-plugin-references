<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\DatasetRepository;
use ACPT\Core\Repository\DynamicBlockRepository;
use ACPT\Core\Repository\FormRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Utils\Wordpress\Posts;
use ACPT\Utils\Wordpress\Terms;
use ACPT\Utils\Wordpress\Translator;

class FetchAllFindBelongsQuery implements QueryInterface
{
	/**
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function execute()
	{
	    $postTypes = $this->postTypes();
	    $taxonomies = $this->taxonomies();

		return [
            MetaTypes::CUSTOM_POST_TYPE => $postTypes[MetaTypes::CUSTOM_POST_TYPE],
            BelongsTo::PARENT_POST_ID => $postTypes[BelongsTo::PARENT_POST_ID],
            BelongsTo::POST_ID => $postTypes[BelongsTo::POST_ID],
            BelongsTo::POST_CAT => $this->categories(),
            BelongsTo::POST_TEMPLATE => $this->pageTemplates(),
            MetaTypes::TAXONOMY => $taxonomies[MetaTypes::TAXONOMY],
            BelongsTo::TERM_ID => $taxonomies[BelongsTo::TERM_ID],
            BelongsTo::POST_TAX => $taxonomies[BelongsTo::POST_TAX],
            MetaTypes::OPTION_PAGE => $this->optionPages(),
            MetaTypes::MEDIA => $this->mimeTypes(),
            MetaTypes::META => $this->metaGroups(),
            MetaTypes::FIELDS => $this->metaFieldSlugs(),
            MetaTypes::USER => $this->allUsers(),
            BelongsTo::USER_ID => $this->users(),
            'block' => $this->blocks(),
            'form' => $this->forms(),
            'dataset' => $this->dataSets(),
            'woo_product_data' => $this->productData(),
        ];
	}

    /**
     * @return array
     */
	private function nullValue()
    {
        return [
            'value' => null,
            'label' => Translator::translate("Select"),
        ];
    }

    /**
     * @return array
     */
    private function mimeTypes()
    {
        $data = [$this->nullValue()];
        $mimeTypes = get_allowed_mime_types();
        $mimeTypes[] = "All formats";

        usort($mimeTypes, function ($a, $b){
            return strtolower($a) <=> strtolower($b);
        });

        foreach ($mimeTypes as $mimeType){
            $data[]  = [
                'value' => $mimeType,
                'label' => $mimeType,
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function pageTemplates()
    {
        $data = [$this->nullValue()];
        $templates = wp_get_theme()->get_page_templates();

        foreach ($templates as $file => $template){
            $data[]  = [
                'value' => $file,
                'label' => $template,
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function categories()
    {
        $data = [$this->nullValue()];
        $categories = get_categories([
            "orderby" => "name",
            "hide_empty" => false
        ]);

        usort($categories, function (\WP_Term $a, \WP_Term $b){
            return strtolower($a->name) <=> strtolower($b->name);
        });

        foreach ($categories as $category){
            $data[]  = [
                'value' => $category->term_id,
                'label' => $category->name,
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function allUsers()
    {
        return [
            $this->nullValue(),
            [
                'value' => null,
                'label' => Translator::translate('User'),
            ]
        ];
    }

    /**
     * @return array
     */
    private function users()
    {
        $data = [$this->nullValue()];
        $users = get_users([
            'fields' => [
                'ID',
                'display_name',
            ]
        ]);

        usort($users, function (\stdClass $a, \stdClass $b){
            return strtolower($a->display_name) <=> strtolower($b->display_name);
        });

        /** @var \WP_User $user */
        foreach ($users as $user){
            $data[]  = [
                'value' => $user->id,
                'label' => $user->display_name,
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function metaGroups()
    {
        $data = [$this->nullValue()];

        $metaGroups = MetaRepository::get([
            'lazy' => true
        ]);

        foreach ($metaGroups as $metaGroup){
            $data[]  = [
                'value' => $metaGroup->getId(),
                'label' => $metaGroup->getUIName(),
            ];
        }

        return $data;
    }

    /**
     * Get all parent fields slugs
     *
     * @return array
     * @throws \Exception
     */
    private function metaFieldSlugs()
    {
        $data = [$this->nullValue()];

        $fields = MetaRepository::getFieldDBNames();

        foreach ($fields as $field){
            $data[]  = [
                'value' => $field['id'],
                'label' => $field['box_name']."_".$field['field_name'],
            ];
        }

        return $data;
    }


    /**
     * @return array
     * @throws \Exception
     */
    private function optionPages()
    {
        $data = [$this->nullValue()];

        $optionPages = OptionPageRepository::get([
            'sortedBy' => 'menu_slug'
        ]);

        foreach ($optionPages as $optionPage){
            $data[]  = [
                'id' => $optionPage->getId(),
                'value' => $optionPage->getMenuSlug(),
                'label' => $optionPage->getMenuTitle(),
            ];

            foreach ($optionPage->getChildren() as $childOptionPage){
                $data[]  = [
                    'id' => $childOptionPage->getId(),
                    'value' => $childOptionPage->getMenuSlug(),
                    'label' => $childOptionPage->getMenuTitle(),
                ];
            }
        }

        return $data;
    }

    private function blocks()
    {
        $data = [$this->nullValue()];

        $blocks = DynamicBlockRepository::get([]);

        foreach ($blocks as $block){
            $data[]  = [
                'value' => $block->getId(),
                'label' => $block->getName(),
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function forms()
    {
        $data = [$this->nullValue()];

        $forms = FormRepository::get([
            'lazy' => true
        ]);

        foreach ($forms as $form){
            $data[]  = [
                'value' => $form->getId(),
                'label' => $form->getName(),
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function dataSets()
    {
        $data = [$this->nullValue()];
        $datasets = DatasetRepository::get([
            'lazy' => true
        ]);

        foreach ($datasets as $dataset){
            $data[]  = [
                'value' => $dataset->getId(),
                'label' => $dataset->getName(),
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function productData()
    {
        $data = [$this->nullValue()];
        $data = apply_filters( 'add_woo_product_data_to_acpt_list', $data );

        return $data;
    }

    /**
     * @return array
     */
    private function taxonomies()
    {
        try {
            $data = [
                MetaTypes::TAXONOMY => [$this->nullValue()],
                BelongsTo::POST_TAX => [$this->nullValue()],
                BelongsTo::TERM_ID => [$this->nullValue()],
            ];

            $taxonomies = get_taxonomies(['show_ui' => true], 'objects');

            // Add ACPT taxonomies
            $acptTaxonomis = TaxonomyRepository::get([]);

            foreach ($acptTaxonomis as $acptTaxonomy){
                if(!taxonomy_exists($acptTaxonomy->getSlug())){
                    $tax = new \WP_Taxonomy($acptTaxonomy->getSlug(), 'page');
                    $tax->label = $acptTaxonomy->getSlug();
                    $taxonomies[] = $tax;
                }
            }

            usort($taxonomies, function (\WP_Taxonomy $a, \WP_Taxonomy $b){
                return strtolower($a->label) <=> strtolower($b->label);
            });

            /** @var \WP_Taxonomy $taxonomy */
            foreach ($taxonomies as $taxonomy){
                $data[MetaTypes::TAXONOMY][]  = [
                    'value' => $taxonomy->name,
                    'label' => Terms::formatTaxonomyLabel($taxonomy),
                ];

                $terms = [];
                $queriedTerms = get_terms([
                    'taxonomy'   => $taxonomy->name,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'fields' => 'id=>name',
                ]);

                foreach ($queriedTerms as $termId => $queriedTerm){
                    if(is_string($queriedTerm)) {
                        $terms[] = [
                                'label' => $queriedTerm,
                                'value' => $termId,
                        ];
                    }
                }

                if(!empty($terms)){
                    $taxGroup = [
                        'label' => Terms::formatTaxonomyLabel($taxonomy),
                        'taxonomy' => $taxonomy->name,
                        'options' => $terms
                    ];

                    $data[BelongsTo::POST_TAX][] = $taxGroup;
                    $data[BelongsTo::TERM_ID][] = $taxGroup;
                }
            }

            return $data;
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return [
                MetaTypes::TAXONOMY => [$this->nullValue()],
                BelongsTo::POST_TAX => [$this->nullValue()],
                BelongsTo::TERM_ID => [$this->nullValue()],
            ];
        }
    }

    /**
     * @return array
     */
    private function postTypes()
    {
        try {
            $data = [
                MetaTypes::CUSTOM_POST_TYPE => [$this->nullValue()],
                BelongsTo::PARENT_POST_ID => [$this->nullValue()],
                BelongsTo::POST_ID => [$this->nullValue()],
            ];

            $customPostTypes = get_post_types([
                    'public' => true,
            ], 'objects');

            // Add other cpt to the list from integrations with third parts plugins
            $customPostTypes = apply_filters( 'add_cpt_to_acpt_list', $customPostTypes );

            // Add ACPT post types
            $acptCustomPostTypes = CustomPostTypeRepository::get([]);

            foreach ($acptCustomPostTypes as $acptCustomPostType){
                if(!post_type_exists($acptCustomPostType->getName())){

                    $postType = new \WP_Post_Type($acptCustomPostType->getName());
                    $postType->label = $acptCustomPostType->getPlural();
                    $customPostTypes[] = $postType;
                }
            }

            usort($customPostTypes, function (\WP_Post_Type $a, \WP_Post_Type $b){
                return strtolower($a->label) <=> strtolower($b->label);
            });

            /** @var \WP_Post_Type $customPostType */
            foreach ($customPostTypes as $customPostType){

                $toBeExcluded = [
                    'attachment'
                ];

                if(!in_array($customPostType->name, $toBeExcluded)){
                    $data[MetaTypes::CUSTOM_POST_TYPE][]  = [
                        'value' => $customPostType->name,
                        'label' => $customPostType->label,
                    ];

                    $posts = [];
                    $parentPosts = [];

                    $postsFromCache = Posts::getPostsByTypeFromCache($customPostType->name);

                    foreach ($postsFromCache as $p){

                        $id = $p->ID;
                        $title = $p->post_title;
                        $parent = $p->post_parent;

                        if($parent == 0){
                            $parentPosts[] = [
                                'label' => $title ." (#".$id.")",
                                'value' => $id,
                            ];
                        }

                        $posts[] = [
                            'label' => $title ." (#".$id.")",
                            'value' => $id,
                        ];
                    }

                    if(!empty($parentPosts)){
                        $data[BelongsTo::PARENT_POST_ID][] = [
                            'label' => $customPostType->name,
                            'options' => $parentPosts
                        ];
                    }

                    if(!empty($posts)){
                        $data[BelongsTo::POST_ID][] = [
                            'label' => $customPostType->name,
                            'options' => $posts
                        ];
                    }
                }
            }

            return $data;
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return [
                MetaTypes::CUSTOM_POST_TYPE => [$this->nullValue()],
                BelongsTo::PARENT_POST_ID => [$this->nullValue()],
                BelongsTo::POST_ID => [$this->nullValue()],
            ];
        }
    }
}