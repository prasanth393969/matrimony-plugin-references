<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\Wordpress\WPUtils;

class TextareaField extends AbstractField
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

        $allowHtml = $this->metaBoxFieldModel->getAdvancedOption("allow_html");

        if($allowHtml == 1){
            $rawData['value'] = html_entity_decode($rawData['value']);
        }

	    return $this->addBeforeAndAfter($this->renderTextarea($rawData['value']));
    }

	/**
	 * @param $data
	 *
	 * @return string
	 */
    private function renderTextarea($data)
    {
    	return WPUtils::renderShortCode($data, true);
    }
}