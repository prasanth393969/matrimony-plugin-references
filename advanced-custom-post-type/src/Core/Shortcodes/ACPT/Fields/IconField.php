<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\Wordpress\WPUtils;

class IconField extends AbstractField
{
    public function render()
    {
        if(!$this->isFieldVisible()){
            return null;
        }

	    $rawData = $this->fetchRawData();

	    if(!isset($rawData['value'])){
		    return null;
	    }

	    return $this->addBeforeAndAfter(WPUtils::renderShortCode($rawData['value']));
    }
}