<?php

namespace ACPT\Integrations\GenerateBlocks\Provider\Fields;

use ACPT\Core\Helper\Weights;

class WeightField extends CurrencyField
{
    /**
     * @inheritDoc
     */
    protected function render($rawValue, $options = [])
    {
        if(!is_array($rawValue)){
            return null;
        }

        if(empty($rawValue['value'])){
            return null;
        }

        $val = $rawValue['value'];
        $before = $rawValue['before'];
        $after = $rawValue['after'];

        if(!isset($val['value'])){
            return null;
        }

        if(!isset($val['weight'])){
            return null;
        }

        if(!isset($val['weight']['symbol'])){
            return null;
        }

        if(!isset(Weights::getList()[$val['weight']]['symbol'])){
            return null;
        }

        if(isset($options['raw']) and $options['raw'] == 1){
            $value = $val['value'];
        } else {
            $decimals = $options['decimals'] ?? 2;
            $decimal_separator = $options['decimal_separator'] ?? ".";
            $thousands_separator = $options['thousands_separator'] ?? ',';
            $value = number_format($val['value'], $decimals, $decimal_separator, $thousands_separator);
        }

        $symbol = $val['weight']['symbol'];
        $position = $options['uom_position'];

        if($position === "after"){
            return $before . $value . " " . $symbol . $after;
        }

        if($position === "before"){
            return $before . $symbol . " " . $value . $after;
        }

        return $before . $value . $after;
    }
}