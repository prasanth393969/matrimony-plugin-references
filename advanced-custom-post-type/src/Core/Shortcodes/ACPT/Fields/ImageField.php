<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\Wordpress\WPAttachment;

class ImageField extends AbstractField
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

	    $wpAttachment = $this->getAttachment($rawData);

        if(empty($wpAttachment)){
            return null;
        }

        return $this->addBeforeAndAfter($this->renderImage($wpAttachment));
    }

    /**
     * @param WPAttachment $wpAttachment
     * @return int|string|null
     */
    private function renderImage(WPAttachment $wpAttachment)
    {
	    if($wpAttachment->isEmpty()){
	    	return null;
	    }

	    if($this->payload->preview){
	    	return $this->addBeforeAndAfter($wpAttachment->render([
                'style' => 'border: 1px solid #c3c4c7; object-fit: fill;',
                'size' => 'thumbnail',
                'w' => 80,
                'h' => 60,
            ]));
	    }

        $render = $this->metaBoxFieldModel->getAdvancedOption("render") ?? "html";

        if($render === "id"){
            return $wpAttachment->getId();
        }

        if($render === "url"){
            return $wpAttachment->getSrc();
        }

	    $width = ($this->payload->width !== null) ? $this->payload->width : '100%';
	    $height = ($this->payload->height !== null) ? $this->payload->height : null;

	    return $this->addBeforeAndAfter($wpAttachment->render([
            'w' => $width,
            'h' => $height,
        ]));
    }
}