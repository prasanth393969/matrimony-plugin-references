<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Utils\PHP\Arrays;
use ACPT\Utils\Wordpress\Posts;
use ACPT\Utils\Wordpress\Terms;
use ACPT\Utils\Wordpress\Users;

class GenerateRelationalFieldOptionsQuery implements QueryInterface
{
    /**
     * @var string
     */
    private $fieldType;

    /**
     * @var array
     */
    private $args;

    /**
     * @var null
     */
    private $defaultValues;

    /**
     * @var null
     */
    private $layout;
    /**
     * @var null
     */
    private $id;

    /**
     * @var string
     */
    private $format;

    /**
     * GenerateRelationalFieldOptionsQuery constructor.
     *
     * @param        $fieldType
     * @param array  $args
     * @param string $format
     * @param null   $defaultValues
     * @param null   $layout
     * @param null   $id
     */
    public function __construct( $fieldType, array $args = [], $format = 'html', $defaultValues = null, $layout = null, $id = null)
    {
        $this->fieldType = $fieldType;
        $this->args = $args ?? [];

        if(!empty($defaultValues) and !is_array($defaultValues)){
            $defaultValues = explode(",",$defaultValues);
        }

        $this->format = $format;
        $this->defaultValues = $defaultValues;
        $this->layout = $layout;
        $this->id = $id;
    }

