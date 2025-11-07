<?php

namespace ACPT\Integrations\Oxygen\Provider;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Integrations\Oxygen\Provider\Helper\OxygenDataKey;
use ACPT\Utils\PHP\Barcode;
use ACPT\Utils\PHP\Country;
use ACPT\Utils\PHP\Email;
use ACPT\Utils\PHP\Phone;
use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Wordpress\WPAttachment;

class OxygenDataProvider
{
	/**
	 * @param $dynamicData
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function initDynamicData($dynamicData)
	{
		$fields = [];

		// add custom post type fields
		$customPostTypes = CustomPostTypeRepository::get([]);
		foreach ($customPostTypes as $customPostType){
			$fields = array_merge( $fields,  get_acpt_meta_field_objects(MetaTypes::CUSTOM_POST_TYPE, true, $customPostType->getName()));
		}

		// add taxonomy fields
		$taxonomies = TaxonomyRepository::get([]);
		foreach ($taxonomies as $taxonomy){
			$fields = array_merge( $fields,  get_acpt_meta_field_objects(MetaTypes::TAXONOMY, true, $taxonomy->getSlug()));
		}

		// add option page fields
		$optionPages = OptionPageRepository::get([]);
		foreach ($optionPages as $optionPage){
			$fields = array_merge( $fields,  get_acpt_meta_field_objects(MetaTypes::OPTION_PAGE, true, $optionPage->getMenuSlug()));

			foreach ($optionPage->getChildren() as $childPage){
				$fields = array_merge( $fields,  get_acpt_meta_field_objects(MetaTypes::OPTION_PAGE, true, $childPage->getMenuSlug()));
			}
		}

		// Generate the settings for each field type
		$allOptions = array_reduce( $fields, [$this, "addButton"], [] );

		if( count( $allOptions ) > 0 ) {
			array_unshift( $allOptions, [
				'name' => __( 'Select the ACPT meta field', 'oxygen-acpt' ),
				'type' => 'heading'
			] );

			$dynamicData[] = [
				'name'       => __( 'ACPT Field', 'oxygen-acpt' ),
				'mode'       => 'content',
				'position'   => 'Post',
				'data'       => 'acpt_content',
				'handler'    => [$this, 'dynamicDataContentHandler'],
				'properties' => $allOptions,
			];
		}

		$optionsForUrl = array_reduce( $fields, [$this, "addUrlButton" ], [] );

		if( count( $optionsForUrl ) > 0 ) {
			$image = [
				'name' => __( 'ACPT field', 'oxygen-acpt' ),
				'mode' => 'image',
				'position' => 'Post',
				'data' => 'acpt_image',
				'handler' => [$this, 'dynamicDataUrlHandler'],
				'properties' => $optionsForUrl
			];

			$dynamicData[] = $image;

			$link = [
				'name' => __( 'ACPT field', 'oxygen-acpt' ),
				'mode' => 'link',
				'position' => 'Post',
				'data' => 'acpt_link',
				'handler' => [$this, 'dynamicDataUrlHandler'],
				'properties' => $optionsForUrl
			];

			$dynamicData[] = $link;

			$customField = [
				'name' => __( 'ACPT field', 'oxygen-acpt' ),
				'mode' => 'custom-field',
				'position' => 'Post',
				'data' => 'acpt_custom_field',
				'handler' => [$this, 'dynamicDataUrlHandler'],
				'properties' => $optionsForUrl
			];

			$dynamicData[] = $customField;
		}

		$optionsForImageId = array_reduce( $fields, [$this, "addImageIdButton"], [] );

		if( count( $optionsForImageId ) > 0 ) {
			$imageIdField = [
				'name' => __( 'ACPT field', 'oxygen' ),
				'mode' => 'image-id',
				'position' => 'Post',
				'data' => 'acpt_image_id',
				'handler' => [$this, 'dynamicDataImageIdHandler'],
				'properties' => $optionsForImageId
			];

			$dynamicData[] = $imageIdField;
		}

		return $dynamicData;
	}

	/**
	 * @param array $result
	 * @param \stdClass $fieldObject
	 *
	 * @return array
	 */
	public function addButton( $result, $fieldObject )
	{
		$invalidFieldTypes = [
			MetaFieldModel::CLONE_TYPE,
			MetaFieldModel::REPEATER_TYPE,
			MetaFieldModel::FLEXIBLE_CONTENT_TYPE,
			MetaFieldModel::POST_TYPE,
			MetaFieldModel::POST_OBJECT_MULTI_TYPE,
			MetaFieldModel::POST_OBJECT_TYPE,
			MetaFieldModel::TERM_OBJECT_MULTI_TYPE,
			MetaFieldModel::TERM_OBJECT_TYPE,
			MetaFieldModel::USER_MULTI_TYPE,
			MetaFieldModel::USER_TYPE,
		];

		$settingsPage = (isset($fieldObject->option_page)) ? $fieldObject->option_page : null;

		$properties = [];

		// $properties
		switch ($fieldObject->type){

			case MetaFieldModel::COUNTRY_TYPE:
				$properties[] = [
					'name'      => __( 'Please select what you want to insert', 'oxygen-acpt' ),
					'data'      => 'country_render',
					'type'      => 'select',
					'options'   => [
						__( 'Plain text', 'oxygen-acpt' ) =>'text',
						__( 'Flag', 'oxygen-acpt' ) => 'flag',
						__( 'Flag and text', 'oxygen-acpt' ) => 'full',
					],
					'nullval'   => 'text'
				];
				break;

			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:
			case MetaFieldModel::LIST_TYPE:
				$properties[] = [
					'name'      => __( 'How the list should be displayed?', 'oxygen-acpt' ),
					'data'      => 'list_type',
					'type'      => 'select',
					'options'   => [
						__( 'List', 'oxygen-acpt' ) =>'list',
						__( 'Comma separated', 'oxygen-acpt' ) => 'comma_separated',
					],
					'nullval'   => 'list'
				];
				break;

			case MetaFieldModel::DATE_RANGE_TYPE:
			case MetaFieldModel::DATE_TYPE:
				if( !isset( $label ) ) $label = __( 'PHP Date Format. Defaults to Y-m-d', 'oxygen-acpt' );
				$properties[] = [
					'name' => $label,
					'data' => 'format',
					'type' => 'text'
				];
				break;

			case MetaFieldModel::DATE_TIME_TYPE:
				if( !isset( $label ) ) $label = __( 'PHP Date Format. Defaults to Y-m-d', 'oxygen-acpt' );
				$properties[] = [
					'name' => $label,
					'data' => 'date_format',
					'type' => 'text'
				];

				if( !isset( $label ) ) $label = __( 'PHP Time Format. Defaults to H:i:s', 'oxygen-acpt' );
				$properties[] = [
					'name' => $label,
					'data' => 'time_format',
					'type' => 'text',
				];
				break;

			case MetaFieldModel::EMAIL_TYPE:
				$properties[] = [
					'name'      => __( 'Please select what you want to insert', 'oxygen-acpt' ),
					'data'      => 'insert_type',
					'type'      => 'select',
					'options'   => [
						__( 'Email link', 'oxygen-acpt' ) =>'email_link',
						__( 'Email URL', 'oxygen-acpt' ) => 'email_url',
					],
					'nullval'   => 'email_link'
				];
				break;

			case MetaFieldModel::EMBED_TYPE:
				$properties[] = [
					'name' => __( 'Width', 'oxygen-acpt' ),
					'data' => 'width',
					'type' => 'text',
				];
				$properties[] = [
					'name' => __( 'Height', 'oxygen-acpt' ),
					'data' => 'height',
					'type' => 'text',
				];
				break;

			case MetaFieldModel::FILE_TYPE:
			case MetaFieldModel::URL_TYPE:
				$properties[] = [
					'name'      => __( 'Please select the target link', 'oxygen-acpt' ),
					'data'      => 'target_link',
					'type'      => 'select',
					'options'   => [
						__( 'Opens in a new window or tab', 'oxygen-acpt' ) =>'_blank',
						__( 'Opens in the same frame as it was clicked', 'oxygen-acpt' ) => '_self',
						__( 'Opens in the parent frame', 'oxygen-acpt' ) => '_parent',
						__( 'Opens in the full body of the window', 'oxygen-acpt' ) => '_top',
					],
					'nullval'   => '_blank'
				];
				break;

			case MetaFieldModel::GALLERY_TYPE:
				$properties[] = [
					'name' => __( 'Output type', 'oxygen-acpt' ),
					'data' => 'output_type',
					'type' => 'select',
					'options'=> [
						__( 'Images ID list', 'oxygen-acpt' ) => 'images_id_list',
						__( 'Gallery', 'oxygen-acpt' ) => 'gallery',
					],
					'nullval' => 'gallery'
				];
				$properties[] = [
					'name' => __( 'Elements per row', 'oxygen-acpt' ),
					'data' => 'per_row',
					'type' => 'select',
					'options'=> [
						"1" => "1",
						"2" => "2",
						"3" => "3",
						"4" => "4",
						"6" => "6",
					],
					'show_condition' => 'dynamicDataModel.output_type == \'gallery\''
				];
				$properties[] = [
					'name' => __( 'Separator', 'oxygen-acpt' ),
					'data' => 'separator',
					'type' => 'text',
					'show_condition' => 'dynamicDataModel.output_type == \'images_id_list\''
				];
				break;

			case MetaFieldModel::IMAGE_TYPE:
				$properties[] = [
					'name'     => __( 'Please select what you want to insert', 'oxygen-acpt' ),
					'data'      => 'insert_type',
					'type'      => 'select',
					'options'   => [
						__( 'Image element', 'oxygen-acpt' ) =>'image_element',
						__( 'Image URL', 'oxygen-acpt' ) => 'image_url',
						__( 'Image Title', 'oxygen-acpt' ) => 'image_title',
						__( 'Image Caption', 'oxygen-acpt' ) => 'image_caption'
					],
					'nullval'   => 'image_element'
				];
				$properties[] = [
					'name'=> __( 'Size', 'oxygen-acpt' ),
					'data'=> 'size',
					'type'=> 'select',
					'options'=> [
						__( 'Thumbnail', 'oxygen-acpt' ) => 'thumbnail',
						__( 'Medium', 'oxygen-acpt' ) => 'medium',
						__( 'Medium Large', 'oxygen-acpt' ) => 'medium_large',
						__( 'Large', 'oxygen-acpt' ) => 'large',
						__( 'Original', 'oxygen-acpt' ) => 'full'
					],
					'nullval' => 'medium',
					'change'=> 'scope.dynamicDataModel.width = ""; scope.dynamicDataModel.height = ""',
					'show_condition' => "dynamicDataModel.insert_type == 'image_element'"
				];
				$properties[] = [
					'name' => __( 'or', 'oxygen-acpt' ),
					'type' => 'label',
					'show_condition' => 'dynamicDataModel.insert_type == \'image_element\''
				];
				$properties[] = [
					'name' => __( 'Width', 'oxygen-acpt' ),
					'data' => 'width',
					'type' => 'text',
					'helper'=> true,
					'change' => "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					'show_condition' => "dynamicDataModel.insert_type == 'image_element'"
				];
				$properties[] = [
					'name' => __( 'Height', 'oxygen-acpt' ),
					'data' => 'height',
					'type' => 'text',
					'helper' => true,
					'change' => "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					'show_condition' => 'dynamicDataModel.insert_type == \'image_element\''
				];
				break;

			case MetaFieldModel::PHONE_TYPE:
				$properties[] = [
					'name'      => __( 'How the phone should be displayed?', 'oxygen-acpt' ),
					'data'      => 'phone_type',
					'type'      => 'select',
					'options'   => [
						__( 'Link', 'oxygen-acpt' ) =>'link',
						__( 'Text', 'oxygen-acpt' ) => 'text',
					],
					'nullval'   => 'text'
				];
				break;

			case MetaFieldModel::VIDEO_TYPE:
				$properties[] = [
					'name'     => __( 'Please select what you want to insert', 'oxygen-acpt' ),
					'data'      => 'insert_type',
					'type'      => 'select',
					'options'   => [
						__( 'Video element', 'oxygen-acpt' ) =>'video_element',
						__( 'Video URL', 'oxygen-acpt' ) => 'video_url',
						__( 'Video Title', 'oxygen-acpt' ) => 'video_title',
						__( 'Video Caption', 'oxygen-acpt' ) => 'video_caption'
					],
					'nullval'   => 'video_element'
				];
				$properties[] = [
					'name' => __( 'Width', 'oxygen-acpt' ),
					'data' => 'width',
					'type' => 'text',
					'change' => "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					'show_condition' => "dynamicDataModel.insert_type == 'video_element'"
				];
				$properties[] = [
					'name' => __( 'Height', 'oxygen-acpt' ),
					'data' => 'height',
					'type' => 'text',
					'change' => "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					'show_condition' => 'dynamicDataModel.insert_type == \'video_element\''
				];
				break;

			default:
				$properties[] = [
					'name' => __( 'Include prepend and append text (if configured)', 'oxygen-acpt' ),
					'data' => 'include_prepend_append',
					'type' => 'checkbox',
					'value' => 'yes'
				];
				break;
		}

		if( !in_array( $fieldObject->type, $invalidFieldTypes ) ) {

            $originalId = (!empty($fieldObject->forgedBy)) ? $fieldObject->id : null;

			$args = [
				'name' => '['.$fieldObject->findLabel . '] ' . $fieldObject->boxName . ' ' . $fieldObject->name,
				'data' => OxygenDataKey::encode($fieldObject->belongsToLabel, $fieldObject->findLabel, $fieldObject->boxName, $fieldObject->name, $originalId),
				'type' => 'button',
				'properties' => $properties,
			];

			if($settingsPage !== null){
				$args['settings_page'] = $settingsPage;
			}

			$result[] = $args;
		}

		return $result;
	}

