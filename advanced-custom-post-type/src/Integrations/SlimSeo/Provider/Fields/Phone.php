<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

use ACPT\Utils\PHP\Phone as PhoneFormatter;

class Phone extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if(is_array($this->value) and isset($this->value['value'])){
            $value = $this->value['value'];
            $dial = $this->value['dial'];

            return wp_strip_all_tags($this->before . PhoneFormatter::format($value, $dial, PhoneFormatter::FORMAT_E164) . $this->after);
        }

        return '';
    }
}
