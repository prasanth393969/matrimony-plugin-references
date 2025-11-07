<?php

namespace ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Blocks;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\Users;

class RelationalBlockRenderer extends AbstractBlockRenderer
{
    /**
     * @param $attributes
     * @param $content
     *
     * @return string
     */
    public function render($attributes, $content)
    {
        $rawValue = $this->rawValue($attributes);

        if(empty($rawValue)){
            return null;
        }

        if(!isset($rawValue->value['value'])){
            return null;
        }

        $value = $rawValue->value['value'];

        switch ($rawValue->type){

            // POST_TYPE
            case MetaFieldModel::POST_TYPE:

                if(is_array($value)){
                    $c = null;

                    foreach ($value as $val){
                        $c .= $this->replacePlaceholderWithContent($content, $val);
                    }

                    return $c;
                }

                return $this->replacePlaceholderWithContent($content, $value);

                break;

            // Single value
            case MetaFieldModel::POST_OBJECT_TYPE:
            case MetaFieldModel::TERM_OBJECT_TYPE:
            case MetaFieldModel::USER_TYPE:
                return $this->replacePlaceholderWithContent($content, $value);

            // Array of values
            case MetaFieldModel::POST_OBJECT_MULTI_TYPE:
            case MetaFieldModel::TERM_OBJECT_MULTI_TYPE:
            case MetaFieldModel::USER_MULTI_TYPE:

                $c = null;

                foreach ($value as $val){
                    $c .= $this->replacePlaceholderWithContent($content, $val);
                }

                return $c;
        }

        return null;
    }

    /**
     * @param $content
     * @param $value
     *
     * @return string
     */
    private function replacePlaceholderWithContent($content, $value)
    {
        // {{wp_post_title:link}}
        // {{wp_post_title}}
        // {{wp_post_excerpt}}
        // {{wp_post_thumbnail_url}}
        if($value instanceof \WP_Post){
            $content = str_replace("{{wp_post_excerpt}}", $value->post_excerpt, $content);
            $content = str_replace("{{wp_post_title}}", $value->post_title, $content);
            $content = str_replace("{{wp_post_title:link}}", "<a href='".get_permalink($value->ID)."'>".$value->post_title."</a>", $content);

            preg_match_all('/(?:<img\s+src="(.*?)"[^.]*?\/?>|&lt;img\s+src="(.*?)"[^.]*?\/?&gt;)/', $content, $matchedImages);

            if(!empty($matchedImages[0])){
                foreach ($matchedImages[0] as $index => $matchedImage){
                    $img = $matchedImage;
                    $src = $matchedImages[1][$index];

                    $replacedImg = str_replace("{{wp_post_thumbnail_url}}", $value->post_title, $img);
                    $realImageUrl = get_the_post_thumbnail_url($value->ID);

                    if(!empty($realImageUrl)){
                        $replacedImg = str_replace($src, get_the_post_thumbnail_url($value->ID), $replacedImg);
                    }

                    $content = str_replace($img, $replacedImg, $content);
                }
            }
        }

        // {{term_name}}
        // {{term_name:link}}
        if($value instanceof \WP_Term){
            $content = str_replace("{{term_name}}", $value->name, $content);
            $content = str_replace("{{term_name:link}}", '<a href="'.get_term_link($value->term_id).'">'.$value->name.'</a>', $content);
        }

        // {{wp_user_avatar}}
        // {{wp_user_name}}
        // {{wp_user_bio}}
        if($value instanceof \WP_User){
            $content = str_replace("{{wp_user_avatar}}", Users::getAvatar($value, 48), $content);
            $content = str_replace("{{wp_user_name}}", Users::getUserLabel($value), $content);
            $content = str_replace("{{wp_user_bio}}", Users::getBio($value), $content);
        }

        return $content;
    }
}