	/**
	 * This function returns the meta field output
	 *
	 * @param $atts
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	public function dynamicDataContentHandler($atts)
	{
		global $wpdb;

		$rawMetaValue = $this->getRawMetaValue($atts);

		if($rawMetaValue === null){
			return null;
		}

		$fieldObject = $rawMetaValue['fieldObject'];
		$rawValue =  $rawMetaValue['rawValue'];

		if(empty($rawValue) or $rawValue === null){
			return null;
		}

		if(empty($fieldObject)){
		    return null;
        }

		switch ($fieldObject->type){

			// COUNTRY_TYPE
			case MetaFieldModel::COUNTRY_TYPE:
				if(!is_array($rawValue)){
					return null;
				}

				if(!isset($rawValue['country'])){
					return null;
				}

				if(!isset($rawValue['value'])){
					return null;
				}

				$render = isset($atts['country_render']) ? $atts['country_render'] : 'text';

				$countryName = $rawValue['value'];
				$countryCode = $rawValue['country'];

				if($render === 'flag'){
					return Country::getFlag($countryCode);
				}

				if($render === 'full'){
					return Country::fullFormat($countryCode, $countryName);
				}

				return $countryName;

			// TABLE_TYPE
			case MetaFieldModel::TABLE_TYPE:
				if(is_string($rawValue) and Strings::isJson($rawValue)){
					$generator = new TableFieldGenerator($rawValue);

					return $generator->generate();
				}

				return null;

            // BARCODE_TYPE
            case MetaFieldModel::BARCODE_TYPE:
                if($rawValue === null or $rawValue === '' or empty($rawValue)){
                    return null;
                }

                if(!is_array($rawValue)){
                    return null;
                }

                return Barcode::render($rawValue);

            // QR_CODE_TYPE
            case MetaFieldModel::QR_CODE_TYPE:
                if($rawValue === null or $rawValue === '' or empty($rawValue)){
                    return null;
                }

                if(!is_array($rawValue)){
                    return null;
                }

                return QRCode::render($rawValue);

			// RATING_TYPE
			case MetaFieldModel::RATING_TYPE:
				if($rawValue === null or $rawValue === '' or empty($rawValue)){
					return null;
				}

				return Strings::renderStars($rawValue);

			// CHECKBOX_TYPE
			// LIST_TYPE
			// SELECT_MULTI_TYPE
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::LIST_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:

				if($rawValue === null or $rawValue === '' or empty($rawValue)){
					return null;
				}

				$listType = isset($atts['list_type']) ? $atts['list_type'] : 'list';

				switch( $listType ){
					case "comma_separated":
						return implode(", ", $rawValue);

					default:
					case "list":

						$output = "<ul>";
						foreach ($rawValue as $item){
							$output .= "<li>".$item."</li>";
						}
						$output .= "</ul>";

						return $output;
				}

			// CURRENCY_TYPE
			case MetaFieldModel::CURRENCY_TYPE:

				if(!isset($rawValue['amount']) and !isset($rawValue['unit'])){
					return null;
				}

				return $rawValue['amount'] .' '. $rawValue['unit'];

			// DATE_TYPE
			case MetaFieldModel::DATE_TYPE:

                if(!isset($rawValue['value'])){
                    return null;
                }

                if(!isset($rawValue['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $rawValue['object'];
                $value = $rawValue['value'];
				$defaultFormat = 'Y-m-d';
				$date = date_create_from_format( $defaultFormat, $value );

				if ($date) {
					$format = empty( $atts[ 'format' ] ) ? $defaultFormat : $atts[ 'format' ];

					return $date->format( $format );
				}

				return null;

			// DATE_TIME_TYPE
			case MetaFieldModel::DATE_TIME_TYPE:

                if(!isset($rawValue['value'])){
                    return null;
                }

                if(!isset($rawValue['object'])){
                    return null;
                }

                /** @var \DateTime $dateTimeObject */
                $dateTimeObject = $rawValue['object'];
                $value = $rawValue['value'];
				$defaultDateFormat = 'Y-m-d';
				$defaultTimeFormat = 'H:i:s';
				$date = date_create_from_format( $defaultDateFormat . ' ' . $defaultTimeFormat, $value );

