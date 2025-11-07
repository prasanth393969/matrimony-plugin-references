<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

class DateRange extends Base
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

        if(is_array($rawValue) and count($rawValue) === 2){
            $value = $rawValue[0]. " - " . $rawValue[1];

            return wp_strip_all_tags($this->before . $value . $this->after);
        }

        return '';
    }
}
