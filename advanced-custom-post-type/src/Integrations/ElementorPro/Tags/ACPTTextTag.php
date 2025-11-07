<?php

namespace ACPT\Integrations\ElementorPro\Tags;

use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\PHP\Barcode;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\Wordpress\WPAttachment;
use Elementor\Modules\DynamicTags\Module;

class ACPTTextTag extends ACPTAbstractTag
{
	/**
	 * @inheritDoc
	 */
	public function get_categories()
	{
		return [
			Module::TEXT_CATEGORY,
            Module::POST_META_CATEGORY,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_name()
	{
		return 'acpt-text';
	}

	/**
	 * @inheritDoc
	 */
	public function get_title()
	{
		return esc_html__( "ACPT text field", ACPT_PLUGIN_NAME );
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

		    if(empty($value)){
		        return $render;
            }

            $fieldType = $field['fieldType'];

            switch ($fieldType){

                // POST_TYPE
                // POST_OBJECT_TYPE
                // POST_OBJECT_MULTI_TYPE
                case MetaFieldModel::POST_TYPE:
                case   MetaFieldModel::POST_OBJECT_MULTI_TYPE:
                case MetaFieldModel::POST_OBJECT_TYPE:

                    $posts = [];

                    if($value instanceof \WP_Post){
                        $posts[] = $value->post_title;
                    } elseif(is_array($value)){
                        foreach ($value as $post){
                            if($post instanceof \WP_Post){
                                $posts[] = $post->post_title;
                            }
                        }
                    }

                    $render .= implode(", ", $posts);

                    break;

                // ADDRESS_TYPE
                case MetaFieldModel::ADDRESS_TYPE:
                    if(isset($value['address']) and !empty($value['address'])){
                        $render .= $before . $value['address'] . $after;
                    }
                    break;

                // AUDIO_TYPE
                case MetaFieldModel::AUDIO_TYPE:
                    if($value instanceof WPAttachment){
                        $render .= $before . $value->getTitle() . $after;
                    }
                    break;

                // BARCODE_TYPE
                case MetaFieldModel::BARCODE_TYPE:
                    if(!empty($value)){
                        $render .= $before . Barcode::render($value) . $after;
                    }
                    break;

                // QR_CODE_TYPE
                case MetaFieldModel::QR_CODE_TYPE:
                    if(!empty($value)){
                        $render .= $before . QRCode::render($value) . $after;
                    }
                    break;

                // RATING_TYPE
                case MetaFieldModel::RATING_TYPE:
                    if(!empty($value)){
                        $render .= $before . ($value/2) . "/5" . $after;
                    }
                    break;

                // CHECKBOX_TYPE
                // SELECT_MULTI_TYPE
                case MetaFieldModel::CHECKBOX_TYPE:
                case MetaFieldModel::SELECT_MULTI_TYPE:
                    if(!empty($value) and is_array($value)){
                        $render .= $before . implode(",", $value) . $after;
                    }
                    break;

                // PHONE_TYPE
                case MetaFieldModel::PHONE_TYPE:
                    if(!empty($value) and is_array($value) and isset($value['value'])){
                        $dial = $value['dial'] ?? null;
                        $val = $value['value'] ?? null;
                        $format = Phone::FORMAT_E164;

                        if($val !== null){
                            $render .= $before . Phone::format($val, $dial, $format) . $after;
                        }
                    }
                    break;

                // COUNTRY_TYPE
                case MetaFieldModel::COUNTRY_TYPE:
                    if(!empty($value) and is_array($value) and isset($value['value'])){
                        $render .= $before . $value['value'] . $after;
                    }
                    break;

                // TABLE_TYPE
                case MetaFieldModel::TABLE_TYPE:
                    if(is_string($value) and Strings::isJson($value)){
                        $generator = new TableFieldGenerator($value);
                        $render .= $generator->generate();
                    }
                    break;

                // URL_TYPE
                case MetaFieldModel::URL_TYPE:
                    if(!empty($value) and is_array($value) and isset($value['url'])){
                        $render .= $value['label'] ? $before . $value['label'] . $after : $value['url'];
                    }
                    break;

                default:

                    // Fix for Numeric fields
                    if(is_numeric($value)){
                        $value = (string)$value;
                    }

                    if(is_string($value)){
                        $render .= $before . $value . $after;
                    }
                    break;
            }
        }

		echo $render;
	}
}
