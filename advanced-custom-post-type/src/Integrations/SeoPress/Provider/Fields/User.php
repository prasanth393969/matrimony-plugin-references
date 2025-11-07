<?php

namespace ACPT\Integrations\SeoPress\Provider\Fields;

use ACPT\Utils\Wordpress\Users;

class User extends Base
{
    /**
     * @return string|null
     */
    public function getValue()
    {
        if($this->value instanceof \WP_User){
            return Users::getUserLabel($this->value);
        }

        return '';
    }
}
