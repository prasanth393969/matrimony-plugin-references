<?php

use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\PHP\Barcode;
use ACPT\Utils\PHP\QRCode;
use ACPT\Utils\Wordpress\WPAttachment;

class ACPT_Divi_Dynamic_Content_Fields
{
    /**
     * Add ACPT fields to dynamic content source data
     *
     * @param array $custom_fields
     * @param int $post_id
     * @param array $raw_custom_fields
     *
     * @return array
     */
    public static function get_fields($custom_fields, $post_id, $raw_custom_fields )
    {
        try {
            $custom_fields = [];
            $metaGroups = ACPT_Divi_Helper::getGroupFields($post_id);

            foreach ($metaGroups as $group){
	            foreach ($group->getBoxes() as $boxModel){
		            foreach ($boxModel->getFields() as $fieldModel){

			            $fieldModel->setBelongsToLabel(MetaTypes::CUSTOM_POST_TYPE);
                        $postType = get_post_type($post_id);

			            if($postType !== 'et_theme_builder'){
                            $fieldModel->setFindLabel($postType);
                        }

			            $fieldType = $fieldModel->getType();

			            if(in_array($fieldType, ACPT_Divi_Helper::allowedFields())){
				            $custom_fields[$fieldModel->getId()] = [
					            'label'    => esc_html( $fieldModel->getUiName() ),
					            'type'     => self::get_type($fieldType),
					            'fields'   => [
						            'before' => [
							            'label'   => esc_html__( 'Before', ACPT_EXT_TEXT_DOMAIN ),
							            'type'    => 'text',
							            'default' => '',
							            'show_on' => 'text',
						            ],
						            'after'  => [
							            'label'   => esc_html__( 'After', ACPT_EXT_TEXT_DOMAIN ),
							            'type'    => 'text',
							            'default' => '',
							            'show_on' => 'text',
						            ],
					            ],
					            'meta_key' => $fieldModel->getId(),
					            'custom'   => true,
					            'group'    => 'ACPT: ['.$group->getUiName().'] - ' . $boxModel->getUiName(),
				            ];
			            }
		            }
	            }
            }

            return $custom_fields;
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return $custom_fields;
        }
    }

    /**
     * It returns 'text', 'image', 'url' or 'any'
     *
     * @param string $fieldType
     *
     * @return string
     */
    private static function get_type($fieldType)
    {
        switch ($fieldType){
            case MetaFieldModel::QR_CODE_TYPE:
            case MetaFieldModel::IMAGE_TYPE:
            case MetaFieldModel::GALLERY_TYPE:
                $type = 'image';
                break;

            case MetaFieldModel::FILE_TYPE:
            case MetaFieldModel::URL_TYPE:
            case MetaFieldModel::VIDEO_TYPE:
                $type = 'url';
                break;

            case MetaFieldModel::ADDRESS_TYPE:
            case MetaFieldModel::COLOR_TYPE:
            case MetaFieldModel::CURRENCY_TYPE:
            case MetaFieldModel::DATE_TYPE:
            case MetaFieldModel::DATE_TIME_TYPE:
            case MetaFieldModel::DATE_RANGE_TYPE:
            case MetaFieldModel::EDITOR_TYPE:
            case MetaFieldModel::EMAIL_TYPE:
            case MetaFieldModel::EMBED_TYPE:
            case MetaFieldModel::HTML_TYPE:
            case MetaFieldModel::LENGTH_TYPE:
            case MetaFieldModel::LIST_TYPE:
            case MetaFieldModel::NUMBER_TYPE:
            case MetaFieldModel::PHONE_TYPE:
            case MetaFieldModel::SELECT_TYPE:
            case MetaFieldModel::SELECT_MULTI_TYPE:
            case MetaFieldModel::RANGE_TYPE:
            case MetaFieldModel::RATING_TYPE:
            case MetaFieldModel::TABLE_TYPE:
            case MetaFieldModel::TEXT_TYPE:
            case MetaFieldModel::TEXTAREA_TYPE:
            case MetaFieldModel::TIME_TYPE:
            case MetaFieldModel::WEIGHT_TYPE:
                $type = 'text';
                break;

            default:
                $type = 'any';
        }

        return $type;
    }

    /**
     * @param string $meta_value
     * @param string $meta_key
     * @param int $post_id
     *
     * @return string|null
     */
    public static function get_value($meta_value, $meta_key, $post_id )
    {
        try {
            global $wp_query;
            $metaFieldModel = MetaRepository::getMetaFieldById($meta_key);

            if($metaFieldModel === null){
                return null;
            }

            if(ACPT_Divi_Helper::isLayoutPage($post_id)){
                return self::format_placeholder_value($metaFieldModel);
            }

            $is_blog_query = isset( $wp_query->et_pb_blog_query ) and $wp_query->et_pb_blog_query;

            $identifier = $post_id;
            $identifierKey = 'post_id';

            if ( ! $is_blog_query and (is_category() or is_tag() or is_tax())) {
                $term       = get_queried_object();
                $identifier = $term->term_id;
                $identifierKey = 'term_id';
            } elseif ( is_author() ) {
                $user       = get_queried_object();
                $identifier = $user->ID;
                $identifierKey = 'user_id';
            }

            return self::get_textual_value_for_field($metaFieldModel, (int)$identifier, $identifierKey);
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return $meta_value;
        }
    }

