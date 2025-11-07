<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

class ArrayValues extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        $values = [];

        if(is_array($this->value) and !empty($this->value)){
            foreach ($this->value as $value){
                $values[] = $this->before . $value. $this->after;
            }
        }

        return implode(", ", $values);
    }
}
