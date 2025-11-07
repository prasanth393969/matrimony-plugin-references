<?php

namespace ACPT\Integrations\SeoPress\Provider\Fields;

class DateTime extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if(!isset($this->value['value'])){
            return '';
        }

        $rawValue = $this->value['value'];

        if(is_string($rawValue)){
            return wp_strip_all_tags($this->before . $rawValue . $this->after);
        }

        return '';
    }
}
