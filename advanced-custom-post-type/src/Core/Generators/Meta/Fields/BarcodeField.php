<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\JSON;
use ACPT\Utils\Wordpress\Translator;

class BarcodeField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $id = "barcode_".Strings::generateRandomId();
        $this->enqueueAssets();

        $savedBarcodeValue = $this->barcodeValue();
        $savedBarcodeValueJson = (!empty($savedBarcodeValue)) ? htmlspecialchars(json_encode($savedBarcodeValue)) : null;
        $cssClass = 'regular-text acpt-admin-meta-field-input';

        if($this->hasErrors()){
            $cssClass .= ' has-errors';
        }

        if($this->isChild() or $this->isNestedInABlock()){

            if($this->isLeadingField()){
                $cssClass .= ' acpt-leading-field';
            }

            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::BARCODE_TYPE.'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[barcode_value]" id="barcode_value_'.$id.'" value=\''.$savedBarcodeValueJson.'\'>';
            $field .= $this->barcodeGenerator($id, Strings::esc_attr($this->getIdName()).'[value]', $savedBarcodeValue, $cssClass);
        } else {
            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::BARCODE_TYPE.'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_barcode_value" id="barcode_value_'.$id.'" value=\''.$savedBarcodeValueJson.'\'>';
            $field .= $this->barcodeGenerator($id, Strings::esc_attr($this->getIdName()), $savedBarcodeValue, $cssClass);
        }

        return $this->renderField($field);
    }

    /**
     * @param $id
     * @param $fieldId
     * @param $savedBarcodeValue
     * @param $cssClass
     *
     * @return string
     */
    private function barcodeGenerator($id, $fieldId, $savedBarcodeValue, $cssClass)
    {
        $defaultValue = Strings::esc_attr($this->getDefaultValue());
        $defaultSvg = (!empty($savedBarcodeValue) and isset($savedBarcodeValue['svg'])) ? html_entity_decode($savedBarcodeValue['svg']) : null;
        $defaultFormat = (!empty($savedBarcodeValue) and isset($savedBarcodeValue['format'])) ? $savedBarcodeValue['format'] : null;
        $defaultColor = (!empty($savedBarcodeValue) and isset($savedBarcodeValue['color'])) ? $savedBarcodeValue['color'] : null;
        $defaultBgColor = (!empty($savedBarcodeValue) and isset($savedBarcodeValue['bgColor'])) ? $savedBarcodeValue['bgColor'] : null;

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

        $barcode = "<div class='acpt-barcode-wrapper' id='acpt-barcode-wrapper-".$id."'>";

        // SVG
        $barcode .= "<div>";
        $barcode .= "<div class='acpt-barcode' id='acpt-".$id."'>";

        if($defaultSvg !== null){

            preg_match_all('/<svg(.*?)id=\"(.*?)\"(.*?)>/', $defaultSvg, $match);
            if(isset($match[2]) and isset($match[2][0])){
                $defaultSvg = str_replace($match[2][0], $id, $defaultSvg);
                $barcode .= $defaultSvg;
            } else {
                $barcode .= '<svg class="acpt-barcode-svg" id="'.$id.'"></svg>';
            }
        } else {
            $barcode .= '<svg class="acpt-barcode-svg" id="'.$id.'"></svg>';
        }

        $barcode .= "</div>";
        $barcode .= '<div class="acpt-barcode-errors"></div>';
        $barcode .= "</div>";

        $barcode .= "<div class='acpt-barcode-controls'>";

        // value
        $barcode .= "<div>";
        $barcode .= "<label>".Translator::translate("Enter the value")."</label>";
        $barcode .= "<input id='".$fieldId."' name='".$fieldId."' type='text' value='".$defaultValue."' class='".$cssClass." value'/>";
        $barcode .= "</div>";

        // format
        $barcode .= "<div>";
        $barcode .= "<label>".Translator::translate("Format")."</label>";
        $barcode .= "<select class='acpt-admin-meta-field-input format'>";

        foreach ($formats as $format){
            $selected = ($format == $defaultFormat) ? 'selected="selected"' : '';
            $barcode .= "<option ".$selected." value='".$format."'>".$format."</option>";
        }

        $barcode .= "</select>";
        $barcode .= "</div>";

        // colors
        $barcode .= "<div class='barcode-colors'>";
        $barcode .= "<div class='color-wrapper'>";
        $barcode .= "<label>".Translator::translate("Color")."</label>";
        $barcode .= "<input class='acpt-color-picker color' type='text' value='".$defaultColor."'/>";
        $barcode .= "</div>";
        $barcode .= "<div class='bg-color-wrapper'>";
        $barcode .= "<label>".Translator::translate("Background")."</label>";
        $barcode .= "<input class='acpt-color-picker bgColor' type='text' value='".$defaultBgColor."'/>";
        $barcode .= "</div>";
        $barcode .= "</div>";

        $barcode .= "<div class='buttons'>";
        $barcode .= "<button class='button button-danger clear-barcode'>".Translator::translate("Clear")."</button>";
        $barcode .= "</div>";
        
        $barcode .= "</div>";
        $barcode .= "</div>";

        return $barcode;
    }

    /**
     * @return array|null
     */
    private function barcodeValue()
    {
        $savedBarcodeValue = $this->getDefaultAttributeValue('barcode_value', null);

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

    private function enqueueAssets()
    {
        wp_enqueue_script( 'jsbarcode', plugins_url( 'advanced-custom-post-type/assets/vendor/jsbarcode/JsBarcode.all.min.js'), [], '3.12.1', true);
    }
}