				if ($date) {
					$defaultDateFormat = empty( $atts[ 'date_format' ] ) ? $defaultDateFormat : $atts[ 'date_format' ];
					$defaultTimeFormat = empty( $atts[ 'time_format' ] ) ? $defaultTimeFormat : $atts[ 'time_format' ];

					return $date->format( $defaultDateFormat . ' ' . $defaultTimeFormat );
				}

				return null;

			// DATE_RANGE_TYPE
			case MetaFieldModel::DATE_RANGE_TYPE:

                if(!isset($rawValue['value'])){
                    return null;
                }

                if(!isset($rawValue['object'])){
                    return null;
                }

                $dateTimeObject = $rawValue['object'];
                $value = $rawValue['value'];
				$defaultFormat = 'Y-m-d';

				if(empty($rawValue) or $rawValue === null){
					return null;
				}

				$format = empty( $atts[ 'format' ] ) ? $defaultFormat : $atts[ 'format' ];
				$dateStart = date_create_from_format( $defaultFormat, $value[0] );
				$dateEnd = date_create_from_format( $defaultFormat, $value[1] );

				return $dateStart->format($format) . ' - '. $dateEnd->format($format);

			// EDITOR_TYPE
			case MetaFieldModel::EDITOR_TYPE:

				if(!is_string($rawValue)){
					return null;
				}

