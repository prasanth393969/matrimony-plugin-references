<?php

namespace ACPT\Utils\PHP;

class QRCode
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

        if(empty($rawData['url'])){
            return null;
        }

        if(empty($rawData['value'])){
            return $rawData['url'];
        }

        if(!isset($rawData['value']['img'])){
            return $rawData['url'];
        }

        if(!isset($rawData['value']['resolution'])){
            return $rawData['url'];
        }

        return '<img alt="'.$rawData['url'].'" src="'.$rawData['value']['img'].'" width="'.$rawData['value']['resolution'].'" height="'.$rawData['value']['resolution'].'" />';
    }
}
