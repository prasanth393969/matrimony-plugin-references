<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

use ACPT\Utils\Wordpress\Users;

class Relationship extends Base
{
    /**
     * @return array|null
     */
    public function getValue()
    {
        if(is_array($this->value) and !empty($this->value)){

            $elements = [];

            foreach ($this->value as $element){
                if($element instanceof \WP_Post){
                    $elements[] = $element->post_title;
                } elseif($element instanceof \WP_Term){
                    $elements[] = $element->name;
                } elseif($element instanceof \WP_User){
                    $elements[] = Users::getUserLabel($element);
                }
            }

            return $elements;
        }

        return [];
    }
}