				return do_shortcode($rawValue);

			// EMAIL_TYPE
			case MetaFieldModel::EMAIL_TYPE:

				$insertType = isset($atts['insert_type']) ? $atts['insert_type'] : 'email_url';

				switch( $insertType ){
					case "email_link":

						if($rawValue === null){
							return null;
						}

						if(!is_string($rawValue)){
							return null;
						}

						return "<a href='mailto:".Email::sanitize($rawValue)."'>".$rawValue."</a>";

					default:
					case "email_url":
						return $rawValue;
				}

			// EMBED_TYPE
			case MetaFieldModel::EMBED_TYPE:

				$width = isset($atts['width']) ? $atts['width'] : "100%";
				$height = isset($atts['height']) ? $atts['height'] : null;

				return (new \WP_Embed())->shortcode([
					'width' => $width,
					'height' => $height,
				], $rawValue);

			// FILE_TYPE
			case MetaFieldModel::FILE_TYPE:

				if(!isset($rawValue['file']) or !$rawValue['file'] instanceof WPAttachment){
					return null;
				}

				if($rawValue['file']->isEmpty()){
					return null;
				}

				$url = $rawValue['file']->getSrc();
				$label = (isset($rawValue['label']) and !empty($rawValue['label'])) ? $rawValue['label'] : $url;

