<?php

namespace ACPT\Integrations\SlimSeo\Provider\Fields;

use ACPT\Utils\Wordpress\Users;

class UserMulti extends Base
{
    /**
     * @return array|null
     */
    public function getValue()
    {
        if(is_array($this->value) and !empty($rawValue)){
            $users = [];

            foreach ($this->value as $user){
                if($user instanceof \WP_User){
                    $users[] = Users::getUserLabel($user);
                }
            }

            return $users;
        }

        return [];
    }
}