    /**
     * @param MetaFieldModel $fieldModel
     * @return string
     */
    private static function format_placeholder_value(MetaFieldModel $fieldModel)
    {
        $value = esc_html(
            sprintf(
                __( 'Your "%1$s" ACPT Field Value Will Display Here', 'et_builder' ),
                $fieldModel->getUiName()
            )
        );

        switch ( $fieldModel->getType() ) {
            case MetaFieldModel::IMAGE_TYPE:
                $value = ET_BUILDER_PLACEHOLDER_LANDSCAPE_IMAGE_DATA;
                break;

            case MetaFieldModel::TERM_OBJECT_TYPE:
                $value = esc_html(
                    implode(
                        ', ',
                        array(
                            __( 'Category 1', 'et_builder' ),
                            __( 'Category 2', 'et_builder' ),
                            __( 'Category 3', 'et_builder' ),
                        )
                    )
                );
                break;
        }

        return $value;
    }

    /**
     * Return a textual value for the field
     *
     * @param MetaFieldModel $metaFieldModel
     * @param integer        $identifier
     * @param string         $identifierKey
     *
     * @return string|array|null
     */
    private static function get_textual_value_for_field(MetaFieldModel $metaFieldModel, $identifier, $identifierKey)
    {
        $meta_value = get_acpt_field([
            $identifierKey => $identifier,
            'box_name' => $metaFieldModel->getBox()->getName(),
            'field_name' => $metaFieldModel->getName(),
        ]);

        if(empty($meta_value)){
            return null;
        }

        $fieldType = $metaFieldModel->getType();

        switch ($fieldType){

            case MetaFieldModel::ADDRESS_TYPE:
                return $meta_value['address'];

            case MetaFieldModel::CURRENCY_TYPE:
                return $meta_value['amount']. ' ' . $meta_value['unit'];

            case MetaFieldModel::GALLERY_TYPE:
                $ids = [];

                foreach ($meta_value as $image){
                    if($image instanceof WPAttachment and !$image->isEmpty()){
                        $ids[] = $image->getId();
                    }
                }

                return implode(',', $ids);


		    case MetaFieldModel::DATE_TYPE:
		    case MetaFieldModel::DATE_TIME_TYPE:
		    case MetaFieldModel::TIME_TYPE:

		        if(!isset($meta_value['value'])){
		            return null;
                }

                return $meta_value['value'];

		    case MetaFieldModel::DATE_RANGE_TYPE:
                if(!isset($meta_value['value'])){
                    return null;
                }

		    	return implode(' - ', $meta_value['value']);

            case MetaFieldModel::QR_CODE_TYPE:
                return QRCode::render($meta_value);

            case MetaFieldModel::BARCODE_TYPE:
                return Barcode::render($meta_value);

            case MetaFieldModel::FILE_TYPE:
            case MetaFieldModel::IMAGE_TYPE:
            case MetaFieldModel::VIDEO_TYPE:

            	if($meta_value instanceof WPAttachment and !$meta_value->isEmpty()){
            		return $meta_value->getSrc();
	            }

                return null;

		    case MetaFieldModel::TABLE_TYPE:
			    if(is_string($meta_value) and Strings::isJson($meta_value)){
				    $generator = new TableFieldGenerator($meta_value);

				    return $generator->generate();
			    }

			    return null;

		    case MetaFieldModel::RATING_TYPE:
			    return Strings::renderStars($meta_value);

            case MetaFieldModel::WEIGHT_TYPE:
                return $meta_value['weight']. ' ' . $meta_value['unit'];

            case MetaFieldModel::LENGTH_TYPE:
                return $meta_value['length']. ' ' . $meta_value['unit'];

            case MetaFieldModel::LIST_TYPE:
                return implode(PHP_EOL, $meta_value);

            case MetaFieldModel::CHECKBOX_TYPE:
            case MetaFieldModel::SELECT_MULTI_TYPE:
                return (implode(",", $meta_value));

            case MetaFieldModel::URL_TYPE:
                return $meta_value['url'];

            default:
                return $meta_value;
        }
    }

    /**
     * Add Dynamic Content support for Images field of Gallery module.
     *
     * @param array $modules Modules list.
     *
     * @return array Filtered modules list.
     */
    public static function add_dynamic_support_for_gallery_field( $modules )
    {
        if ( empty( $modules['et_pb_gallery'] ) ) {
            return $modules;
        }

        $module = $modules['et_pb_gallery'];

        if ( ! isset( $module->fields_unprocessed ) ) {
            return $modules;
        }

        if ( ! empty( $module->fields_unprocessed['gallery_ids'] ) ) {
            $module->fields_unprocessed['gallery_ids']['dynamic_content'] = 'image';
        }

        $modules['et_pb_gallery'] = $module;

        return $modules;
    }
}