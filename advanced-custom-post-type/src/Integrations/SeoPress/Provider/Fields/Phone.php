<?php

namespace ACPT\Integrations\SeoPress\Provider\Fields;

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
            $dial = $this->value['dial'] ?? null;
            $phone = PhoneFormatter::format($value, $dial, PhoneFormatter::FORMAT_E164);

            return wp_strip_all_tags($this->before . $phone . $this->after);
        }

        return '';
    }
}
