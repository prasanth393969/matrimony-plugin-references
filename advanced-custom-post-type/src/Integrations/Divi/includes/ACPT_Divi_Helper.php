<?php

use ACPT\Constants\BelongsTo;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\PHP\Arrays;

class ACPT_Divi_Helper
{
    /**
     * @return array
     */
    public static function allowedFields()
    {
        return [
            MetaFieldModel::ADDRESS_TYPE,
            MetaFieldModel::CHECKBOX_TYPE,
            MetaFieldModel::COLOR_TYPE,
            MetaFieldModel::CURRENCY_TYPE,
            MetaFieldModel::DATE_TYPE,
            MetaFieldModel::DATE_TIME_TYPE,
            MetaFieldModel::DATE_RANGE_TYPE,
            MetaFieldModel::EDITOR_TYPE,
            MetaFieldModel::EMAIL_TYPE,
            MetaFieldModel::EMBED_TYPE,
            MetaFieldModel::FILE_TYPE,
            MetaFieldModel::GALLERY_TYPE,
            MetaFieldModel::HTML_TYPE,
            MetaFieldModel::IMAGE_TYPE,
            MetaFieldModel::LENGTH_TYPE,
            MetaFieldModel::LIST_TYPE,
            MetaFieldModel::NUMBER_TYPE,
            MetaFieldModel::PHONE_TYPE,
            MetaFieldModel::BARCODE_TYPE,
            MetaFieldModel::QR_CODE_TYPE,
            MetaFieldModel::RADIO_TYPE,
            MetaFieldModel::RANGE_TYPE,
            MetaFieldModel::RATING_TYPE,
            MetaFieldModel::SELECT_TYPE,
            MetaFieldModel::SELECT_MULTI_TYPE,
            MetaFieldModel::TABLE_TYPE,
            MetaFieldModel::TEXT_TYPE,
            MetaFieldModel::TEXTAREA_TYPE,
            MetaFieldModel::TIME_TYPE,
            MetaFieldModel::VIDEO_TYPE,
            MetaFieldModel::WEIGHT_TYPE,
            MetaFieldModel::URL_TYPE,
        ];
    }

    /**
     * @param null $postId
     * @return MetaGroupModel[]
     * @throws Exception
     */
    public static function getGroupFields($postId = null)
    {
        // return all the fields if is layout page
        if(!$postId or self::isLayoutPage($postId)){
            return MetaRepository::get([
                'clonedFields' => true,
            ]);
        }

        $specificPostGroups = MetaRepository::get([
            'belongsTo' => BelongsTo::POST_ID,
            'find' => $postId,
            'clonedFields' => true,
        ]);

//        $specificParentPostGroups = MetaRepository::get([
//            'belongsTo' => BelongsTo::PARENT_POST_ID,
//            'find' => $post_id,
//            'clonedFields' => true,
//        ]);

        $customPostTypeGroups = MetaRepository::get([
            'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
            'find' => get_post_type($postId),
            'clonedFields' => true,
        ]);

        $groups = array_merge($customPostTypeGroups, $specificPostGroups);

        return Arrays::arrayUniqueOfEntities($groups);
    }

    /**
     * Determine if this a layout page from post ID
     *
     * @param null $postId
     * @return bool
     */
    public static function isLayoutPage($postId = null)
    {
        if(!$postId){
            return false;
        }

        $postType = get_post_type($postId);

        return et_theme_builder_is_layout_post_type($postType);
    }
}
