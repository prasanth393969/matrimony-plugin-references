<?php

namespace ACPT\Integrations\ElementorPro\Tags;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Email;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\Wordpress\WPAttachment;
use Elementor\Modules\DynamicTags\Module;

class ACPTUrlTag extends ACPTAbstractTag
{
	/**
	 * @inheritDoc
	 */
	public function get_categories()
	{
		return [
			Module::URL_CATEGORY,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_name()
	{
		return 'acpt-url';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return esc_html__( "ACPT URL field", ACPT_PLUGIN_NAME );
	}

	public function render()
	{
		$render = '';
		$field = $this->extractField();

		if(!empty($field)){
            $rawData = $this->getRawData();

            $after = $rawData['after'];
            $before = $rawData['before'];
            $value = $rawData['value'];

            $fieldType = $field['fieldType'];

            switch ($fieldType){

                case MetaFieldModel::AUDIO_TYPE:
                    if($value instanceof WPAttachment){
                        $render .= $value->getSrc();
                    }
                    break;

                case MetaFieldModel::QR_CODE_TYPE:

                    if(isset($value['url'])){
                        $render .= $value['url'];
                    }

                    break;

                case MetaFieldModel::EMBED_TYPE:
                    $render .= $value;
                    break;

                case MetaFieldModel::EMAIL_TYPE:
                    if(!empty($value) and is_string($value)){
                        $render .= 'mailto:'.Email::sanitize($value);
                    }
                    break;

                case MetaFieldModel::PHONE_TYPE:
                    if(!empty($value) and is_array($value) and isset($value['value'])){
                        $val = $value['value'];
                        $dial = $value['dial'] ?? null;
                        $render .= Phone::format($val, $dial, Phone::FORMAT_RFC3966);
                    }

                    break;

                case MetaFieldModel::URL_TYPE:
                    if(!empty($value) and is_array($value) and isset($value['url'])){
                        $render .= $value['url'];
                    }
                    break;
            }
		}

		echo $render;
	}
}