				if($url === null){
					return null;
				}

				if(!is_string($url)){
					return null;
				}

				return "<a href='".Url::sanitize($url)."' target='".$atts['target_link']."'>".$label."</a>";

			// IMAGE_TYPE
			case MetaFieldModel::IMAGE_TYPE:

				if($rawValue === null or !$rawValue instanceof WPAttachment){
					return null;
				}

				if($rawValue->isEmpty()){
					return null;
				}

				$imageSize = explode( 'x', empty($atts['size']) ? '' : strtolower($atts['size']) );

				if( count($imageSize) == 1 ){
					$imageSize = $imageSize[0];
				} else{
					$imageSize = array_map( 'intval', $imageSize );
				}

				if( empty( $imageSize ) ) $imageSize = "medium";

				$imageId = $rawValue->getId();
				$imageUrl = wp_get_attachment_image_src( $imageId, $imageSize )[0];
				$imageAttachment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID='%d';", $imageId ),ARRAY_A );

				if( empty( $atts['insert_type'] ) ) $atts['insert_type'] = 'image_element';

				$insertType = isset($atts['insert_type']) ? $atts['insert_type'] : 'image_element';

				switch( $insertType ){
					case "image_element":
						return wp_get_attachment_image($imageId, $imageSize);

					case "image_url":
						return $imageUrl;

					case "image_title":
						return $imageAttachment['post_title'];

					case "image_caption":
						return $imageAttachment['post_excerpt'];

				}

				return null;

