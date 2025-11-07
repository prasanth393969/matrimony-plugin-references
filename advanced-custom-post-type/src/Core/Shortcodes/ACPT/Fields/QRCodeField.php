<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Core\Helper\Strings;

class QRCodeField extends AbstractField
{
    public function render()
    {
        if(!$this->isFieldVisible()){
            return null;
        }

        $rawData = $this->fetchRawData();

        if(!isset($rawData['value'])){
            return null;
        }

        if($this->payload->preview){
            return $rawData['value'];
        }

        if(!isset($rawData['qr_code_value'])){
            return $rawData['value'];
        }

        if(!is_string($rawData['qr_code_value'])){
            return $rawData['value'];
        }

        if(empty($rawData['qr_code_value'])){
            return $rawData['value'];
        }

        if(!Strings::isJson($rawData['qr_code_value'])){
            return $rawData['value'];
        }

        $QRCodeValue = json_decode($rawData['qr_code_value'], true);

        if(!isset($QRCodeValue['img'])){
            return $rawData['value'];
        }

        if(!isset($QRCodeValue['resolution'])){
            return $rawData['value'];
        }

        return "<img src='".$QRCodeValue['img']."' alt='".$rawData['value']."' width='".$QRCodeValue['resolution']."' height='".$QRCodeValue['resolution']."' />";
    }
}