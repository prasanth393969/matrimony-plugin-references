<?php

namespace ACPT\Integrations\SeoPress\Provider\Fields;

class Term extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if($this->value instanceof \WP_Term){
            return $this->value->name;
        }

        return '';
    }
}
