<?php

namespace ACPT\Integrations\Elementor\Widgets;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Currencies;
use ACPT\Core\Helper\Lengths;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Weights;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Shortcodes\ACPT\OptionPageMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\PostMetaShortcode;
use ACPT\Utils\PHP\Barcode;
use ACPT\Utils\PHP\Date;
use ACPT\Utils\PHP\Email;
use ACPT\Utils\PHP\ImageSlider;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\Wordpress\WPAttachment;
use ACPT\Utils\Wordpress\WPUtils;

class WidgetRender
{
	/**
	 * @param MetaFieldModel $fieldModel
	 * @param $settings
	 *
	 * @return mixed|string|null
	 * @throws \Exception
	 */
	public static function render(MetaFieldModel $fieldModel, $settings)
	{
		$context = null;
		$contextId = null;

        $belongsTo = $fieldModel->getBelongsToLabel();

        switch ($belongsTo){
            case BelongsTo::PARENT_POST_ID:
            case BelongsTo::POST_ID:
            case MetaTypes::CUSTOM_POST_TYPE:
            case BelongsTo::POST_TAX:
            case BelongsTo::POST_CAT:
            case BelongsTo::POST_TEMPLATE:
                $context = 'post_id';
                $contextId = (isset($_GET['post']) and get_post_type($_GET['post']) !== 'elementor_library') ? $_GET['post'] : null;

                if($contextId === null){
                    global $post;
                    $contextId = $post->ID;
                }

                break;

            case MetaTypes::OPTION_PAGE:
                $context = 'option_page';
                $contextId = $fieldModel->getFindLabel();
                break;
        }

		if($context === null and $contextId === null){
			return null;
		}

		$box = Strings::esc_attr($fieldModel->getBox()->getName());
		$field = Strings::esc_attr($fieldModel->getName());
		$width = (isset($settings['acpt_width'])) ? $settings['acpt_width'] : null;
		$height = (isset($settings['acpt_height'])) ? $settings['acpt_height'] : null;
		$elements = (isset($settings['acpt_elements'])) ? $settings['acpt_elements'] : null;
		$target = (isset($settings['acpt_target'])) ? $settings['acpt_target'] : null;
		$dateFormat = (isset($settings['acpt_dateformat'])) ? $settings['acpt_dateformat'] : null;
		$timeFormat = (isset($settings['acpt_timeformat'])) ? $settings['acpt_timeformat'] : null;
		$render = (isset($settings['acpt_render'])) ? $settings['acpt_render'] : null;
		$repeaterTemplate = (isset($settings['acpt_repeater'])) ? $settings['acpt_repeater'] : null;
		$repeaterWrapper = (isset($settings['acpt_wrapper'])) ? $settings['acpt_wrapper'] : 'div';
		$cssRepeaterWrapper = (isset($settings['acpt_css'])) ? $settings['acpt_css'] : '';
		$block = (isset($settings['acpt_block'])) ? $settings['acpt_block'] : null;
		$sort = (isset($settings['acpt_sort'])) ? $settings['acpt_sort'] : null;
		$phoneFormat = (isset($settings['acpt_phone_format'])) ? $settings['acpt_phone_format'] : null;

		if($fieldModel->getType() === MetaFieldModel::REPEATER_TYPE){
			return self::renderRepeater(
				$context,
				$contextId,
				$box,
				$field,
				$repeaterWrapper,
				$cssRepeaterWrapper,
				$repeaterTemplate
			);
		}

		if($fieldModel->getType() === MetaFieldModel::FLEXIBLE_CONTENT_TYPE){
			return self::renderFlexible(
				$context,
				$contextId,
				$box,
				$field,
				$repeaterWrapper,
				$cssRepeaterWrapper,
				$repeaterTemplate,
				$block
			);
		}

		if (
			$_SERVER['PHP_SELF'] === '/wp-admin/post.php' or
			$_SERVER['PHP_SELF'] === '/wp-admin/admin-ajax.php'
		){
			return self::renderShortcode(
				$context,
				$contextId,
				$box,
				$field,
				$width,
				$height,
				$target,
				$dateFormat,
				$timeFormat,
                $phoneFormat,
				$elements,
				$render,
                $sort
			);
		}

		return self::renderField(
			$context,
			$contextId,
			$box,
			$field,
			$width,
			$height,
			$target,
			$dateFormat,
			$timeFormat,
            $phoneFormat,
			$elements,
			$render,
            $sort
		);
	}

