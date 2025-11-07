<?php

namespace ACPT\Core\Generators\CustomPostType;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\Fields\AbstractField;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Data\Meta;

class CustomPostTypeMetaBoxFieldGenerator
{
    /**
     * @param int    $postId
     * @param MetaFieldModel $metaField
     *
     * @return AbstractField
     * @throws \Exception
     */
    public static function generate(MetaFieldModel $metaField, $postId = null)
    {
	    return self::getCustomPostTypeField($metaField, $postId);
    }

    /**
     * @param int    $postId
     * @param MetaFieldModel $metaField
     *
     * @return AbstractField
     */
    private static function getCustomPostTypeField(MetaFieldModel $metaField, $postId = null): ?AbstractField
    {
        $className = 'ACPT\\Core\\Generators\\Meta\\Fields\\'.$metaField->getType().'Field';
        $value = null;

	    if(class_exists($className)){

	        if(!empty($postId)){
	            $value = Meta::fetch($postId, MetaTypes::CUSTOM_POST_TYPE, $metaField->getDbName());
            }

	    	/** @var AbstractField $instance */
	    	$instance = new $className($metaField, MetaTypes::CUSTOM_POST_TYPE, $postId);

            if($value !== '' and $value !== null){
                $instance->setValue($value);
            }

		    return $instance;
	    }

        return null;
    }
}
