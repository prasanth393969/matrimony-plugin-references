<?php

namespace ACPT\Core\Generators\User;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Meta\Fields\AbstractField;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Data\Meta;

class UserMetaFieldGenerator
{
    /**
     * @var MetaFieldModel
     */
    private $metaField;

    /**
     * @var \WP_User $user
     */
    private $user;

    /**
     * UserMetaFieldGenerator constructor.
     *
     * @param MetaFieldModel $metaField
     * @param \WP_User           $user
     */
    public function __construct(MetaFieldModel $metaField, \WP_User $user)
    {
        $this->metaField = $metaField;
        $this->user = $user;
    }

	/**
	 * @return AbstractField|null
	 */
    public function generate()
    {
	    return $this->getUserMetaField();
    }

	/**
	 * @return AbstractField|null
	 */
    private function getUserMetaField()
    {
	    $className = 'ACPT\\Core\\Generators\\Meta\\Fields\\'.$this->metaField->getType().'Field';
        $value = null;

	    if(class_exists($className)){

            if(!empty($this->user->ID)){
                $value = Meta::fetch($this->user->ID, MetaTypes::USER, $this->metaField->getDbName());
            }

		    /** @var AbstractField $instance */
		    $instance = new $className($this->metaField, MetaTypes::USER, $this->user->ID);

            if($value !== '' and $value !== null){
                $instance->setValue($value);
            }

		    return $instance;
	    }

	    return null;
    }
}