	/**
	 * @param $contextId
	 * @param $context
	 * @param $box
	 * @param $field
	 * @param null $width
	 * @param null $height
	 * @param null $target
	 * @param null $dateFormat
	 * @param null $timeFormat
	 * @param null $phoneFormat
	 * @param null $elements
	 * @param null $render
	 * @param null $sort
	 *
	 * @return mixed|null
	 */
	private static function renderField(
		$context,
		$contextId,
		$box,
		$field,
		$width = null,
		$height = null,
		$target = null,
		$dateFormat = null,
		$timeFormat = null,
        $phoneFormat = null,
		$elements = null,
		$render = null,
        $sort = null
	)
	{
		$payload = [
			$context => $contextId,
			'box_name' => $box,
			'field_name' => $field,
		];

		if($target){
			$payload['target'] = $target;
		}

		if($width){
			$payload['width'] = $width;
		}

		if($height){
			$payload['height'] = $height;
		}

		if($dateFormat){
			$payload['date-format'] = $dateFormat;
		}

		if($timeFormat){
			$payload['time-format'] = $timeFormat;
		}

		if($phoneFormat){
			$payload['phone-format'] = $phoneFormat;
		}

		if($elements){
			$payload['elements'] = $elements;
		}

		if($render){
			$payload['render'] = $render;
		}

		if($sort){
			$payload['sort'] = $sort;
		}

		return acpt_field($payload);
	}

	/**
	 * @param $context
	 * @param $contextId
	 * @param $box
	 * @param $field
	 * @param null $width
	 * @param null $height
	 * @param null $target
	 * @param null $dateFormat
	 * @param null $timeFormat
	 * @param null $phoneFormat
	 * @param null $elements
	 * @param null $render
	 * @param null $sort
	 *
	 * @return string
	 * @throws \Exception
	 */
	private static function renderShortcode(
		$context,
		$contextId,
		$box,
		$field,
		$width = null,
		$height = null,
		$target = null,
		$dateFormat = null,
		$timeFormat = null,
        $phoneFormat = null,
		$elements = null,
		$render = null,
		$sort = null
	)
	{
		$attr = [
			'box' => Strings::esc_attr($box),
			'field' => Strings::esc_attr($field),
			'width' => $width ? $width  : null,
			'height' => $height ? $height  : null,
			'target' => $target ? $target  : null,
			'date-format' => $dateFormat ? $dateFormat  : null,
			'time-format' => $timeFormat ? $timeFormat  : null,
			'phone-format' => $phoneFormat ? $phoneFormat  : null,
			'elements' => $elements ? $elements  : null,
			'render' => $render ? $render  : null,
			'sort' => $sort ? $sort  : null,
		];

		if($context === 'post_id'){
			$attr['pid'] = $contextId;
			$postMetaShortcode = new PostMetaShortcode();

			return $postMetaShortcode->render($attr);
		}

		if ($context === 'option_page'){
			$attr['page'] = $contextId;
			$optionPageMetaShortcode = new OptionPageMetaShortcode();

			return $optionPageMetaShortcode->render($attr);
		}

		return null;
	}

	/**
	 * @param $context
	 * @param $contextId
	 * @param $boxName
	 * @param $fieldName
	 * @param $wrapper
	 * @param $css
	 * @param $template
	 *
	 * @return string|null
	 */
	private static function renderRepeater(
		$context,
		$contextId,
		$boxName,
		$fieldName,
		$wrapper,
		$css,
		$template
	)
	{
		$rawData = get_acpt_field([
			$context => $contextId,
			'box_name' => $boxName,
			'field_name' => $fieldName,
			'with_context' => true,
		]);

		if (empty($rawData)){
			return null;
		}

		$settings = get_acpt_meta_field_object($boxName, $fieldName);
		$render = '<'.$wrapper.' class="'.$css.'">';

		// Extract tags.Allowed syntax:
		// %email:arg1,arg2%
		preg_match_all('/%[a-zA-Z0-9_ \:\,\-\/]+%/', $template, $variables);

		foreach ($rawData as $index => $item) {

			$replace = $template;

			foreach ($variables[0] as $variable){
				$var = self::extractVars($variable);
				$key = $var['key'];
				$args = $var['args'];

				if(isset($item[$key]) and !empty($item[$key])){
					$fieldSettings = array_filter($settings->children, function ($s) use ($key){
						return $s->name === $key;
					});

					if(!empty($fieldSettings)){
						$fieldSettings = array_values($fieldSettings)[0];
						$replace = str_replace($variable, self::replacingValue($item[$key], $fieldSettings, $args), $replace);
					}
				} else {
				    // empty values
                    $replace = str_replace($variable, "", $replace);
                }
			}

			$render .= $replace;
		}

		$render .= '</'.$wrapper.'>';

		return $render;
	}