			// GALLERY_TYPE
			case MetaFieldModel::IMAGE_SLIDER_TYPE:
			case MetaFieldModel::GALLERY_TYPE:

				wp_enqueue_style( 'acpt.oxigen', plugins_url( 'advanced-custom-post-type/assets/static/css/acpt-oxigen.css'), [], '2.2.0', 'all');
				$outputType = isset($atts['output_type']) ? $atts['output_type'] : 'acpt_gallery';
				$separator = isset($atts['separator']) ? $atts['separator'] : ',';
				$perRow = isset($atts['per_row']) ? $atts['per_row'] : 1;

				switch ($outputType){
					case "images_id_list":

						$imageIds = [];
						foreach ($rawValue as $item){
							if($item instanceof WPAttachment){
								$imageIds[] = $item->getId();
							}
						}

						if(!is_array($imageIds)){
							return null;
						}

						return implode($separator, $imageIds);

					case "acpt_gallery":
					default:
						$output = '<div class="acpt-grid-'.$perRow.'">';
						foreach ($rawValue as $item){
							if($item instanceof WPAttachment){
								$output .= '<div class="item">'.$item->render().'</div>';
							}
						}
						$output .= '</div>';

						return $output;
				}

			// LENGTH_TYPE
			case MetaFieldModel::LENGTH_TYPE:

				if(!isset($rawValue['length']) and !isset($rawValue['unit'])){
					return null;
				}

				return $rawValue['length'] .' '. $rawValue['unit'];

			// NUMBER_TYPE
			// RANGE_TYPE
			case MetaFieldModel::NUMBER_TYPE:
			case MetaFieldModel::RANGE_TYPE:
				return (int)$rawValue;

			// PHONE_TYPE
			case MetaFieldModel::PHONE_TYPE:

                if(!isset($rawValue['value'])){
                    return null;
                }

                $val = $rawValue['value'];
                $dial = $rawValue['dial'] ?? null;

				$phoneType = isset($atts['phone_type']) ? $atts['phone_type'] : 'text';

				switch ($phoneType){
					case "link":
						return "<a href='".Phone::format($val, $dial, Phone::FORMAT_RFC3966)."' target='_blank'>".$val."</a>";

					case "text":
					default:
						return $val;
				}

			// URL_TYPE
			case MetaFieldModel::URL_TYPE:

				if(!isset($rawValue['url'])){
					return null;
				}

				$url = $rawValue['url'];
				$label = (isset($rawValue['label']) and !empty($rawValue['label'])) ? $rawValue['label'] : $rawValue['url'];

				if($url === null){
					return null;
				}

				if(!is_string($url)){
					return null;
				}

				$target = $atts['target_link'] ?? "_blank";

				return "<a href='".Url::sanitize($url)."' target='".$target."'>".$label."</a>";

			// VIDEO_TYPE
			case MetaFieldModel::VIDEO_TYPE:

				if($rawValue === null or !$rawValue instanceof WPAttachment){
					return null;
				}

				if($rawValue->isEmpty()){
					return null;
				}

				if( empty( $atts['insert_type'] ) ) $atts['insert_type'] = 'video_element';

				$videoUrl = $rawValue->getSrc();
				$insertType = isset($atts['insert_type']) ? $atts['insert_type'] : 'image_element';

				switch( $insertType ){
					case "video_element":

						$width = isset($atts['width']) ? $atts['width'] : "100%";
						$height = isset($atts['height']) ? $atts['height'] : null;

						return '<video width="'.$width.'" height="'.$height.'" controls>
		                            <source src="'.$videoUrl.'" type="video/mp4">
		                            Your browser does not support the video tag.
		                        </video>';

					case "video_url":
						return $videoUrl;

					case "video_title":
						return $rawValue->getTitle();

					case "video_caption":
						return $rawValue->getCaption();
				}

				return null;

			// WEIGHT_TYPE
			case MetaFieldModel::WEIGHT_TYPE:

				if(!isset($rawValue['weight']) and !isset($rawValue['unit'])){
					return null;
				}

				return $rawValue['weight'] .' '. $rawValue['unit'];

