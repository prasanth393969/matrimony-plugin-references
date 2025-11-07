<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

class Currency extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if(is_array($this->value) and isset($this->value['currency']) and isset($this->value['unit'])){
            $value = $this->value['currency'] . ' ' . $this->value['unit'];

            return wp_strip_all_tags($this->before . $value . $this->after);
        }

        return '';
    }
}
