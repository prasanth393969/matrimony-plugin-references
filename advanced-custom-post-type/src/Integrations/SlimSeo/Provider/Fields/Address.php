<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

class Address extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if(is_array($this->value) and isset($this->value['address'])){
            return wp_strip_all_tags($this->before . $this->value['address'] . $this->after );
        }

        return '';
    }
}