    /**
     * @return array|mixed
     */
    public function execute()
    {
        $return = [
            'options' => '',
            'selected' => '',
        ];

        $options = $this->getOptions();

        if(empty($options)){
            return $return;
        }

        if($this->format === "data"){
            return $this->formatData($options);
        }

        return $this->renderHtml($options);
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        try {
            switch ($this->fieldType){

                // Post objects
                case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
                case MetaFieldModel::POST_OBJECT_TYPE:

                    $query = [];

                    if(isset($this->args['post_type'])){
                        $query['post_type'] = $this->args['post_type'];
                    }

                    if(isset($this->args['post_status'])){
                        $query['post_status'] = $this->args['post_status'];
                    }

                    if(isset($this->args['post_taxonomy'])){
                        $query['taxonomy'] = $this->args['post_taxonomy'];
                    }

                    $posts = [];
                    $data = Posts::getList($query);

                    foreach ($data as $post){

                        $postType = $post['postType'];

                        foreach ($post['posts'] as $id => $title){

                            if($id !== get_the_ID()){
                                $posts[$postType][] = [
                                    'value' => $id,
                                    'label' => $title,
                                ];
                            }
                        }
                    }

                    return $posts;

                // Relational field
                case MetaFieldModel::POST_TYPE:

                    if(!isset($this->args['toType'])){
                        throw new \Exception('Missing `toType`');
                    }

                    if(!isset($this->args['toValue'])){
                        throw new \Exception('Missing `toValue`');
                    }

                    $toType = $this->args['toType'];
                    $toValue = $this->args['toValue'];

                    switch($toType){

                        case MetaTypes::MEDIA:
                        case MetaTypes::CUSTOM_POST_TYPE:

                            $posts = [];
                            $data = Posts::getPostsByTypeFromCache($toValue);
                            $data = array_filter($data, function ($post){
                                return $post->ID != get_the_ID();
                            });

                            foreach ($data as $post){
                                $posts[$toValue][] = [
                                    'value' => $post->ID,
                                    'label' => $post->post_title,
                                ];
                            }

                            return $posts;

                        case MetaTypes::TAXONOMY:

                            $terms = [];
                            $data = $categoryIds = get_terms([
                                'taxonomy'   => $toValue,
                                'hide_empty' => false,
                            ]);

                            foreach ($data as $term){
                                $terms[$toValue][] = [
                                    'value' => $term->term_id,
                                    'label' => $term->name,
                                ];
                            }

                            return $terms;

                        case MetaTypes::OPTION_PAGE:

                            $pages = [];
                            $data = OptionPageRepository::get([]);

                            foreach ($data as $page){
                                $pages['Pages'][] = [
                                    'label' => $page->getMenuTitle(),
                                    'value' => $page->getId(),
                                ];

                                foreach ($page->getChildren() as $child){
                                    $pages['Pages'][] = [
                                        'label' => $child->getMenuTitle(),
                                        'value' => $child->getId(),
                                    ];
                                }
                            }

                            return $pages;

                        case MetaTypes::USER:

                            $users = [];

                            foreach(Users::getList() as $id => $user){
                                $users['Users'][] = [
                                    'label' => $user,
                                    'value' => $id
                                ];
                            }

                            return $users;
                    }

                    break;

                case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
                case MetaFieldModel::TERM_OBJECT_TYPE:

                    $query = [];

                    if(isset($this->args['term_taxonomy'])){
                        $query['taxonomy'] = $this->args['term_taxonomy'];
                    }

                    $terms = [];
                    $data = Terms::getList($query);

                    foreach ($data as $tax){

                        $taxonomy = $tax['taxonomy'];

                        foreach ($tax['terms'] as $id => $term){
                            $terms[$taxonomy][] = [
                                'value' => $id,
                                'label' => $term,
                            ];
                        }
                    }

                    return $terms;

                case MetaFieldModel::USER_TYPE:
                case MetaFieldModel::USER_MULTI_TYPE:

                    $query = [];

                    if(isset($this->args['user_role'])){
                        $query['role'] = explode(",", $this->args['user_role']);
                    }

                    $users = [];
                    $data = Users::getList($query);

                    foreach ($data as $id => $user){
                        $users["Users"][] = [
                            'value' => $id,
                            'label' => $user,
                        ];
                    }

                    return $users;
            }

        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return [];
        }

        return [];
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function renderHtml(array $options)
    {
        $return = [
            'options' => '',
            'selected' => '',
        ];

        // options
        foreach ($options as $section => $opts){

            $return['options'] .= '<div class="section">'.$section.'</div>';

            foreach ($opts as $option){
                $cssClass = (is_array($this->defaultValues) and in_array($option['value'], $this->defaultValues)) ? 'hidden selected' : '';

                $return['options'] .= '
                    <div 
                        id="'.$option['value'].'" 
                        class="value '.$cssClass.'" 
                        data-value="'.$option['value'].'"
                    >
                        '.$this->renderLabel($option['label'], $option['value'], $this->layout).'
                    </div>
                ';
            }
        }

        // selected values
        if(is_array($this->defaultValues)){
            foreach ($this->defaultValues as $value){

                $filter = [];

                foreach ($options as $opts){
                    $filter = array_merge(array_filter($opts, function($item) use ($value) {
                        return $item['value'] == $value;
                    }), $filter);
                }

                if(!empty($filter)){
                    $filter = Arrays::reindex($filter);

                    $return['selected'] .= '
                        <li id="'.$this->id.'" class="sortable-li sortable-li-'.$this->id.' value" data-value="'.$value.'">
                            <div class="handle-placeholder">
                                <span class="handle">.<br/>.<br/>.</span>
                                <span class="placeholder">'.$this->renderLabel($filter[0]['label'], $value, $this->layout).'</span>
                            </div>
                            <a class="delete" href="#">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                                    <path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path>
                                </svg>
                            </a>
                        </li>
                    ';
                }
            }
        }

        return $return;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function formatData(array $options)
    {
        $data = [];

        foreach ($options as $label => $value){
            if(is_array($value)){

                $opt = [];

                foreach ($value as $option){
                    $opt [] = [
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'selected' => (is_array($this->defaultValues) and in_array($option['value'], $this->defaultValues)) ? true : false,
                    ];
                }

                $data[] = [
                    'label' => $label,
                    'options' => $opt,
                ];

            } else {
                $data[] = [
                    'label' => $label,
                    'value' => $value,
                    'selected' => (is_array($this->defaultValues) and in_array($value, $this->defaultValues)) ? true : false,
                ];
            }
        }

        return $data;
    }

    /**
     * @param $label
     * @param $value
     * @param null $layout
     *
     * @return mixed
     */
    private function renderLabel($label, $value, $layout = null)
    {
        if($layout === 'image'){

            switch ($this->fieldType) {

                // Post objects
                case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
                case MetaFieldModel::POST_OBJECT_TYPE:
                    $thumbnailUrl = get_the_post_thumbnail_url($value);

                    if(empty($thumbnailUrl)){
                        $thumbnailUrl = "https://placehold.co/40x40";
                    }

                    return $this->renderItemWithThumbnail($thumbnailUrl, $label);

                // User objects
                case MetaFieldModel::USER_TYPE:
                case MetaFieldModel::USER_MULTI_TYPE:
                    $thumbnailUrl = get_avatar_url($value);

                    if(empty($thumbnailUrl)){
                        $thumbnailUrl = "https://placehold.co/40x40";
                    }

                    return $this->renderItemWithThumbnail($thumbnailUrl, $label);

                // Relational field
                case MetaFieldModel::POST_TYPE:
                    $toType = $this->args[ 'toType' ];

                    switch ($toType){

                        case MetaTypes::CUSTOM_POST_TYPE:
                        case MetaTypes::MEDIA:
                            $thumbnailUrl = get_the_post_thumbnail_url($value);

                            if(empty($thumbnailUrl)){
                                $thumbnailUrl = "https://placehold.co/40x40";
                            }

                            return $this->renderItemWithThumbnail($thumbnailUrl, $label);

                        case MetaTypes::USER:
                            $thumbnailUrl = get_avatar_url($value);

                            if(empty($thumbnailUrl)){
                                $thumbnailUrl = "https://placehold.co/40x40";
                            }

                            return $this->renderItemWithThumbnail($thumbnailUrl, $label);

                        default:
                            return $label;
                    }
            }
        }

        return $label;
    }

    /**
     * @param $thumbnailUrl
     * @param $label
     *
     * @return string
     */
    private function renderItemWithThumbnail($thumbnailUrl, $label)
    {
        $labelToRender = '<div class="selectize-item">';
        $labelToRender .= '<div class="selectize-thumbnail">';
        $labelToRender .= '<img src="'.$thumbnailUrl.'" width="40" height="40" alt="'.$label.'">';
        $labelToRender .= '</div>';
        $labelToRender .= '<div class="selectize-label">'.$label.'</div>';
        $labelToRender .= '</div>';

        return $labelToRender;
    }
}