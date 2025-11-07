<?php

namespace ACPT\Integrations\SeoPress\Provider\Fields;

class Post extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if($this->value instanceof \WP_Post){
            return $this->value->post_title;
        }

        return '';
    }
}
