<?php

namespace ACPT\Integrations\SlimSeo\Provider;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\OptionPageRepository;

abstract class AbstractSlimSeoProvider
{
    const BASE_ID = "acpt";

    /**
     * Allowed fields
     */
    const ALLOWED_FIELDS = [
        MetaFieldModel::ADDRESS_TYPE,
        MetaFieldModel::ADDRESS_MULTI_TYPE,
        MetaFieldModel::CHECKBOX_TYPE,
        MetaFieldModel::COLOR_TYPE,
        MetaFieldModel::COUNTRY_TYPE,
        MetaFieldModel::CURRENCY_TYPE,
        MetaFieldModel::DATE_TYPE,
        MetaFieldModel::DATE_RANGE_TYPE,
        MetaFieldModel::DATE_TIME_TYPE,
        MetaFieldModel::EDITOR_TYPE,
        MetaFieldModel::EMAIL_TYPE,
        MetaFieldModel::HTML_TYPE,
        MetaFieldModel::ID_TYPE,
        MetaFieldModel::LENGTH_TYPE,
        MetaFieldModel::LIST_TYPE,
        MetaFieldModel::NUMBER_TYPE,
        MetaFieldModel::PASSWORD_TYPE,
        MetaFieldModel::PHONE_TYPE,
        MetaFieldModel::POST_TYPE,
        MetaFieldModel::POST_OBJECT_TYPE,
        MetaFieldModel::POST_OBJECT_MULTI_TYPE,
        MetaFieldModel::QR_CODE_TYPE,
        MetaFieldModel::RADIO_TYPE,
        MetaFieldModel::RANGE_TYPE,
        MetaFieldModel::RATING_TYPE,
        MetaFieldModel::SELECT_TYPE,
        MetaFieldModel::SELECT_MULTI_TYPE,
        MetaFieldModel::TERM_OBJECT_TYPE,
        MetaFieldModel::TERM_OBJECT_MULTI_TYPE,
        MetaFieldModel::TEXTAREA_TYPE,
        MetaFieldModel::TEXT_TYPE,
        MetaFieldModel::TIME_TYPE,
        MetaFieldModel::TOGGLE_TYPE,
        MetaFieldModel::URL_TYPE,
        MetaFieldModel::USER_TYPE,
        MetaFieldModel::USER_MULTI_TYPE,
        MetaFieldModel::WEIGHT_TYPE,
    ];

    /**
     * @var array
     */
    protected array $variables = [];

    /**
     * Run the integration
     */
    public function __construct()
    {
        add_action( 'init', [ $this, 'init' ]);
    }

    /**
     * Run the integration
     * @see https://docs.wpslimseo.com/slim-seo-schema/integrations/acf/
     */
    abstract public function init(): void;

    /**
     * @param array $variables
     * @return array
     */
    public function addVariables( array $variables ): array
    {
        $this->variables = $variables;

        $groups = get_acpt_meta_group_objects();

        foreach ($groups as $group){

            $fields = [];

            foreach ($group->belongs as $belong){
                foreach ($group->boxes as $box){
                    $fields = array_merge($fields, $this->addFields($belong, $box->fields));
                }
            }

            $fields = array_unique($fields);

            if(!empty($fields)){
                $this->variables[] = [
                    'label'   => sprintf( __( '[ACPT] %s', 'slim-seo-schema' ), $group->label ?? $group->name ),
                    'options' => $fields
                ];
            }
        }

        return $this->variables;
    }

