<?php

namespace ACPT\Integrations\WPGraphQL;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\CustomPostType\CustomPostTypeModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Models\WooCommerce\WooCommerceProductDataFieldModel;
use ACPT\Core\Models\WooCommerce\WooCommerceProductDataModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\DynamicBlockRepository;
use ACPT\Core\Repository\FormRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Integrations\AbstractIntegration;
use ACPT\Utils\Data\Meta;

class ACPT_WPGraphQL extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "wp-graphql";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
    	$isActive = is_plugin_active( 'wp-graphql/wp-graphql.php' );

    	if(!$isActive){
    		return false;
	    }

        return $isActive;
    }

	/**
	 * @inheritDoc
	 * @throws \Exception
	 */
    protected function runIntegration()
    {
        add_action( 'graphql_register_types', function()  {

            $this->registerAcptTypes();

            // Post Types
            if(ACPT_ENABLE_CPT and ACPT_ENABLE_META){
                $postTypes = CustomPostTypeRepository::get([]);

                /** @var CustomPostTypeModel $postType */
                foreach ($postTypes as $postType){
                    $this->registerAcptCustomPostType($postType);
                }
            }

            // Option pages
            if(ACPT_ENABLE_PAGES and ACPT_ENABLE_META){
                $this->registerAcptOptionPages();
            }

            // Forms
            if(ACPT_ENABLE_FORMS){
                $this->registerAcptForms();
            }

            // Blocks
            if(ACPT_ENABLE_BLOCKS){
                $this->registerAcptBlocks();
            }
        });
    }

    /**
     * Register ACPT types and subtypes
     */
    private function registerAcptTypes()
    {
        // blocks
        register_graphql_object_type( 'AcptBlocks', [
            'description' => __( "ACPT blocks", ACPT_PLUGIN_NAME ),
            'fields' => [
                'blocks' => [
                    'type' => [ 'list_of' => 'AcptBlock' ],
                    'description' => __( 'List of all ACPT blocks', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        register_graphql_object_type( 'AcptBlock', [
            'description' => __( "Single ACPT dynamic block", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The form name', ACPT_PLUGIN_NAME ),
                ],
                'title' => [
                    'type' => 'String',
                    'description' => __( 'The form label', ACPT_PLUGIN_NAME ),
                ],
                'category' => [
                    'type' => 'String',
                    'description' => __( 'The form unique key', ACPT_PLUGIN_NAME ),
                ],
                'icon' => [
                    'type' => 'String',
                    'description' => __( 'The form action', ACPT_PLUGIN_NAME ),
                ],
                'css' => [
                    'type' => 'String',
                    'description' => __( 'The form action', ACPT_PLUGIN_NAME ),
                ],
                'callback' => [
                    'type' => 'String',
                    'description' => __( 'The form action', ACPT_PLUGIN_NAME ),
                ],
                'keywords' => [
                    'type' => 'String',
                    'description' => __( 'The form action', ACPT_PLUGIN_NAME ),
                ],
                'postTypes' => [
                    'type' => 'String',
                    'description' => __( 'The form action', ACPT_PLUGIN_NAME ),
                ],
                'controls' => [
                    'type' => [ 'list_of' => 'AcptBlockControl' ],
                    'description' => __( 'List of form meta data', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        register_graphql_object_type( 'AcptBlockControl', [
            'description' => __( "Single ACPT block control", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The control name', ACPT_PLUGIN_NAME ),
                ],
                'label' => [
                    'type' => 'String',
                    'description' => __( 'The control label', ACPT_PLUGIN_NAME ),
                ],
                'type' => [
                    'type' => 'String',
                    'description' => __( 'The control type', ACPT_PLUGIN_NAME ),
                ],
                'default' => [
                    'type' => 'String',
                    'description' => __( 'The control default value', ACPT_PLUGIN_NAME ),
                ],
                'settings' => [
                    'type' => [ 'list_of' => 'AcptGenericSetting' ],
                    'description' => __( 'List of control settings', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        // forms
        register_graphql_object_type( 'AcptForms', [
            'description' => __( "ACPT forms", ACPT_PLUGIN_NAME ),
            'fields' => [
                'forms' => [
                    'type' => [ 'list_of' => 'AcptForm' ],
                    'description' => __( 'List of all ACPT forms', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        register_graphql_object_type( 'AcptForm', [
            'description' => __( "Single ACPT form", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The form name', ACPT_PLUGIN_NAME ),
                ],
                'label' => [
                    'type' => 'String',
                    'description' => __( 'The form label', ACPT_PLUGIN_NAME ),
                ],
                'key' => [
                    'type' => 'String',
                    'description' => __( 'The form unique key', ACPT_PLUGIN_NAME ),
                ],
                'action' => [
                    'type' => 'String',
                    'description' => __( 'The form action', ACPT_PLUGIN_NAME ),
                ],
                'submissions' => [
                    'type' => [ 'list_of' => 'AcptFormSubmission' ],
                    'description' => __( 'List of form submissions', ACPT_PLUGIN_NAME ),
                ],
                'fields' => [
                    'type' => [ 'list_of' => 'AcptFormField' ],
                    'description' => __( 'List of form fields', ACPT_PLUGIN_NAME ),
                ],
                'meta' => [
                    'type' => [ 'list_of' => 'AcptFormMeta' ],
                    'description' => __( 'List of form meta data', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        register_graphql_object_type( 'AcptFormSubmission', [
                'description' => __( "Single ACPT form submission", ACPT_PLUGIN_NAME ),
                'fields' => [
                    'action' => [
                        'type' => 'String',
                        'description' => __( 'The form action', ACPT_PLUGIN_NAME ),
                    ],
                    'browser' => [
                        'type' => 'String',
                        'description' => __( 'The user browser', ACPT_PLUGIN_NAME ),
                    ],
                    'ip' => [
                        'type' => 'String',
                        'description' => __( 'The user IP', ACPT_PLUGIN_NAME ),
                    ],
                    'user' => [
                        'type' => 'String',
                        'description' => __( 'The user name', ACPT_PLUGIN_NAME ),
                    ],
                    'createdAt' => [
                        'type' => 'String',
                        'description' => __( 'The form submission date', ACPT_PLUGIN_NAME ),
                    ],
                    'data' => [
                        'type' => [ 'list_of' => 'AcptFormSubmissionDatum' ],
                        'description' => __( 'List of data', ACPT_PLUGIN_NAME ),
                    ],
                ],
        ] );

        register_graphql_object_type( 'AcptFormSubmissionDatum', [
            'description' => __( "Single ACPT form submission data", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The name', ACPT_PLUGIN_NAME ),
                ],
                'type' => [
                    'type' => 'String',
                    'description' => __( 'The type', ACPT_PLUGIN_NAME ),
                ],
                'value' => [
                    'type' => 'String',
                    'description' => __( 'The value', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        register_graphql_object_type( 'AcptFormField', [
            'description' => __( "Single ACPT form field", ACPT_PLUGIN_NAME ),
            'fields' => [
                'key' => [
                    'type' => 'String',
                    'description' => __( 'The page title', ACPT_PLUGIN_NAME ),
                ],
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The page title', ACPT_PLUGIN_NAME ),
                ],
                'group' => [
                    'type' => 'String',
                    'description' => __( 'The page title', ACPT_PLUGIN_NAME ),
                ],
                'type' => [
                    'type' => 'String',
                    'description' => __( 'The page title', ACPT_PLUGIN_NAME ),
                ],
                'extra' => [
                    'type' => [ 'list_of' => 'AcptGenericSetting' ],
                    'description' => __( 'List of extra elements', ACPT_PLUGIN_NAME ),
                ],
                'settings' => [
                    'type' => [ 'list_of' => 'AcptGenericSetting' ],
                    'description' => __( 'List of extra elements', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        register_graphql_object_type( 'AcptGenericSetting', [
                'description' => __( "Single ACPT form field setting element", ACPT_PLUGIN_NAME ),
                'fields' => [
                    'key' => [
                        'type' => 'String',
                        'description' => __( 'The key', ACPT_PLUGIN_NAME ),
                    ],
                    'value' => [
                        'type' => 'String',
                        'description' => __( 'The value', ACPT_PLUGIN_NAME ),
                    ],
                ],
        ] );

        register_graphql_object_type( 'AcptFormMeta', [
            'description' => __( "Single ACPT form metadata", ACPT_PLUGIN_NAME ),
            'fields' => [
                'key' => [
                    'type' => 'String',
                    'description' => __( 'The page title', ACPT_PLUGIN_NAME ),
                ],
                'value' => [
                    'type' => 'String',
                    'description' => __( 'The page title', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        // option pages
        register_graphql_object_type( 'AcptOptionPages', [
            'description' => __( "ACPT option pages", ACPT_PLUGIN_NAME ),
            'fields' => [
                'pages' => [
                    'type' => [ 'list_of' => 'AcptOptionPage' ],
                    'description' => __( 'List of all ACPT option pages', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        register_graphql_object_type( 'AcptOptionPage', [
            'description' => __( "Single ACPT option page", ACPT_PLUGIN_NAME ),
            'fields' => [
                'pageTitle' => [
                    'type' => 'String',
                    'description' => __( 'The page title', ACPT_PLUGIN_NAME ),
                ],
                'menuTitle' => [
                    'type' => 'String',
                    'description' => __( 'The menu title of page', ACPT_PLUGIN_NAME ),
                ],
                'capability' => [
                    'type' => 'String',
                    'description' => __( 'Page capabilities', ACPT_PLUGIN_NAME ),
                ],
                'menuSlug' => [
                    'type' => 'String',
                    'description' => __( 'The page slug', ACPT_PLUGIN_NAME ),
                ],
                'icon' => [
                    'type' => 'String',
                    'description' => __( 'The page icon', ACPT_PLUGIN_NAME ),
                ],
                'description' => [
                        'type' => 'String',
                        'description' => __( 'The page description', ACPT_PLUGIN_NAME ),
                ],
                'children' => [
                    'type' => [ 'list_of' => 'AcptOptionPage' ],
                    'description' => __( 'List of children pages', ACPT_PLUGIN_NAME ),
                ],
                'meta' => [
                    'type' => [ 'list_of' => 'AcptMetaGroup' ],
                    'description' => __( 'List of associated meta fields', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        // acpt (only used in posts)
        register_graphql_object_type( 'Acpt', [
            'description' => __( "ACPT data", ACPT_PLUGIN_NAME ),
            'fields' => [
                'meta' => [
                    'type' => [ 'list_of' => 'AcptMetaGroup' ],
                    'description' => __( 'List of all meta', ACPT_PLUGIN_NAME ),
                ],
                'product_data' => [
                    'type' => [ 'list_of' => 'WooCommerceProductData' ],
                    'description' => __( 'List of all product data (only for WooCommerce product post type)', ACPT_PLUGIN_NAME ),
                ],
            ],
        ] );

        // meta group
        register_graphql_object_type( 'AcptMetaGroup', [
            'description' => __( "ACPT meta group", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The name of the meta group', ACPT_PLUGIN_NAME ),
                ],
                'label' => [
                    'type' => 'String',
                    'description' => __( 'The label of the meta group', ACPT_PLUGIN_NAME ),
                ],
                'display' => [
                    'type' => 'String',
                    'description' => __( 'The display rules of the meta group', ACPT_PLUGIN_NAME ),
                ],
                'context' => [
                    'type' => 'String',
                    'description' => __( 'The context rules of the meta group', ACPT_PLUGIN_NAME ),
                ],
                'priority' => [
                    'type' => 'String',
                    'description' => __( 'The priority rules of the meta group', ACPT_PLUGIN_NAME ),
                ],
                'meta_boxes' => [
                    'type' => [ 'list_of' => 'AcptMetaBox' ]
                ],
            ],
        ]);

        // meta box
        register_graphql_object_type( 'AcptMetaBox', [
            'description' => __( "ACPT meta box", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The name of the meta box', ACPT_PLUGIN_NAME ),
                ],
                'label' => [
                    'type' => 'String',
                    'description' => __( 'The name of the meta box', ACPT_PLUGIN_NAME ),
                ],
                'meta_fields' => [
                    'type' => [ 'list_of' => 'AcptMetaField' ]
                ],
            ],
        ]);

        // meta field
        register_graphql_object_type( 'AcptMetaField', [
            'description' => __( "ACPT meta field", ACPT_PLUGIN_NAME ),
            'fields' => [
                'key' => [
                    'type' => 'String',
                    'description' => __( 'The key of the meta field', ACPT_PLUGIN_NAME ),
                ],
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The name of the meta field', ACPT_PLUGIN_NAME ),
                ],
                'type' => [
                    'type' => 'String',
                    'description' => __( 'The type of the meta field', ACPT_PLUGIN_NAME ),
                ],
                'values' => [
                    'type' => [ 'list_of' => 'String' ],
                    'description' => __( 'The value of the meta field', ACPT_PLUGIN_NAME ),
                ],
            ],
        ]);

        // wc product data
        register_graphql_object_type( 'WooCommerceProductData', [
            'description' => __( "WooCommerce product data", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The name of the meta box', ACPT_PLUGIN_NAME ),
                ],
                'fields' => [
                    'type' => [ 'list_of' => 'WooCommerceProductDataField' ]
                ],
            ],
        ]);

        // wc product data field
        register_graphql_object_type( 'WooCommerceProductDataField', [
            'description' => __( "WooCommerce product data field", ACPT_PLUGIN_NAME ),
            'fields' => [
                'name' => [
                    'type' => 'String',
                    'description' => __( 'The name of the meta field', ACPT_PLUGIN_NAME ),
                ],
                'type' => [
                    'type' => 'String',
                    'description' => __( 'The type of the meta field', ACPT_PLUGIN_NAME ),
                ],
                'values' => [
                    'type' => [ 'list_of' => 'String' ],
                    'description' => __( 'The value of the meta field', ACPT_PLUGIN_NAME ),
                ],
            ],
        ]);

        // query object
        register_graphql_input_type( 'AcptQuery', [
            'description' => __( "Query object", ACPT_PLUGIN_NAME ),
            'fields' => [
                'meta_query' => [
                    'type' => 'AcptMetaQuery',
                    'description' => __( 'Meta query', ACPT_PLUGIN_NAME ),
                ],
            ]
        ]);

        // meta_query
        register_graphql_input_type( 'AcptMetaQuery', [
            'description' => __( "Meta query object", ACPT_PLUGIN_NAME ),
            'fields' => [
                'relation' => [
                    'type' => 'String',
                    'description' => __( 'Meta query relation', ACPT_PLUGIN_NAME ),
                ],
                'elements' => [
                    'type' => [ 'list_of' => 'AcptMetaQueryElement' ],
                    'description' => __( 'Meta query element object', ACPT_PLUGIN_NAME ),
                ],
            ],
        ]);

        register_graphql_enum_type(
            'MetaCompareOperatorEnum',
            [
                'description' => __( 'Meta query comparison operator.', ACPT_PLUGIN_NAME ),
                'values' => [
                    'Equal'  => [
                        'name'        => 'Equal',
                        'value'       => '=',
                        'description' => __( 'Queries meta keys that equal a meta value', ACPT_PLUGIN_NAME ),
                    ],
                    'Not_equal'  => [
                        'name'        => 'Not_equal',
                        'value'       => '!=',
                        'description' => __( 'Queries meta keys that are NOT equal to a meta value', ACPT_PLUGIN_NAME ),
                    ],
                    'Greater_than' => [
                        'name'        => 'Greater_than',
                        'value'       => '>',
                        'description' => __( ' Queries meta keys that are greater than the meta value', ACPT_PLUGIN_NAME ),
                    ],
                    'Greater_than_equal' => [
                        'name'        => 'Greater_than_equal',
                        'value'       => '>=',
                        'description' => static function () {
                            return __( '’ Queries meta keys that are greater than & equal to the meta value', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'Less_than' => [
                        'name'        => 'Less_than',
                        'value'       => '<',
                        'description' => static function () {
                            return __( 'Queries meta keys that are less than the meta value', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'Less_than_equal' => [
                        'name'        => 'Less_than_equal',
                        'value'       => '<=',
                        'description' => static function () {
                            return __( 'Queries meta keys that are less than & equal to the meta value', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'LIKE' => [
                        'name'        => 'LIKE',
                        'value'       => 'LIKE',
                        'description' => static function () {
                            return __( 'Queries meta keys that contain a word/phrase (for example querying “red” would match the phrases “Red”, “looksred”, and “redstyle”)', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'NOT_LIKE' => [
                        'name'        => 'NOT_LIKE',
                        'value'       => 'NOT LIKE',
                        'description' => static function () {
                            return __( 'The opposite of above', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'IN' => [
                        'name'        => 'IN',
                        'value'       => 'IN',
                        'description' => static function () {
                            return __( 'Queries meta keys where the value exists in an array', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'NOT_IN' => [
                        'name'        => 'NOT_IN',
                        'value'       => 'NOT IN',
                        'description' => static function () {
                            return __( 'Queries meta keys where the value exists not in an array', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'BETWEEN' => [
                        'name'        => 'BETWEEN',
                        'value'       => 'BETWEEN',
                        'description' => static function () {
                            return __( 'Queries meta keys where the value is between two numbers', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'NOT_BETWEEN' => [
                        'name'        => 'NOT_BETWEEN',
                        'value'       => 'NOT_BETWEEN',
                        'description' => static function () {
                            return __( 'Queries meta keys where the value is not between two numbers', ACPT_PLUGIN_NAME );
                        },
                    ],
//                    'EXISTS'          => [
//                        'name'        => 'EXISTS',
//                        'value'       => 'EXISTS',
//                        'description' => static function () {
//                            return __( 'Queries meta keys where the value exists at all', ACPT_PLUGIN_NAME );
//                        },
//                    ],
//                    'NOT_EXISTS' => [
//                        'name'        => 'NOT_EXISTS',
//                        'value'       => 'NOT_EXISTS',
//                        'description' => static function () {
//                            return __( 'Queries meta keys where the value doesn’t exist at all', ACPT_PLUGIN_NAME );
//                        },
//                    ],
                    'REGEXP' => [
                        'name'        => 'REGEXP',
                        'value'       => 'REGEXP',
                        'description' => static function () {
                            return __( 'Queries meta keys based on a regular expression', ACPT_PLUGIN_NAME );
                        },
                    ],
                    'NOT_REGEXP' => [
                        'name'        => 'NOT_REGEXP',
                        'value'       => 'NOT REGEXP',
                        'description' => static function () {
                            return __( 'Opposite of above REGEXP', ACPT_PLUGIN_NAME );
                        },
                    ]
                ]
            ]
        );

        // meta_query element
        register_graphql_input_type( 'AcptMetaQueryElement', [
            'description' => __( "Meta query element object", ACPT_PLUGIN_NAME ),
            'fields' => [
                'type' => [
                    'type' => 'String',
                    'description' => __( 'Meta query element type', ACPT_PLUGIN_NAME ),
                ],
                'key' => [
                    'type' => 'String',
                    'description' => __( 'Meta query element key', ACPT_PLUGIN_NAME ),
                ],
                'value' => [
                    'type' => 'String',
                    'description' => __( 'Meta query element value', ACPT_PLUGIN_NAME ),
                ],
                'value_num'  => [
                    'type' => 'Integer',
                    'description' => __( 'Meta query element value (numeric)', ACPT_PLUGIN_NAME ),
                ],
                'compare' => [
                    'type' => 'MetaCompareOperatorEnum',
                    'description' => __( 'Meta query element compare operator', ACPT_PLUGIN_NAME ),
                ],
            ],
        ]);
    }

    /**
     * Register meta fields for custom post types
     *
     * @param CustomPostTypeModel $postType
     */
    private function registerAcptCustomPostType(CustomPostTypeModel $postType)
    {
        try {
            $this->saveSettingsForNativePosts($postType);
            $settings = $postType->getSettings();

            if(isset($settings['show_in_graphql']) and $settings['show_in_graphql'] === true){

                $singleName = $settings["graphql_single_name"];
                $pluralName = $settings["graphql_plural_name"];

                register_graphql_field('RootQueryTo'.ucfirst($singleName).'ConnectionWhereArgs',
                        'query', [
                                'type' => 'AcptQuery',
                                'description' => __('The meta query object to filter by', ACPT_PLUGIN_NAME),
                        ]
                );

                $metaFields = $this->acptMetaFieldSettings($postType);

                register_graphql_field( $singleName, 'acpt', $metaFields);
                register_graphql_field( $pluralName, 'acpt', $metaFields);

                add_filter('graphql_post_object_connection_query_args', function ($query_args, $source, $args, $context, $info) {

                    $query = $args['where']['query'];

                    if (isset($query)) {
                        $query_args['meta_query'] = $query;
                    }

                    return $query_args;
                }, 10, 5);
            }
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * @param CustomPostTypeModel $postType
     *
     * @throws \Exception
     */
    private function saveSettingsForNativePosts(CustomPostTypeModel $postType)
    {
        if($postType->isNative() and !isset($settings['show_in_graphql'])){

            $settings['show_in_graphql'] = true;
            $settings['graphql_single_name'] = strtolower($postType->getSingular());
            $settings['graphql_plural_name'] = strtolower($postType->getPlural());
            $postType->modifySettings($settings);

            CustomPostTypeRepository::save($postType);
        }
    }

    /**
     * ACPT meta field settings
     *
     * @param CustomPostTypeModel $postType
     * @see https://www.wpgraphql.com/2020/03/11/registering-graphql-fields-with-arguments
     *
     * @return array
     */
    private function acptMetaFieldSettings(CustomPostTypeModel $postType)
    {
        return [
            'type' => 'Acpt',
            'resolve' => function( $post, $args, $context, $info ) use ($postType) {

                $postId = (int)$post->databaseId;

                $meta = [];
                $meta['meta'] = [];
                $meta['product_data'] = [];

                // meta groups
                $metaGroups = MetaRepository::get([
	                'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
	                'find' => $postType->getName(),
                ]);

                $meta['meta'] = $this->fetchMetaGroups($metaGroups, MetaTypes::CUSTOM_POST_TYPE, $postId);

                // WooCommerce
                if($postType->isWooCommerce()){

                    /** @var WooCommerceProductDataModel $productDatum */
                    foreach ($postType->getWoocommerceProductData() as $productDatum){

                        $productDatumArray = [];
                        $productDatumArray['name'] = $productDatum->getName();
                        $productDatumArray['fields'] = [];

                        /** @var WooCommerceProductDataFieldModel $field */
                        foreach ($productDatum->getFields() as $field){

                            $listTypes = [
                                WooCommerceProductDataFieldModel::SELECT_TYPE,
                                WooCommerceProductDataFieldModel::RADIO_TYPE,
                            ];

                            $key = $field->getDbName();
                            $values = (in_array($field->getType(), $listTypes)) ? Meta::fetch($postId, MetaTypes::CUSTOM_POST_TYPE, $key, true) : [Meta::fetch($postId, MetaTypes::CUSTOM_POST_TYPE, $key, true)];

                            $productDatumArray['fields'][] = [
                                'key' => $field->getDBName(),
                                'name' => $field->getName(),
                                'type' => $field->getType(),
                                'values' => (!empty($values)) ? $values : [],
                            ];
                        }

                        $meta['product_data'][] = $productDatumArray;
                    }
                }

                return $meta;
            },
        ];
    }

    /**
     * Register option pages
     */
    private function registerAcptOptionPages()
    {
        try {
            register_graphql_field( 'RootQuery', 'optionPages', [
                'type' => "AcptOptionPages",
                'description' => __( 'ACPT option pages', ACPT_PLUGIN_NAME ),
                'args' => [
                    "pageTitle" => [
                        'type' => "String"
                    ],
                    "menuTitle" => [
                        'type' => "String"
                    ],
                    "menuSlug" => [
                        'type' => "String"
                    ],
                    "hasChildren" => [
                        'type' => "Boolean"
                    ],
                    'meta' => [
                        'type' => 'AcptQuery',
                        'description' => __('The meta query object to filter by', ACPT_PLUGIN_NAME),
                    ]
                ],
                'resolve' => function( $root, $args, $context, $info ) {

                    $pages = [];

                    foreach (OptionPageRepository::get([]) as $pageModel){

                        $children = [];

                        foreach ($pageModel->getChildren() as $childModel){

                            $metaGroups = MetaRepository::get([
                                'belongsTo' => MetaTypes::OPTION_PAGE,
                                'find' => $childModel->getMenuSlug(),
                            ]);

                            $meta = $this->fetchMetaGroups($metaGroups, MetaTypes::OPTION_PAGE, $childModel->getMenuSlug());

                            $children[] = [
                                "pageTitle" => $childModel->getPageTitle(),
                                "menuTitle" => $childModel->getMenuTitle(),
                                "capability" => $childModel->getCapability(),
                                "menuSlug" => $childModel->getMenuSlug(),
                                "icon" => $childModel->getIcon(),
                                "description" => $childModel->getDescription(),
                                "meta" => $meta,
                            ];
                        }

                        $metaGroups = MetaRepository::get([
                            'belongsTo' => MetaTypes::OPTION_PAGE,
                            'find' => $pageModel->getMenuSlug(),
                        ]);

                        $meta = $this->fetchMetaGroups($metaGroups, MetaTypes::OPTION_PAGE, $pageModel->getMenuSlug());

                        $pages[] = [
                            "pageTitle" => $pageModel->getPageTitle(),
                            "menuTitle" => $pageModel->getMenuTitle(),
                            "capability" => $pageModel->getCapability(),
                            "menuSlug" => $pageModel->getMenuSlug(),
                            "icon" => $pageModel->getIcon(),
                            "description" => $pageModel->getDescription(),
                            "children" => $children,
                            "meta" => $meta,
                        ];
                    }

                    // apply filters
                    $pages = array_filter($pages, function (array $page) use($args) {
                        return $this->applyArgsFilterToOptionPages($page, $args);
                    });

                    return [
                        "pages" => $pages
                    ];
                }
            ] );

        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * @param $page
     * @param $args
     *
     * @return bool
     */
    private function applyArgsFilterToOptionPages( $page, $args)
    {
        $queriedPageTitle = isset($args['pageTitle']) ? $args['pageTitle'] : null;
        $queriedMenuTitle = isset($args['menuTitle']) ? $args['menuTitle'] : null;
        $queriedMenuSlug = isset($args['menuSlug']) ? $args['menuSlug'] : null;
        $queriedHasChildren = isset($args['hasChildren']) ? $args['hasChildren'] : null;
        $query = (isset($args['meta']) and isset($args['meta']['meta_query'])) ? $args['meta']['meta_query'] : [];

        $results = [];

        if($queriedPageTitle){
            $results[] = Strings::contains($queriedPageTitle, $page['pageTitle']);
        }

        if($queriedMenuTitle){
            $results[] = Strings::contains($queriedMenuTitle, $page['menuTitle']);
        }

        if($queriedMenuSlug){
            $results[] = Strings::contains($queriedMenuSlug, $page['menuSlug']);
        }

        if($queriedHasChildren === false){
            $results[] = empty($page['children']);
        } elseif($queriedHasChildren === true){
            $results[] = !empty($page['children']);
        }

        if(!empty($query) and !empty($page['meta'])){
            foreach ($page['meta'] as $group){
                foreach ($group['meta_boxes'] as $box){
                    foreach ($box['meta_fields'] as $field){
                        $values = $field['values'];

                        if(isset($query['elements']) and !empty($query['elements']) and !empty($values)){
                            foreach ($query['elements'] as $element){
                                $key = $element['key'] ?? null;
                                $compare = $element['compare'] ?? "=";
                                $value = $element['value'] ?? null;
                                $value_num = isset($element['value_num']) ? (int)$element['value_num'] : null;

                                if($key === $field['key']){
                                    foreach ($values as $v){
                                        $results[] = Strings::comparison($v, $value, $compare);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($page['children'] as $child){
            $results[] = $this->applyArgsFilterToOptionPages($child, $args);
        }

        return !in_array(false, $results);
    }

    /**
     * @param MetaGroupModel[] $metaGroups
     * @param                  $belongsTo
     * @param                  $find
     *
     * @return array
     */
    private function fetchMetaGroups($metaGroups, $belongsTo, $find)
    {
        $metaGroupsArray = [];

        foreach ($metaGroups as $group){

            $metaGroupArray = [];
            $metaGroupArray['name'] = $group->getName();
            $metaGroupArray['label'] = $group->getLabel();
            $metaGroupArray['display'] = $group->getDisplay();
            $metaGroupArray['context'] = $group->getContext();
            $metaGroupArray['priority'] = $group->getPriority();

            foreach ($group->getBoxes() as $metaBox){

                $metaBoxArray = [];
                $metaBoxArray['name'] = $metaBox->getName();
                $metaBoxArray['label'] = $metaBox->getLabel();
                $metaBoxArray['meta_fields'] = [];

                foreach ($metaBox->getFields() as $field){

                    $listTypes = [
                            MetaFieldModel::GALLERY_TYPE,
                            MetaFieldModel::LIST_TYPE,
                            MetaFieldModel::SELECT_MULTI_TYPE,
                    ];

                    $key = "";

                    if($belongsTo === MetaTypes::OPTION_PAGE){
                        $key .= $find."_";
                    }

                    $key .= Strings::toDBFormat($metaBox->getName()) . '_' . Strings::toDBFormat($field->getName());

                    $values = (in_array($field->getType(), $listTypes)) ? Meta::fetch($find, $belongsTo, $key, true) : [Meta::fetch($find, $belongsTo, $key, true)];

                    $metaBoxArray['meta_fields'][] = [
                        'key' => $field->getDBName(),
                        'name' => $field->getName(),
                        'type' => $field->getType(),
                        'values' => (!empty($values)) ? $values : [],
                    ];
                }

                $metaGroupArray['meta_boxes'][] = $metaBoxArray;
            }

            $metaGroupsArray[] = $metaGroupArray;
        }

        return $metaGroupsArray;
    }

    /**
     * Register forms
     */
    private function registerAcptForms()
    {
        try {
            register_graphql_field( 'RootQuery', 'forms', [
                    'type' => "AcptForms",
                    'description' => __( 'ACPT forms', ACPT_PLUGIN_NAME ),
                    'args' => [
                        "name" => [
                            'type' => "String"
                        ],
                    ],
                    'resolve' => function( $root, $args, $context, $info ) {

                        $forms = [];

                        foreach (FormRepository::get([]) as $formModel){

                            $meta = [];
                            $fields = [];
                            $submissions = [];

                            foreach ($formModel->getMeta() as $m){
                                $meta[] = [
                                    'key' => $m->getKey(),
                                    'value' => $m->getValue(),
                                ];
                            }

                            foreach ($formModel->getFields() as $f){

                                $extra = [];
                                $settings = [];

                                foreach ($f->getExtra() as $key => $value){
                                    $extra[] = $this->formatGenericSettings($key, $value);;
                                }

                                foreach ($f->getSettings() as $key => $value){
                                    $settings[] =  $this->formatGenericSettings($key, $value);
                                }

                                $fields[] = [
                                    'key' => $f->getKey(),
                                    'name' => $f->getName(),
                                    'group' => $f->getGroup(),
                                    'type' => $f->getType(),
                                    'extra' => $extra,
                                    'settings' => $settings,
                                ];
                            }

                            foreach ($formModel->getSubmissions() as $s){

                                $data = [];

                                foreach ($s->getData() as $d){

                                    $data[] = [
                                        'name' => $d->getName(),
                                        'type' => $d->getType(),
                                        'value' => $d->getValueAsString(),
                                    ];
                                }

                                $submissions[] = [
                                    'action' => $s->getAction(),
                                    'browser' => (!empty($s->getBrowser())) ? $s->getBrowser()['name'] : null,
                                    'ip' => $s->getIp(),
                                    'user' => $s->getUser(),
                                    'createdAt' => $s->getCreatedAt()->format("Y-m-d H:i:s"),
                                    'data' => $data
                                ];
                            }

                            $forms[] = [
                                'key' => $formModel->getKey(),
                                'name' => $formModel->getName(),
                                'label' => $formModel->getLabel(),
                                'action' => $formModel->getAction(),
                                'meta' => $meta,
                                'fields' => $fields,
                                'submissions' => $submissions,
                            ];
                        }

                        // apply filters
                        $forms = array_filter($forms, function (array $form) use($args) {
                            $queriedName = isset($args['name']) ? $args['name'] : null;

                            if($queriedName){
                                return Strings::contains($queriedName, $form['name']);
                            }

                            return true;
                        });

                        return [
                            "forms" => $forms
                        ];
                    }
            ] );

        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * Register blocks
     */
    private function registerAcptBlocks()
    {
        try {
            register_graphql_field( 'RootQuery', 'blocks', [
                    'type' => "AcptBlocks",
                    'description' => __( 'ACPT dynamic blocks', ACPT_PLUGIN_NAME ),
                    'args' => [
                        "name" => [
                            'type' => "String"
                        ],
                    ],
                    'resolve' => function( $root, $args, $context, $info ) {

                        $blocks = [];

                        foreach (DynamicBlockRepository::get([]) as $blockModel){

                            $controls = [];

                            foreach ($blockModel->getControls() as $c){

                                $settings = [];

                                foreach ($c->getSettings() as $key => $value){
                                    $settings[] = $this->formatGenericSettings($key, $value);
                                }

                                $controls[] = [
                                    'name' => $c->getName(),
                                    'label' => $c->getLabel(),
                                    'type' => $c->getType(),
                                    'default' => $c->getDefault(),
                                    'settings' => $settings,
                                ];
                            }

                            $blocks[] = [
                                'name' => $blockModel->getName(),
                                'title' => $blockModel->getTitle(),
                                'icon' => $blockModel->getIcon(),
                                'css' => $blockModel->getCSS(),
                                'category' => $blockModel->getCategory(),
                                'callback' => $blockModel->getCallback(),
                                'keywords' => (!empty($blockModel->getKeywords())) ? implode(", ", $blockModel->getKeywords()) : null,
                                'postTypes' => (!empty($blockModel->getPostTypes())) ? implode(", ", $blockModel->getPostTypes()) : null,
                                'controls' => $controls,
                            ];
                        }

                        // apply filters
                        $blocks = array_filter($blocks, function (array $block) use($args) {
                            $queriedName = isset($args['name']) ? $args['name'] : null;

                            if($queriedName){
                                return Strings::contains($queriedName, $block['name']);
                            }

                            return true;
                        });

                        return [
                            "blocks" => $blocks
                        ];
                    }
            ] );

        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * @param $key
     * @param $value
     *
     * @return array
     */
    private function formatGenericSettings($key, $value)
    {
        if(is_bool($value)){
            $value = $value ? "TRUE" : "FALSE";
        }

        if(is_array($value)){
            $value = json_encode($value);
        }

        return [
            'key' => $key,
            'value' => $value,
        ];
    }
}