	/**
	 * @param $context
	 * @param $contextId
	 * @param $boxName
	 * @param $fieldName
	 * @param $wrapper
	 * @param $css
	 * @param $template
	 * @param $block
	 *
	 * @return string
	 */
	private static function renderFlexible(
		$context,
		$contextId,
		$boxName,
		$fieldName,
		$wrapper,
		$css,
		$template,
		$block = null
	)
	{
		if($block === null){
			return null;
		}

		$rawData = get_acpt_block([
			$context => $contextId,
			'box_name' => $boxName,
			'parent_field_name' => $fieldName,
			'block_name' => $block
		]);

		if (empty($rawData)){
			return null;
		}

		$settings = get_acpt_meta_field_object($boxName, $fieldName);
		$render = '<'.$wrapper.' class="'.$css.'">';

		// Extract tags.Allowed syntax:
		// %email:arg1,arg2%
		preg_match_all('/%[a-zA-Z0-9_ \:\,\-\/]+%/', $template, $variables);

		foreach ($rawData as $index => $items) {
			$numberOfFields = count(array_values($items[$block])[0]);

			for($i=0; $i < $numberOfFields; $i++){

				$replace = $template;

				foreach ($variables[0] as $variable){
					$var = self::extractVars($variable);
					$key = $var['key'];
					$args = $var['args'];

                    if(isset($items[$block][$key][$i]) and !empty($items[$block][$key][$i])){
                        $fieldSettings = array_filter($settings->blocks, function ($b) use ($key){

                            $match = 0;

                            foreach ($b->fields as $f){
                                if($f->name === $key){
                                    $match++;
                                }
                            }

                            return $match > 0;
                        });

                        if(!empty($fieldSettings)){
                            $fieldSettings = array_values($fieldSettings)[0];
                            $fieldSettings = array_filter($fieldSettings->fields, function ($s) use ($key){
                                return $s->name === $key;
                            });
                        }

                        if(!empty($fieldSettings)) {
                            $fieldSettings = array_values( $fieldSettings )[0];
                            $field = $items[$block][$key][$i];
                            $replace = str_replace($variable, self::replacingValue($field, $fieldSettings, $args), $replace);
                        }
                    } else {
                        $replace = str_replace($variable, "", $replace);
                    }
				}

				$render .= $replace;
			}
		}

		$render .= '</'.$wrapper.'>';

		return $render;
	}

	/**
	 * @param string $variable
	 *
	 * @return array
	 */
	private static function extractVars($variable)
	{
		$var = str_replace("%", "", $variable);
		$pos = strpos($var,":");

		if($pos === false){
			return [
				'key' => $var,
				'args' => [],
			];
		}

		$varLen = strlen($var);

		$key = '';
		$vars = '';

		for($i = 0; $i < $pos; $i++){
			$key .= $var[$i];
		}

		for($i = ($pos+1); $i < $varLen; $i++){
			$vars .= $var[$i];
		}

		$args = explode(",", $vars);

		return [
			'key' => $key,
			'args' => $args,
		];
	}

