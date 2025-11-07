<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Core\Helper\Id;

class IDField extends AbstractField
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

        $idStrategy = $this->metaBoxFieldModel->getAdvancedOption('id_strategy') ?? Id::UUID_V1;

	    if(!Id::isValid($rawData['value'], $idStrategy)){
	        return null;
        }

        return $rawData['value'];
    }
}