    /**
     * @param \stdClass $belong
     * @param array $fields
     * @param null $parent
     * @param string $indent
     * @return array
     */
    private function addFields($belong, array $fields = [], $parent = null, string $indent = '' ): array
    {
        $return = [];
        $subIndent = $indent . str_repeat( '&nbsp;', 5 );

        foreach ($fields as $field){
            if(in_array($field->type, self::ALLOWED_FIELDS)){

                $ids = [];
                $labels = [];

                // Option pages fields
                if($belong->belongsTo === MetaTypes::OPTION_PAGE){

                    $pages = [];

                    switch ($belong->operator){
                        case Operator::EQUALS:
                            $pages = [$belong->find];
                            break;

                        case Operator::IN:
                            $pages = explode(",", $belong->find);
                            break;

                       case Operator::NOT_IN:
                           $excludedPages = explode(",", $belong->find);
                           $allPages = OptionPageRepository::getAllSlugs();
                           $pages = array_diff($allPages, $excludedPages);
                           break;

                       case Operator::NOT_EQUALS:
                           $excludedPages = [$belong->find];
                           $allPages = OptionPageRepository::getAllSlugs();
                           $pages = array_diff($allPages, $excludedPages);
                           break;
                    }

                    foreach ($pages as $page){
                        $ids[]    = self::BASE_ID."." . $page . "." . ($parent !== null ? $parent->db_name."_". $field->name : $field->db_name);
                        $labels[] = $indent . "[".$page."] " . ($parent !== null ? "[".$parent->ui_name."] ". $field->label : $field->ui_name);
                    }
                }

                // Other fields
                else {
                    $ids[]    = self::BASE_ID."." . ($parent !== null ? $parent->db_name."_". $field->name : $field->db_name);
                    $labels[] = $indent . ($parent !== null ? "[".$parent->ui_name."] ". $field->label : $field->ui_name);
                }

                foreach ($ids as $i => $id){

                    $label = $labels[$i];

                    // address field
                    if($field->type === MetaFieldModel::ADDRESS_TYPE or $field->type === MetaFieldModel::ADDRESS_MULTI_TYPE){
                        $return[ $id . '.address' ] = sprintf( __( '%s (address)', 'slim-seo-schema' ), $label );
                        $return[ $id . '.lat' ]     = sprintf( __( '%s (latitude)', 'slim-seo-schema' ), $label );
                        $return[ $id . '.lng' ]     = sprintf( __( '%s (longitude)', 'slim-seo-schema' ), $label );
                        $return[ $id . '.city' ]    = sprintf( __( '%s (city)', 'slim-seo-schema' ), $label );
                        $return[ $id . '.country' ]    = sprintf( __( '%s (country)', 'slim-seo-schema' ), $label );
                    } else {
                        $return[$id] = $label;
                    }
                }
            }

            // repeater fields
            if ($field->type === MetaFieldModel::REPEATER_TYPE and !empty($field->children)) {
                $return = array_merge( $return, $this->addFields($belong, $field->children, $field, $subIndent));
            }
        }

        return $return;
    }

    /**
     * Used by Slim SEO and Slim SEO Schema
     *
     * Example usage:
     *
     * {{ acpt.text }}
     * {{ acpt.list.0 }}
     *
     * @param array $data
     * @param int $postId
     * @param int $termId
     * @return array
     */
    public function addData( array $data, int $postId, int $termId ): array
    {
        $postId = $postId ?: ( is_singular() ? get_queried_object_id() : get_the_ID() );

        if(empty($postId)){
            return $data;
        }

        $post = get_post($postId);
        $fieldObjects = $this->getFieldObjects($post);

        return $this->aggregateGroupsData($data, $fieldObjects);
    }

    /**
     * Used exclusively by Slim SEO Schema
     *
     * Example usage:
     *
     * {{ acpt.text }}
     * {{ acpt.list.0 }}
     *
     * @param array $data
     * @return array
     */
    public function addSchemaData( array $data ): array
    {
        $post = is_singular() ? get_queried_object() : get_post();

        if(empty($post)) {
            return [];
        }

        $fieldObjects = $this->getFieldObjects($post);

        return $this->aggregateGroupsData($data, $fieldObjects);
    }

    /**
     * @param \WP_Post $post
     * @return array
     */
    private function getFieldObjects(\WP_Post $post)
    {
        $fieldObjects = get_acpt_fields([
            'post_id' => (int)$post->ID,
            'with_context'  => true,
            'format'  => 'complete',
        ]);

        try {
            $optionPageSlugs = OptionPageRepository::getAllSlugs();

            foreach ($optionPageSlugs as $optionPageSlug){

                $pageFieldObjects = get_acpt_fields([
                    'option_page' => $optionPageSlug,
                    'with_context'  => true,
                    'format'  => 'complete',
                ]);

                foreach ($pageFieldObjects as $i => $pageFieldObject){
                    $pageFieldObjects[$i]->option_page = $optionPageSlug;
                }

                $fieldObjects = array_merge($pageFieldObjects, $fieldObjects);
            }
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }

        // @TODO add user fields

        return $fieldObjects;
    }