    /**
     * @param mixed     $rawValue
     * @param \stdClass $fieldSettings
     * @param array     $args
     *
     * @return string
     */
	private static function replacingValue($rawValue, $fieldSettings, $args = [])
	{
	    if(empty($rawValue)){
	        return null;
        }

        if(!isset($rawValue['value'])){
            return null;
        }

        $value = $rawValue['value'] ?? null;
        $after = $rawValue['after'] ?? null;
        $before = $rawValue['before'] ?? null;

        if(empty($value)){
            return null;
        }

        try {
            $fieldType = $fieldSettings->type;

            switch ($fieldType){

                // BARCODE_TYPE
                case MetaFieldModel::BARCODE_TYPE:
                    return Barcode::render($value);

                // QR_CODE_TYPE
                case MetaFieldModel::QR_CODE_TYPE:
                    return QRCode::render($value);

				// RATING_TYPE
				case MetaFieldModel::RATING_TYPE:
					if(!empty($args) and $args[0] === 'stars'){
						return Strings::renderStars($value);
					}

					return ($value/2)."/5";

				// CURRENCY_TYPE
				case MetaFieldModel::CURRENCY_TYPE:
					if(!is_array($value)){
						return null;
					}

					if(!isset($value['amount'])){
						return null;
					}

					if(!isset($value['unit'])){
						return null;
					}

					$symbol = (isset(Currencies::getList()[$value['unit']])) ? Currencies::getList()[$value['unit']]['symbol'] : $value['unit'];

					return $before . $value['amount'] . " ". $symbol . $after;

				// DATE_RANGE_TYPE
				case MetaFieldModel::DATE_RANGE_TYPE:

                    if(!is_array($value)){
                        return '';
                    }

                    if(!isset($value['value'])){
                        return '';
                    }

                    if(!isset($value['object'])){
                        return '';
                    }

                    $val = $value['value'];
                    $dateTimeObject = $value['object'];

					if(is_string($val)){
                        $val = explode(" - ", $val);
					}

					if(is_array($val) and !empty($val) and count($val) === 2){
						$format = get_option( 'date_format' ) ? get_option( 'date_format' ) : 'Y-m-d';

						if(!empty($args)){
							foreach ($args as $arg){
								if(Date::isDateFormatValid($arg)){
									$format = $arg;
									break;
								}
							}
						}

						if(!$dateTimeObject[0] instanceof \DateTime){
						    return '';
                        }

                        if(!$dateTimeObject[1] instanceof \DateTime){
                            return '';
                        }

                        $value  = $before;
						$value .= Date::format($format, $dateTimeObject[0]);
						$value .= ' - ';
						$value .= Date::format($format, $dateTimeObject[1]);
                        $value .= $after;

						return $value;
					}

					return null;

				// DATE_TYPE
				case MetaFieldModel::DATE_TYPE:

                    if(!is_array($value)){
                        return '';
                    }

                    if(!isset($value['value'])){
                        return '';
                    }

                    if(!isset($value['object'])){
                        return '';
                    }

                    $val = $value['value'];
                    $dateTimeObject = $value['object'];

					$format = get_option( 'date_format' ) ? get_option( 'date_format' ) : 'Y-m-d';
					if(!empty($args)){
						foreach ($args as $arg){
							if(Date::isDateFormatValid($arg)){
								$format = $arg;
								break;
							}
						}
					}

					return $before . Date::format($format, $dateTimeObject) . $after;

				// DATE_TIME_TYPE
				case MetaFieldModel::DATE_TIME_TYPE:

                    if(!is_array($value)){
                        return '';
                    }

                    if(!isset($value['value'])){
                        return '';
                    }

                    if(!isset($value['object'])){
                        return '';
                    }

                    $val = $value['value'];
                    $dateTimeObject = $value['object'];

					$dateFormat = get_option( 'date_format' ) ? get_option( 'date_format' ) : 'Y-m-d';
					$timeFormat = get_option( 'time_format' ) ? get_option( 'time_format' ) : "G:i";
					$format = $dateFormat . ' ' . $timeFormat;

					if(!empty($args)){
						$formats = [];

						foreach ($args as $arg){
							if(Date::isDateFormatValid($arg)){
								$formats[] = $arg;
							}
						}

						if(!is_array($formats)){
							return null;
						}

						$format = implode(" ", $formats);
					}

					return $before . Date::format($format, $dateTimeObject) . $after;

				// TIME_TYPE
				case MetaFieldModel::TIME_TYPE:

                    if(!is_array($value)){
                        return '';
                    }

                    if(!isset($value['value'])){
                        return '';
                    }

                    if(!isset($value['object'])){
                        return '';
                    }

                    $val = $value['value'];
                    $dateTimeObject = $value['object'];

					$format = get_option( 'time_format' ) ? get_option( 'time_format' ) : "G:i";
					if(!empty($args)){
						foreach ($args as $arg){
							if(Date::isDateFormatValid($arg)){
								$format = $arg;
								break;
							}
						}
					}

					return $before . Date::format($format, $dateTimeObject) . $after;

				// EMAIL_TYPE
				case MetaFieldModel::EMAIL_TYPE:
					if(!empty($args) and $args[0] === 'link'){
						return '<a href="mailto:'.Email::sanitize($value).'">'.$before . $value . $after.'</a>';
					}

					return $value;

				// EMBED_TYPE
				case MetaFieldModel::EMBED_TYPE:

					$width = 2000;
					$height = 700;

					if(!empty($args)){
						$width = (isset($args[0])) ? $args[0] : $width;
						$height = (isset($args[1])) ? $args[1] : $height;
					}

					return (new \WP_Embed())->shortcode([
						'width' => $width,
						'height' => $height,
					], Strings::esc_attr($value));

				// LENGTH_TYPE
				case MetaFieldModel::LENGTH_TYPE:

					if(!is_array($value)){
						return null;
					}

					if(!isset($value['length'])){
						return null;
					}

					if(!isset($value['unit'])){
						return null;
					}

					$symbol = (isset(Lengths::getList()[$value['unit']])) ? Lengths::getList()[$value['unit']]['symbol'] : $value['unit'];

					return $before . $value['length'] . " ". $symbol . $after;

				// IMAGE_TYPE
				case MetaFieldModel::IMAGE_TYPE:
					if($value instanceof WPAttachment and $value->isImage()){
						return $value->render();
					}

					return $value;

				// CHECKBOX_TYPE
				// SELECT_MULTI_TYPE
				case MetaFieldModel::SELECT_MULTI_TYPE:
				case MetaFieldModel::CHECKBOX_TYPE:
					if(!is_array($value)){
						return null;
					}

					$separator = ", ";
					if(!empty($args) and !empty($args[0])){
						$separator = $args[0];
					}

					if(!is_array($value)){
						return null;
					}

					return implode($separator, $value);

				// VIDEO_TYPE
				case MetaFieldModel::VIDEO_TYPE:
					if($value instanceof WPAttachment and $value->isVideo()){

						$width = "100%";
						$height = null;

						if(!empty($args)){
							$width = (isset($args[0])) ? $args[0] : $width;
							$height = (isset($args[1])) ? $args[1] : $height;
						}

						return $value->render([
						    'w' => $width,
						    'h' => $height,
                        ]);
					}

					return $value;

                // IMAGE_SLIDER_TYPE
                case MetaFieldModel::IMAGE_SLIDER_TYPE:

                    if(!is_array($value)){
                        return null;
                    }

                    if(count($value) > 2){
                        return null;
                    }

                    $width = (isset($args[0])) ? $args[0] : null;
                    $height = (isset($args[1])) ? $args[1] : null;
                    $defaultPercent = (isset($fieldSettings->advanced_options[41]) and !empty($fieldSettings->advanced_options[41])) ? $fieldSettings->advanced_options[41]->value : 50;

                    return ImageSlider::render($value, $defaultPercent, $width, $height);

				// GALLERY_TYPE
				case MetaFieldModel::GALLERY_TYPE:

					if(!is_array($value)){
						return null;
					}

					if(empty($value)){
						return null;
					}

					$columns = 4;
					if(!empty($args) and is_numeric($args[0])){
						$columns = $args[0];
					}

					$gallery = "<div class='acpt-gallery mosaic per-row-".$columns."'>";

					foreach ($value as $image){
						if($image instanceof WPAttachment and $image->isImage()){
							$gallery .= $image->render();
						}
					}

					$gallery .= "</div>";

					return $gallery;

				// PHONE_TYPE
				case MetaFieldModel::PHONE_TYPE:

                    if(!is_array($value)){
                        return null;
                    }
				    if(!isset($value['value'])){
				        return null;
                    }

                    $val = $value['value'];
                    $dial = $value['dial'] ?? null;

                    $allowed = [
                        Phone::FORMAT_ORIGINAL,
                        Phone::FORMAT_E164,
                        Phone::FORMAT_INTERNATIONAL,
                        Phone::FORMAT_NATIONAL,
                    ];

                    $format = (isset($args[0]) and in_array($args[0], $allowed)) ? $args[0] : Phone::FORMAT_E164;
                    $value = Phone::format($val, $dial, $format);

					if(!empty($args) and $args[1] === 'link'){
						return '<a href="'.Phone::format($val, $dial, Phone::FORMAT_RFC3966).'">'. $before . $value . $after .'</a>';
					}

					return $before . $value . $after;

				// TABLE_TYPE
				case MetaFieldModel::TABLE_TYPE:

					if(is_string($value) and Strings::isJson($value)){
						$generator = new TableFieldGenerator($value);

						return $generator->generate();
					}

					return null;

				// TEXTAREA_TYPE
				case MetaFieldModel::TEXTAREA_TYPE:

					if(!is_string($value)){
						return null;
					}

					return $before . WPUtils::renderShortCode($value, true) . $after;

				// WEIGHT_TYPE
				case MetaFieldModel::WEIGHT_TYPE:
					if(!is_array($value)){
						return null;
					}

					if(!isset($value['weight'])){
						return null;
					}

					if(!isset($value['unit'])){
						return null;
					}

					$symbol = (isset(Weights::getList()[$value['unit']])) ? Weights::getList()[$value['unit']]['symbol'] : $value['unit'];

					return $before . $value['weight'] . " ". $symbol . $after;

				// URL_TYPE
				case MetaFieldModel::URL_TYPE:
					if(!is_array($value)){
						return null;
					}

					if(!isset($value['url'])){
						return null;
					}

					$url = $value['url'];
					$label = (isset($value['label'])) ? $value['label'] : $url;

					if(!empty($args) and $args[0] === 'link'){
						return '<a href="'.$url.'">'.$before . $label . $after .'</a>';
					}

					return $before . $url . $after;

				default:
					return $before . $value . $after;
			}
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}
