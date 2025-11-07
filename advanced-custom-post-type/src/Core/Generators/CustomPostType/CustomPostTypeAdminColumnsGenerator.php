<?php

namespace ACPT\Core\Generators\CustomPostType;

use ACPT\Constants\MetaTypes;
use ACPT\Core\CQRS\Command\SaveMetaFieldValueCommand;
use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Helper\Id;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\CustomPostType\CustomPostTypeModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Models\Taxonomy\TaxonomyModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\Wordpress\Nonce;
use ACPT\Utils\Wordpress\Translator;

class CustomPostTypeAdminColumnsGenerator extends AbstractGenerator
{
	/**
     * Add meta fields to the posts table
     *
	 * @param mixed $postTypeModel
	 *
	 * @throws \Exception
	 */
	public static function addColumns($postTypeModel)
	{
	    $postTypeName = ($postTypeModel instanceof CustomPostTypeModel) ? $postTypeModel->getName() : $postTypeModel;

		$manageEditAction = 'manage_edit-'.$postTypeName.'_columns';
		$manageEditSortAction = 'manage_edit-'.$postTypeName.'_sortable_columns';
		$customColumnsAction = 'manage_'.$postTypeName.'_posts_custom_column';
        $bulkActionsAction = 'bulk_actions-edit-'.$postTypeName;

		$metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
			'find' => $postTypeName,
            'clonedFields' => true
		]);

		$idFields = [];

		foreach ($metaGroups as $metaGroup){
            foreach ($metaGroup->getBoxes() as $metaBoxModel){
                foreach ($metaBoxModel->getFields() as $metaBoxFieldModel){
                    if($metaBoxFieldModel->getType() === MetaFieldModel::ID_TYPE){
                        $idFields[$metaBoxFieldModel->getDbName()] = $metaBoxFieldModel;
                    }
                }
            }
        }

		// add options to bulk actions
        add_filter($bulkActionsAction, function($bulk_actions) use ($idFields) {

            /**
             * @var string $label
             * @var MetaFieldModel $field
             */
            foreach ($idFields as $label => $field){
                $bulk_actions[$label] = "Generate ID for ".$field->getLabel(). " field";
            }

            return $bulk_actions;
        });

		// apply bulk actions
        add_action('init', function () use ($postTypeName, $idFields) {

            /**
             * @var string $label
             * @var MetaFieldModel $field
             */
            foreach ($idFields as $label => $field){
                if(isset($_GET['action']) and $_GET['action'] === $label and $_GET['post_type'] === $postTypeName) {
                    $postIds = $_GET['post'];
                    $paged = $_GET['paged'] ?? 1;
                    $perPage = (int)get_user_option("edit_".$postTypeName."_per_page");
                    $index = ($paged-1) * $perPage;

                    foreach ($postIds as $postId){

                        $strategy = $field->getAdvancedOption('id_strategy') ?? Id::UUID_V1;
                        $command = new SaveMetaFieldValueCommand($field, [
                                'post_id' => $postId,
                                'box_name' => $field->getBox()->getName(),
                                'field_name' => $field->getName(),
                                'value' => Id::generate($strategy, $index),
                        ]);

                        $command->execute();
                        $index++;
                    }
                }
            }
        });

		// add columns to show
		add_filter($manageEditAction, function($columns) use ($postTypeModel, $metaGroups) {

			// meta fields
			foreach($metaGroups as $metaGroup){
				foreach ($metaGroup->getBoxes() as $metaBoxModel){
					foreach ($metaBoxModel->getFields() as $metaBoxFieldModel){
						if ($metaBoxFieldModel->isShowInArchive()){
							$key = Strings::toDBFormat($metaBoxModel->getName()).'_'.Strings::toDBFormat($metaBoxFieldModel->getName());
							$value = Strings::toHumanReadableFormat($metaBoxFieldModel->getName());
                            $columns[$key] = $value;
						}
					}
				}
			}

			return $columns;
		});

		// display value on columns to show
		add_action($customColumnsAction, function($name) use ($postTypeModel, $metaGroups) {
			global $post;

			foreach ($metaGroups as $metaGroup){
				foreach ($metaGroup->getBoxes() as $metaBoxModel){
					foreach ($metaBoxModel->getFields() as $metaBoxFieldModel){
						if ($metaBoxFieldModel->isShowInArchive()){
							$boxName = Strings::toDBFormat($metaBoxModel->getName());
							$fieldName  = Strings::toDBFormat($metaBoxFieldModel->getName());
							$key = $boxName.'_'.$fieldName;

							if($key === $name){
								echo do_shortcode('[acpt admin_view="true" preview="true" pid="'.$post->ID.'" box="'.Strings::esc_attr($boxName).'" field="'.Strings::esc_attr($fieldName).'"]');
							}
						}
					}
				}
			}
		});

		// add sortable columns
		add_filter( $manageEditSortAction, function($columns) use ($postTypeModel, $metaGroups){
			foreach($metaGroups as $metaGroup){
				foreach ($metaGroup->getBoxes() as $metaBoxModel){
					foreach ($metaBoxModel->getFields() as $metaBoxFieldModel){
						if (
                            $metaBoxFieldModel->isShowInArchive() and
                            $metaBoxFieldModel->isFilterable() and
                            $metaBoxFieldModel->isFilterableInAdmin()
                        ){
							$key = Strings::toDBFormat($metaBoxModel->getName()).'_'.Strings::toDBFormat($metaBoxFieldModel->getName());
							$columns[$key] = $key;
						}
					}
				}
			}

			return $columns;
		} );

		// modify the main posts query
		add_action('pre_get_posts', function ($query) use ($postTypeModel, $metaGroups) {

            global $wpdb;

            if(!$query->is_main_query()) {
                return;
            }

		    // filter queries on back-end
			if(is_admin()) {
				$scr = get_current_screen();

				if($scr->base !== 'edit') {
					return;
				}

				//  modify tax_query (excluding WooCommerce product page)
				if(
                    $postTypeModel instanceof CustomPostTypeModel and
                    !$postTypeModel->isNative() and
                    !$postTypeModel->isWooCommerce()
                ){
					foreach ($postTypeModel->getTaxonomies() as $taxonomy) {
						if (!$taxonomy->isNative() and isset($_GET[$taxonomy->getSlug()]) && $_GET[$taxonomy->getSlug()] != 0) {

							$display = self::displayTaxonomy($postTypeModel, $taxonomy);

							if($display){
								$term = sanitize_text_field($_GET[$taxonomy->getSlug()]);
								$query->set('tax_query', [
									[
										'taxonomy' => $taxonomy->getSlug(),
										'field' => 'slug',
										'terms' => $term
									]
								]);
							}
						}
					}
                }

				// sorting and filtering posts by meta fields in backend
                $metaQuery = [];

				foreach ($metaGroups as $metaGroup){
					foreach ($metaGroup->getBoxes() as $metaBoxModel){
						foreach ($metaBoxModel->getFields() as $metaBoxFieldModel){
							if (
                                $metaBoxFieldModel->isShowInArchive() and
                                $metaBoxFieldModel->isFilterable() and
                                $metaBoxFieldModel->isFilterableInAdmin()
                            ){
								$metaKey = Strings::toDBFormat($metaBoxModel->getName()).'_'.Strings::toDBFormat($metaBoxFieldModel->getName());

								// 1. filter
                                if(isset($_GET[$metaKey]) and !empty($_GET[$metaKey])){
                                    $metaQuery[] = [
                                        'key' => $metaKey,
                                        'value' => esc_html($_GET[$metaKey])
                                    ];
                                }

								// 2. order
                                $orderBy = $query->get('orderby');
                                $order = $query->get('order') ?? 'asc'; // can be: sort/asc

								if ($orderBy === $metaKey) {
                                    $query->set('meta_key', $metaKey);
                                    $query->set('orderby', 'meta_value');
                                    $query->set('order', $order);
								}
							}
						}
					}
				}

                if(!empty($metaQuery)){
                    $query->set('meta_query', $metaQuery);
                }

			} else {
                // alter frontend URL prefixes
                if(!$postTypeModel instanceof CustomPostTypeModel){
                    return;
                }

                $frontEndUrlPrefix = $postTypeModel->getSettings()['front_url_prefix'] ?? null;

                if($frontEndUrlPrefix === null){
                    return;
                }

                if($frontEndUrlPrefix !== ""){

                    $urlArr = explode("/", $_SERVER['REQUEST_URI']);
                    $frontEndUrlPrefixArr = explode("/", $frontEndUrlPrefix);
                    $check = array_values(array_intersect($urlArr, $frontEndUrlPrefixArr));

                    if($frontEndUrlPrefixArr == $check){
                        $postName = $query->get("attachment");

                        $postType = $wpdb->get_var(
                            $wpdb->prepare(
                                'SELECT post_type FROM ' . $wpdb->posts . ' WHERE post_name = %s LIMIT 1',
                                $postName
                            )
                        );

                        if($postTypeModel->getName() === $postType){
                            $query->set($postTypeModel->getName(), $postName);
                            $query->set('post_type', $postType);
                            $query->is_single = true;
                            $query->is_page = false;
                        }
                    }

                } else {
                    // remove frontend URL prefix
                    if( count($query->query) === 2 and isset($query->query['page']) and !empty($query->query['name']) ){
                        $postTypes = get_post_types([
                            'public' => true,
                            '_builtin' => false,
                        ]);

                        $query->set('post_type', array_merge(['page', 'post'], $postTypes));
                    }
                }
            }
		});

		// add filterable columns
		add_action( 'restrict_manage_posts', function($post_type) use ($postTypeModel, $postTypeName, $metaGroups) {

			if($post_type !== $postTypeName){
				return;
			}

			if($postTypeModel instanceof CustomPostTypeModel and !$postTypeModel->isNative()){
				foreach ($postTypeModel->getTaxonomies() as $taxonomy) {

					$display = self::displayTaxonomy($postTypeModel, $taxonomy);

					if(!$taxonomy->isNative() and $display){
						$metaKey = $taxonomy->getSlug();
						$selected = '';
						if ( isset($_REQUEST[$metaKey]) ) {
							$selected = $_REQUEST[$metaKey];
						}

						echo '<select id="'.$metaKey.'" name="'.$metaKey.'">';
						echo '<option value="0">' . Translator::translate('Select') . ' ' . $taxonomy->getSingular() .'</option>';

						$terms = get_terms([
							'taxonomy'   => $taxonomy->getSlug(),
							'hide_empty' => false,
						]);

						foreach($terms as $term){
							$isSelected = ($term->slug == $selected) ? ' selected="selected"' : '';
							echo '<option value="'.$term->slug.'"'.$isSelected.'>' . $term->name . '</option>';
						}

						echo '</select>';
					}
				}
			}

			foreach($metaGroups as $metaGroup){
				foreach ($metaGroup->getBoxes() as $metaBoxModel){
					foreach ($metaBoxModel->getFields() as $metaBoxFieldModel){
						if (
                            $metaBoxFieldModel->isShowInArchive() and
                            $metaBoxFieldModel->isFilterable() and
                            $metaBoxFieldModel->isFilterableInAdmin()
                        ){

							//get unique values of the meta field to filer by.
							$metaKey = Strings::toDBFormat($metaBoxModel->getName()).'_'.Strings::toDBFormat($metaBoxFieldModel->getName());
							$metaLabel = $metaBoxFieldModel->getLabelOrName();

							$selected = '';
							if ( isset($_REQUEST[$metaKey]) ) {
								$selected = $_REQUEST[$metaKey];
							}

							global $wpdb;

							$results = $wpdb->get_results(
								$wpdb->prepare( "
                                    SELECT DISTINCT pm.meta_value, pm.meta_id FROM {$wpdb->postmeta} pm
                                    LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                                    WHERE pm.meta_key = '%s' 
                                    AND p.post_status IN ('publish', 'draft')
                                    ORDER BY pm.meta_value",
									$metaKey
								)
							);

							echo '<select id="'.$metaKey.'" name="'.$metaKey.'">';
							echo '<option value="0">' . Translator::translate('Select') . ' ' . $metaLabel .'</option>';

							$unique = [];

							foreach($results as $result){
								if(!in_array($result->meta_value, $unique)){
                                    $isSelected = ($result->meta_value == $selected) ? ' selected="selected"' : '';
									$unique[] = $result->meta_value;
									echo '<option value="'.$result->meta_value.'"'.$isSelected.'>' . $result->meta_value . '</option>';
								}
							}

							echo '</select>';
						}
					}
				}
			}
		});

		// quick edit
        $quickEditTags = [];
		add_action( 'quick_edit_custom_box', function($columnName) use ($postTypeModel, $postTypeName, $metaGroups, &$quickEditTags) {
			global $post;

			if($post !== null and $post->post_type === $postTypeName){
                self::generateQuickEditFields($metaGroups, $columnName, $post->ID, $quickEditTags);
            }
        } );

		// bulk edit
        $quickBuckEditTags = [];
        add_action( 'bulk_edit_custom_box', function ($columnName, $postType) use ($postTypeModel, $postTypeName, $metaGroups, &$quickBuckEditTags) {
            if($postType === $postTypeName){
                self::generateQuickEditFields($metaGroups, $columnName, null, $quickBuckEditTags);
            }
        }, 10, 2 );
	}

    /**
     * @param MetaGroupModel[] $metaGroups
     * @param string $columnName
     * @param null $postId
     * @param array $quickEditTags
     */
	private static function generateQuickEditFields($metaGroups, $columnName, $postId = null, &$quickEditTags = [])
    {
        foreach ($metaGroups as $metaGroup){
            foreach ($metaGroup->getBoxes() as $metaBoxModel){
                foreach ($metaBoxModel->getFields() as $metaBoxFieldModel){
                    if (
                        $metaBoxFieldModel->isForQuickEdit() and
                        $metaBoxFieldModel->canBeQuickEdited()
                    ){
                        $key = Strings::toDBFormat($metaBoxModel->getName()).'_'.Strings::toDBFormat($metaBoxFieldModel->getName());
                        $key = esc_html($key);
                        $label = Strings::toHumanReadableFormat($metaBoxFieldModel->getName());
                        $value = "";

                        if(!empty($postId)){
                            $value = Meta::fetch( $postId, MetaTypes::CUSTOM_POST_TYPE, $key, true );
                        }

                        // check if field is already rendered
                        if( !in_array($key, $quickEditTags) ){
                            Nonce::field();
                            echo self::generateQuickEditField($key, $label, $value, $metaBoxFieldModel);
                            $quickEditTags[] = $key;
                        }
                    }
                }
            }
        }
    }

	/**
     * This function exclude WooCommerce default taxonomies (like `product_brand`) from back-end search
     *
	 * @param CustomPostTypeModel $postTypeModel
	 * @param TaxonomyModel $taxonomy
	 *
	 * @return bool
	 */
	private static function displayTaxonomy(CustomPostTypeModel $postTypeModel, TaxonomyModel $taxonomy): bool
	{
		return ($postTypeModel->isWooCommerce() and $taxonomy->isWooCommerceNative()) ? false : true;
	}
}