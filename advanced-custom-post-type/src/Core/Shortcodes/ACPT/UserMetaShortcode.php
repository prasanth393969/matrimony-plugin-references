<?php

namespace ACPT\Core\Shortcodes\ACPT;

use ACPT\Constants\MetaTypes;

class UserMetaShortcode extends AbstractACPTShortcode
{
    public function render($atts)
    {
        $uid = $atts['uid'];
        $box = $atts['box'];
        $field = $atts['field'];

        if(!$uid or !$box or !$field){
            return '';
        }

	    $uid = isset($atts['uid']) ? $atts['uid'] : get_current_user_id();

	    if(!$uid){
		    return '';
	    }

	    return $this->renderShortcode($uid, MetaTypes::USER, null, $atts);
    }
}