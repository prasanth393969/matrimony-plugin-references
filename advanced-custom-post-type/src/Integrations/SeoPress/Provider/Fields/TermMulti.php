<?php

namespace ACPT\Integrations\SeoPress\Provider\Fields;

class TermMulti extends Base
{
    /**
     * @return array|null
     */
    public function getValue()
    {
        if(is_array($this->value) and !empty($this->value)){
            $terms = [];

            foreach ($this->value as $term){
                if($term instanceof \WP_Term){
                    $terms[] = $term->name;
                }
            }

            return $terms;
        }

        return [];
    }
}
