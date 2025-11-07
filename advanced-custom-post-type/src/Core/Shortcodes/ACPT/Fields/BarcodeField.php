<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Core\Helper\Strings;

class BarcodeField extends AbstractField
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

        if(!isset($rawData['barcode_value'])){
            return $rawData['value'];
        }

        if(!is_string($rawData['barcode_value'])){
            return $rawData['value'];
        }

        if(empty($rawData['barcode_value'])){
            return $rawData['value'];
        }

        if(!Strings::isJson($rawData['barcode_value'])){
            return $rawData['value'];
        }

        $barcodeValue = json_decode($rawData['barcode_value'], true);

        if(!isset($barcodeValue['svg'])){
            return $rawData['value'];
        }

        if(empty($barcodeValue['svg'])){
            return $rawData['value'];
        }

        return html_entity_decode($barcodeValue['svg']);
    }
}