			default:
				return $rawValue;
		}
	}

	/**
	 * @param array $atts
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	private function getRawMetaValue($atts)
	{
		$path = OxygenDataKey::decode($atts['settings_path']);

		if(empty($path)){
			return null;
		}

		$belongsTo = $path['belongs_to'];
		$find = $path['find'];
		$boxName = $path['box_name'];
		$fieldName = $path['field_name'];
		$originalId = $path['original_id'];
		$rawValue = null;

        $fieldObject = get_acpt_meta_field_object($boxName, $fieldName);

        if($fieldObject === null and $originalId !== null){
            $fieldSettings = MetaRepository::getMetaFieldById($originalId);

            if($fieldSettings !== null){
                $fieldObject = $fieldSettings->toStdObject();
            }
        }

		switch ($belongsTo){
			case MetaTypes::OPTION_PAGE:

				if(is_acpt_field_visible([
					'option_page' => $find,
					'box_name' => $boxName,
					'field_name' => $fieldName,
				])){
					$rawValue = get_acpt_field([
						'option_page' => $find,
						'box_name' => $boxName,
						'field_name' => $fieldName,
					]);
				}
				break;

			case MetaTypes::TAXONOMY:
				$queriedObject = get_queried_object();

				if(is_acpt_field_visible([
					'term_id' => $queriedObject->term_id,
					'box_name' => $boxName,
					'field_name' => $fieldName,
				])){
					$rawValue = get_acpt_field([
						'term_id' => $queriedObject->term_id,
						'box_name' => $boxName,
						'field_name' => $fieldName,
					]);
				}
				break;

			default:
			case MetaTypes::CUSTOM_POST_TYPE:
				global $post;

				if(is_acpt_field_visible([
					'post_id' => $post->ID,
					'box_name' => $boxName,
					'field_name' => $fieldName,
				])){
					$rawValue = get_acpt_field([
						'post_id' => $post->ID,
						'box_name' => $boxName,
						'field_name' => $fieldName,
					]);
				}
				break;
		}

		return [
			'fieldObject' => $fieldObject,
			'rawValue' => $rawValue,
		];
	}

	/**
	 * @param $result
	 * @param $fieldObject
	 *
	 * @return array
	 */
	public function addUrlButton( $result, $fieldObject )
	{
		$validFieldTypes = [
            MetaFieldModel::EMAIL_TYPE,
            MetaFieldModel::FILE_TYPE,
            MetaFieldModel::IMAGE_TYPE,
            MetaFieldModel::PHONE_TYPE,
            MetaFieldModel::QR_CODE_TYPE,
            MetaFieldModel::TEXT_TYPE,
            MetaFieldModel::URL_TYPE,
        ];

		$properties = [];
		$settingsPage = (isset($fieldObject->option_page)) ? $fieldObject->option_page : null;

        if(empty($fieldObject)){
            return null;
        }

		// $properties
		switch ($fieldObject->type){
			case MetaFieldModel::IMAGE_TYPE:
				$properties[] = [
					'name'=> __( 'Size', 'oxygen-acpt' ),
					'data'=> 'size',
					'type'=> 'select',
					'options'=> [
						__( 'Thumbnail', 'oxygen-acpt' ) => 'thumbnail',
						__( 'Medium', 'oxygen-acpt' ) => 'medium',
						__( 'Medium Large', 'oxygen-acpt' ) => 'medium_large',
						__( 'Large', 'oxygen-acpt' ) => 'large',
						__( 'Original', 'oxygen-acpt' ) => 'full'
					],
					'nullval' => 'medium',
					'change'=> 'scope.dynamicDataModel.width = ""; scope.dynamicDataModel.height = ""'
				];
				$properties[] = [
					'name' => __( 'or', 'oxygen-acpt' ),
					'type' => 'label',
					'show_condition' => 'dynamicDataModel.insert_type == \'image_element\''
				];
				$properties[] = [
					'name' => __( 'Width', 'oxygen-acpt' ),
					'data' => 'width',
					'type' => 'text',
					'helper'=> true,
					'change' => "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					'show_condition' => "dynamicDataModel.insert_type == 'image_element'"
				];
				$properties[] = [
					'name' => __( 'Height', 'oxygen-acpt' ),
					'data' => 'height',
					'type' => 'text',
					'helper' => true,
					'change' => "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					'show_condition' => 'dynamicDataModel.insert_type == \'image_element\''
				];
				break;
		}

		if( in_array( $fieldObject->type, $validFieldTypes ) ) {

            $originalId = (!empty($fieldObject->forgedBy)) ? $fieldObject->id : null;

			$args = [
				'name' => '['. $fieldObject->findLabel . '] ' . $fieldObject->boxName . ' ' . $fieldObject->name,
				'data' => OxygenDataKey::encode($fieldObject->belongsToLabel, $fieldObject->findLabel, $fieldObject->boxName, $fieldObject->name, $originalId),
				'type' => 'button',
				'properties' => $properties,
			];

			if($settingsPage !== null){
				$args['settings_page'] = $settingsPage;
			}

			$result[] = $args;
		}

		return $result;
	}

	/**
	 * This function ALWAYS returns a url
	 *
	 * @param $atts
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function dynamicDataUrlHandler($atts)
	{
		$rawMetaValue = $this->getRawMetaValue($atts);

		if($rawMetaValue === null){
			return null;
		}

		$fieldObject = $rawMetaValue['fieldObject'];
		$rawValue =  $rawMetaValue['rawValue'];

		if(empty($rawValue) or $rawValue === null){
			return null;
		}

        if(empty($fieldObject)){
            return null;
        }

		switch ($fieldObject->type){

            case MetaFieldModel::QR_CODE_TYPE:

                if(empty($rawValue)){
                    return null;
                }

                if(!is_array($rawValue)){
                    return null;
                }

                if(!isset($rawValue['url'])){
                    return null;
                }

                if(!is_string($rawValue['url'])){
                    return null;
                }

                return $rawValue['url'];

			case MetaFieldModel::EMAIL_TYPE:

                if(empty($rawValue)){
					return null;
				}

				if(!is_string($rawValue)){
					return null;
				}

				return 'mailto:'.Email::sanitize($rawValue);

			case MetaFieldModel::IMAGE_TYPE:

				if($rawValue === null or !$rawValue instanceof WPAttachment){
					return null;
				}

				if($rawValue->isEmpty()){
					return null;
				}

				$imageSize = explode( 'x', empty($atts['size']) ? '' : strtolower($atts['size']) );

				if( count($imageSize) == 1 ){
					$imageSize = $imageSize[0];
				} else{
					$imageSize = array_map( 'intval', $imageSize );
				}

				if( empty( $imageSize ) ) $imageSize = "medium";

				$imageId = $rawValue->getId();

				return wp_get_attachment_image_src( $imageId, $imageSize )[0];

			case MetaFieldModel::FILE_TYPE:
				if(!isset($rawValue['file'])){
					return null;
				}

				if(!$rawValue['file'] instanceof WPAttachment){
					return null;
				}

				return $rawValue['file']->getSrc();

			case MetaFieldModel::PHONE_TYPE:
				return Phone::format($rawValue, null, Phone::FORMAT_RFC3966);

			case MetaFieldModel::URL_TYPE:

				if(!isset($rawValue['url'])){
					return null;
				}

				if($rawValue['url'] === null){
					return null;
				}

				if(!is_string($rawValue['url'])){
					return null;
				}

				return Url::sanitize($rawValue['url']);

			default:
				return $rawValue;
		}
	}

	/**
	 * @param $result
	 * @param $fieldObject
	 *
	 * @return array
	 */
	public function addImageIdButton( $result, $fieldObject )
	{
		$validFieldTypes = [
			MetaFieldModel::IMAGE_TYPE,
		];

		$settingsPage =  (isset($fieldObject->option_page)) ? $fieldObject->option_page : null;

		if( in_array( $fieldObject->type, $validFieldTypes ) ) {

            $originalId = (!empty($fieldObject->forgedBy)) ? $fieldObject->id : null;

			$args = [
				'name' => '['.$fieldObject->findLabel . '] ' . $fieldObject->boxName . ' ' . $fieldObject->name,
				'data' => OxygenDataKey::encode($fieldObject->belongsToLabel, $fieldObject->findLabel, $fieldObject->boxName, $fieldObject->name, $originalId),
				'type' => 'button',
				'properties' => [],
			];

			if($settingsPage !== null){
				$args['settings_page'] = $settingsPage;
			}

			$result[] = $args;
		}

		return $result;
	}

	/**
	 * @param $atts
	 *
	 * @return mixed|null
	 * @throws \Exception
	 */
	public function dynamicDataImageIdHandler($atts)
	{
		$rawMetaValue = $this->getRawMetaValue($atts);

		if($rawMetaValue === null){
			return null;
		}

		$fieldObject = $rawMetaValue['fieldObject'];
		$rawValue =  $rawMetaValue['rawValue'];

		if(empty($rawValue) or $rawValue === null){
			return null;
		}

		switch ($fieldObject->type){
			case MetaFieldModel::IMAGE_TYPE:

				if($rawValue === null or !$rawValue instanceof WPAttachment){
					return null;
				}

				if($rawValue->isEmpty()){
					return null;
				}

				return $rawValue->getId();
		}
	}
}