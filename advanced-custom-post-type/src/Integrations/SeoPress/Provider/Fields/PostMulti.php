<?php

namespace ACPT\Integrations\SeoPress\Provider\Fields;

class PostMulti extends Base
{
    /**
     * @return array|null
     */
    public function getValue()
    {
        if(is_array($this->value) and !empty($this->value)){
            $posts = [];

            foreach ($this->value as $post){
                if($post instanceof \WP_Post){
                    $posts[] = $post->post_title;
                }
            }

            return $posts;
        }

        return [];
    }
}
