<?php

namespace ACPT\Core\Generators\Form\Fields;

use ACPT\Core\Helper\Strings;
use ACPT\Utils\PHP\JSON;
use ACPT\Utils\Wordpress\Translator;

class QRCodeField extends AbstractField
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        $id = Strings::generateRandomId();
        $savedQRCodeValue = $this->QRCodeValue();
        $savedQRCodeValueJson = (!empty($savedQRCodeValue)) ? json_encode($savedQRCodeValue) : null;

        $field  = '<input type="hidden" name="'. Strings::esc_attr($this->getIdName("qr_code_value")).'" id="qr_code_value_'.$id.'" value="'.$savedQRCodeValueJson.'">';
        $field .= "<div class='acpt-qr-code-wrapper' id='acpt-qr-code-wrapper-".$id."'>";
        $field .= $this->renderQRGenerator($id, $savedQRCodeValue);
        $field .= "</div>";

        return $field;
    }

    /**
     * @return array|null
     */
    private function QRCodeValue()
    {
        $savedQRCodeValue = $this->defaultExtraValue('qr_code_value');

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

    /**
     * @param $id
     * @param $savedQRCodeValue
     * @return string
     */
    private function renderQRGenerator($id, $savedQRCodeValue)
    {
        $resolutions = [
            100,
            200,
            300,
            400,
            500,
        ];

        $defaultResolution = $savedQRCodeValue['resolution'] ?? 200;
        $defaultImg = $savedQRCodeValue['img'] ?? "";
        $defaultColorDark = $savedQRCodeValue['colorDark'] ?? "#000000";
        $defaultColorLight = $savedQRCodeValue['colorLight'] ?? "#ffffff";

        $field = "<div class='settings'>";

        // URL
        $field .= "<div>";
        $field .= "<label class='acpt-form-label'>".Translator::translate("Enter the URL")."</label>";
        $field .= "<input
					id='".Strings::esc_attr($this->getIdName())."'
					name='".Strings::esc_attr($this->getIdName())."'
					placeholder='".Translator::translate("example: https://google.com")."'
					value='".$this->defaultValue()."'
					type='url'
					class='url ".$this->cssClass()."'
				/>";
        $field .= "</div>";

        // Resolution
        $field .= "<div>";
        $field .= "<label class='acpt-form-label'>".Translator::translate("Resolution")."</label>";
        $field .= "<select class='acpt-admin-meta-field-input resolution'>";

        foreach ($resolutions as $resolution){
            $selected = ($resolution == $defaultResolution) ? 'selected="selected"' : '';
            $field .= "<option ".$selected." value='".$resolution."'>".$resolution."x".$resolution."</option>";
        }

        $field .= "</select>";
        $field .= "</div>";

        // Colors
        $field .= "<div class='colors'>";
        $field .= "<div>
            <label class='acpt-form-label'>".Translator::translate("Color light")."</label>
                <div class='acpt-color-picker'>
                    <input class='color-light' type='color' value='".$defaultColorLight."'/>
                    <span class='color_val'>".$defaultColorLight."</span>
                </div>
            </div>";

        $field .= "<div>
            <label class='acpt-form-label'>".Translator::translate("Color dark")."</label>
                <div class='acpt-color-picker'>
                    <input class='acpt-color-picker color-dark' type='color' value='".$defaultColorDark."'/>
                    <span class='color_val'>".$defaultColorDark."</span>
                </div>
            </div>";
        $field .= "</div>";

        // Clear button
        $field .= "<a href='#' class='clear-qr-code'>".Translator::translate("Clear")."</a>";
        $field .= "</div>";

        // Image
        $field .= "<div class='acpt-qr-code' id='acpt-qr-code-".$id."'>";

        if(!empty($defaultImg)){
            $field .= '<img src="'.$defaultImg.'" />';
        }

        $field .= "</div>";

        return $field;
    }

    /**
     * @inheritDoc
     */
    public function enqueueFieldAssets()
    {
        wp_enqueue_script( 'qrcodejs', plugins_url( 'advanced-custom-post-type/assets/vendor/qrcode/qrcode.min.js'), [], '1.0.0', true);
    }
}
