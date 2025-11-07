<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

class Url extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if(is_array($this->value) and isset($this->value['url'])){
            $value = $this->value['url'];

            return esc_url($value);
        }

        return '';
    }
}
