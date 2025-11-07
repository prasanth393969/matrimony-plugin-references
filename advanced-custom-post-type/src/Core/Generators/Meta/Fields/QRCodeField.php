<?php

namespace ACPT\Core\Generators\Meta\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\JSON;
use ACPT\Utils\Wordpress\Translator;

class QRCodeField extends AbstractField
{
    public function render()
    {
        $id = "qr_code_".Strings::generateRandomId();
        $this->enqueueAssets();
        $savedQRCodeValue = $this->QRCodeValue();
        $savedQRCodeValueJson = (!empty($savedQRCodeValue)) ? json_encode($savedQRCodeValue) : null;
        $cssClass = 'regular-text acpt-admin-meta-field-input';

        if($this->hasErrors()){
            $cssClass .= ' has-errors';
        }

        if($this->isChild() or $this->isNestedInABlock()){

            if($this->isLeadingField()){
                $cssClass .= ' acpt-leading-field';
            }

            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[type]" value="'.MetaFieldModel::QR_CODE_TYPE.'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[original_name]" value="'.$this->metaField->getName().'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'[qr_code_value]" id="qr_code_value_'.$id.'" value=\''.$savedQRCodeValueJson.'\'>';
            $field .= $this->QRCodeGenerator($id, Strings::esc_attr($this->getIdName()).'[value]', $savedQRCodeValue, $cssClass);
        } else {
            $field = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_type" value="'.MetaFieldModel::QR_CODE_TYPE.'">';
            $field .= '<input type="hidden" name="'. Strings::esc_attr($this->getIdName()).'_qr_code_value" id="qr_code_value_'.$id.'" value=\''.$savedQRCodeValueJson.'\'>';
            $field .= $this->QRCodeGenerator($id, Strings::esc_attr($this->getIdName()), $savedQRCodeValue, $cssClass);
        }

        return $this->renderField($field);
    }

    /**
     * @param $id
     * @param $fieldId
     * @param $savedQRCodeValue
     * @param $cssClass
     * @return string
     */
    private function QRCodeGenerator($id, $fieldId, $savedQRCodeValue, $cssClass)
    {
        $defaultValue = Strings::esc_attr($this->getDefaultValue());
        $defaultImg = (!empty($savedQRCodeValue) and isset($savedQRCodeValue['img'])) ? $savedQRCodeValue['img'] : null;
        $defaultResolution = (!empty($savedQRCodeValue) and isset($savedQRCodeValue['resolution'])) ? $savedQRCodeValue['resolution'] : null;
        $defaultColorDark = (!empty($savedQRCodeValue) and isset($savedQRCodeValue['colorDark'])) ? $savedQRCodeValue['colorDark'] : null;
        $defaultColorLight = (!empty($savedQRCodeValue) and isset($savedQRCodeValue['colorLight'])) ? $savedQRCodeValue['colorLight'] : null;

        $resolutions = [
            100,
            200,
            300,
            400,
            500,
        ];

        $qrCode = "<div class='acpt-qr-code-wrapper' id='acpt-qr-code-wrapper-".$id."'>";
        $qrCode .= "<div class='acpt-qr-code' id='acpt-qr-code-".$id."'>";

        if(!empty($defaultImg)){
            $qrCode .= '<img src="'.$defaultImg.'" />';
        }

        $qrCode .= "</div>";
        $qrCode .= "<div class='acpt-qr-code-controls'>";

        // URL
        $qrCode .= "<div>";
        $qrCode .= "<label>".Translator::translate("Enter the URL")."</label>";
        $qrCode .= "<input id='".$fieldId."' name='".$fieldId."' type='url' value='".$defaultValue."' class='".$cssClass." url'/>";
        $qrCode .= "</div>";

        // resolution
        $qrCode .= "<div>";
        $qrCode .= "<label>".Translator::translate("Resolution")."</label>";
        $qrCode .= "<select class='acpt-admin-meta-field-input resolution'>";

        foreach ($resolutions as $resolution){
            $selected = ($resolution == $defaultResolution) ? 'selected="selected"' : '';
            $qrCode .= "<option ".$selected." value='".$resolution."'>".$resolution."x".$resolution."</option>";
        }

        $qrCode .= "</select>";
        $qrCode .= "</div>";

        // colors
        $qrCode .= "<div class='qr-code-colors'>";
        $qrCode .= "<div class='color-dark-wrapper'>";
        $qrCode .= "<label>".Translator::translate("Color dark")."</label>";
        $qrCode .= "<input class='acpt-color-picker color-dark' type='text' value='".$defaultColorDark."'/>";
        $qrCode .= "</div>";
        $qrCode .= "<div class='color-light-wrapper'>";
        $qrCode .= "<label>".Translator::translate("Color light")."</label>";
        $qrCode .= "<input class='acpt-color-picker color-light' type='text' value='".$defaultColorLight."'/>";
        $qrCode .= "</div>";
        $qrCode .= "</div>";
        
        $qrCode .= "<div class='buttons'>";
        $qrCode .= "<button class='button button-danger clear-qr-code'>".Translator::translate("Clear")."</button>";
        $qrCode .= "</div>";

        $qrCode .= "</div>";
        $qrCode .= "</div>";

        return $qrCode;
    }

    /**
     * @return array|null
     */
    private function QRCodeValue()
    {
        $savedQRCodeValue = $this->getDefaultAttributeValue('qr_code_value', null);

        if(empty($savedQRCodeValue)){
            return null;
        }

        if(!is_string($savedQRCodeValue)){
            return null;
        }

        if(JSON::isValid($savedQRCodeValue)){
            return json_decode($savedQRCodeValue, true);
        }

        return null;
    }

    private function enqueueAssets()
    {
        wp_enqueue_script( 'qrcodejs', plugins_url( 'advanced-custom-post-type/assets/vendor/qrcode/qrcode.min.js'), [], '1.0.0', true);
    }
}