    /**
     * @param array $data
     * @param array $fieldObjects
     * @return array
     */
    private function aggregateGroupsData(array $data, array $fieldObjects = [])
    {
        if(!empty($fieldObjects)){
            foreach ($fieldObjects as $fieldObject){
                $value = (new FieldRenderer($fieldObject))->render();

                // address
                if($fieldObject->type === MetaFieldModel::ADDRESS_TYPE and is_array($value)){
                    if(isset($fieldObject->option_page)){
                        $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['address' ]  = $value['address'] ?? null;
                        $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['lat' ]      = $value['lat'] ?? null;
                        $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['lng' ]      = $value['lng'] ?? null;
                        $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['city' ]     = $value['city'] ?? null;
                        $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['country' ]  = $value['country'] ?? null;
                    } else {
                        $data[self::BASE_ID][ $fieldObject->db_name ]['address' ]  = $value['address'] ?? null;
                        $data[self::BASE_ID][ $fieldObject->db_name ]['lat' ]      = $value['lat'] ?? null;
                        $data[self::BASE_ID][ $fieldObject->db_name ]['lng' ]      = $value['lng'] ?? null;
                        $data[self::BASE_ID][ $fieldObject->db_name ]['city' ]     = $value['city'] ?? null;
                        $data[self::BASE_ID][ $fieldObject->db_name ]['country' ]  = $value['country'] ?? null;
                    }
                }

                // address multi
                elseif($fieldObject->type === MetaFieldModel::ADDRESS_MULTI_TYPE and is_array($value)){
                    foreach ($value as $index => $address){
                        if(isset($fieldObject->option_page)){
                            $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['address' ][$index] = $address['address'] ?? null;
                            $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['lat' ][$index]     = $address['lat'] ?? null;
                            $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['lng' ][$index]     = $address['lng'] ?? null;
                            $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['city' ][$index]    = $address['city'] ?? null;
                            $data[self::BASE_ID][$fieldObject->option_page][ $fieldObject->db_name ]['country' ][$index]    = $address['country'] ?? null;
                        } else {
                            $data[self::BASE_ID][ $fieldObject->db_name ]['address' ][$index] = $address['address'] ?? null;
                            $data[self::BASE_ID][ $fieldObject->db_name ]['lat' ][$index]     = $address['lat'] ?? null;
                            $data[self::BASE_ID][ $fieldObject->db_name ]['lng' ][$index]     = $address['lng'] ?? null;
                            $data[self::BASE_ID][ $fieldObject->db_name ]['city' ][$index]    = $address['city'] ?? null;
                            $data[self::BASE_ID][ $fieldObject->db_name ]['country' ][$index]    = $address['country'] ?? null;
                        }
                    }
                }

                // repeater
                elseif($fieldObject->type === MetaFieldModel::REPEATER_TYPE and count($fieldObject->children) > 0){
                    foreach ($fieldObject->children as $childFieldObject){
                        foreach ($childFieldObject->value as $sub){
                            $childFieldObjectCopy = $childFieldObject;
                            $childFieldObjectCopy->value = $sub;
                            $subValue = (new FieldRenderer($childFieldObjectCopy))->render();

                            if(isset($fieldObject->option_page)){
                                $data[self::BASE_ID][$fieldObject->option_page][$fieldObject->db_name."_". $childFieldObject->name][] = $subValue;
                            } else {
                                $data[self::BASE_ID][$fieldObject->db_name."_". $childFieldObject->name][] = $subValue;
                            }

                            // nested repeater
                            if($childFieldObject->type === MetaFieldModel::REPEATER_TYPE and count($childFieldObject->children) > 0){
                                foreach ($childFieldObject->children as $subChildFieldObject){
                                    if(isset($subChildFieldObject->value) and is_array($subChildFieldObject->value)){
                                        foreach ($subChildFieldObject->value as $nestedSub){
                                            $childFieldObjectCopy = $subChildFieldObject;
                                            $childFieldObjectCopy->value = $nestedSub;
                                            $subValue = (new FieldRenderer($childFieldObjectCopy))->render();

                                            if(isset($fieldObject->option_page)){
                                                $data[self::BASE_ID][$fieldObject->option_page][$childFieldObject->db_name."_". $subChildFieldObject->name][] = $subValue;
                                            } else {
                                                $data[self::BASE_ID][$childFieldObject->db_name."_". $subChildFieldObject->name][] = $subValue;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // default
                    if(isset($fieldObject->option_page)){
                        $data[self::BASE_ID][$fieldObject->option_page][$fieldObject->db_name] = $value;
                    } else {
                        $data[self::BASE_ID][$fieldObject->db_name] = $value;
                    }
                }
            }
        }

        return $data;
    }
}
