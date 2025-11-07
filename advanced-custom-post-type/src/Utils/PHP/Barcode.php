<?php

namespace ACPT\Utils\PHP;

class Barcode
{
    /**
     * @param array $rawData
     * @return string
     */
    public static function render($rawData)
    {
        if(!is_array($rawData)){
            return null;
        }

        if(empty($rawData['text'])){
            return null;
        }

        if(empty($rawData['value'])){
            return $rawData['text'];
        }

        if(!isset($rawData['value']['svg'])){
            return $rawData['text'];
        }

        if(!isset($rawData['value']['format'])){
            return $rawData['text'];
        }

        preg_match_all('/<svg(.*?)id=\"(.*?)\"(.*?)>/', $rawData['value']['svg'], $match);
        if(isset($match[2]) and isset($match[2][0])){
            return $rawData['value']['svg'];
        }

        return $rawData['text'];
    }
}
