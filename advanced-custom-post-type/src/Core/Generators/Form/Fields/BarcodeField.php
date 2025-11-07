<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Utils\PHP\JSON;
use ACPT\Utils\Wordpress\Translator;

class BarcodeField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $id = Strings::generateRandomId();
        $savedBarcodeValue = $this->barcodeValue();
        $savedBarcodeValueJson = (!empty($savedBarcodeValue)) ? htmlspecialchars(json_encode($savedBarcodeValue)) : null;

        $field  = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName("barcode_value")).'" id="barcode_value_'.$id.'" value="'.$savedBarcodeValueJson.'">';
        $field .= "<div class='acpt-barcode-wrapper' id='acpt-barcode-wrapper-".$id."'>";
        $field .= $this->renderBarcodeGenerator($id, $savedBarcodeValue);
        $field .= "</div>";

        return $field;
    }

    /**
     * @return array|null
     */
    private function barcodeValue()
    {
        $savedBarcodeValue = $this->defaultExtraValue('barcode_value');

        if(empty($savedBarcodeValue)){
            return null;
        }

        if(!is_string($savedBarcodeValue)){
            return null;
        }

        if(JSON::isValid($savedBarcodeValue)){
            return json_decode($savedBarcodeValue, true);
        }

        return null;
    }

    /**
     * @param $id
     * @param $savedQRCodeValue
     * @return string
     */
    private function renderBarcodeGenerator($id, $savedQRCodeValue)
    {
        $formats = [
            "code128",
            "ean13",
            "ean8",
            "ean5",
            "ean2",
            "upc",
            "code39",
            "itf14",
            "msi",
            "pharmacode",
        ];

        $defaultFormat = $savedQRCodeValue['format'] ?? "code128";
        $defaultSvg = (isset($savedQRCodeValue['svg'])) ? html_entity_decode($savedQRCodeValue['svg']) : "";
        $defaultColor = $savedQRCodeValue['color'] ?? "#000000";
        $defaultBgColor = $savedQRCodeValue['bgColor'] ?? "#ffffff";

        $field = "<div class='settings'>";

        // URL
        $field .= "<div>";
        $field .= "<label class='acpt-form-label'>".Translator::translate("Enter the value")."</label>";
        $field .= "<input
					id='".Strings::esc_attr($this->getIdName())."'
					name='".Strings::esc_attr($this->getIdName())."'
					placeholder='".Translator::translate("example: 12345")."'
					value='".$this->defaultValue()."'
					type='text'
					class='value ".$this->cssClass()."'
				/>";
        $field .= "</div>";

        // Resolution
        $field .= "<div>";
        $field .= "<label class='acpt-form-label'>".Translator::translate("Format")."</label>";
        $field .= "<select class='acpt-admin-meta-field-input format'>";

        foreach ($formats as $format){
            $selected = ($format == $defaultFormat) ? 'selected="selected"' : '';
            $field .= "<option ".$selected." value='".$format."'>".$format."</option>";
        }

        $field .= "</select>";
        $field .= "</div>";

        // Colors
        $field .= "<div class='colors'>";
        $field .= "<div>
            <label class='acpt-form-label'>".Translator::translate("Color")."</label>
                <div class='acpt-color-picker'>
                    <input class='color' type='color' value='".$defaultColor."'/>
                    <span class='color_val'>".$defaultColor."</span>
                </div>
            </div>";

        $field .= "<div>
            <label class='acpt-form-label'>".Translator::translate("Background")."</label>
                <div class='acpt-color-picker'>
                    <input class='acpt-color-picker bgColor' type='color' value='".$defaultBgColor."'/>
                    <span class='color_val'>".$defaultBgColor."</span>
                </div>
            </div>";
        $field .= "</div>";

        // Clear button
        $field .= "<a href='#' class='clear-barcode'>".Translator::translate("Clear")."</a>";
        $field .= "</div>";

        // SVG
        $field .= "<div>";
        $field .= "<div class='acpt-barcode' id='acpt-barcode-".$id."'>";

        if($defaultSvg !== null){

            preg_match_all('/<svg(.*?)id=\"(.*?)\"(.*?)>/', $defaultSvg, $match);
            if(isset($match[2]) and isset($match[2][0])){
                $defaultSvg = str_replace($match[2][0], $id, $defaultSvg);
                $field .= $defaultSvg;
            } else {
                $field .= '<svg class="acpt-barcode-svg" id="'.$id.'"></svg>';
            }
        } else {
            $field .= '<svg class="acpt-barcode-svg" id="'.$id.'"></svg>';
        }

        $field .= "</div>";
        $field .= '<div class="acpt-barcode-errors"></div>';
        $field .= "</div>";

        return $field;
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets()
    {
        wp_enqueue_script( 'jsbarcode', plugins_url( 'advanced-custom-post-type/assets/vendor/jsbarcode/JsBarcode.all.min.js'), [], '3.12.1', true);
    }
}