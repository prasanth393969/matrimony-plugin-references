<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

class FileField extends AbstractField
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

	    $label = (isset($rawData['label'])) ? $rawData['label'] : $wpAttachment->getTitle();
	    $render = $this->metaBoxFieldModel->getAdvancedOption("render") ?? "html";

        if($this->payload->preview){
            return $this->addBeforeAndAfter('<a href="'.$wpAttachment->getSrc().'" target="_blank">'.$label.'</a>');
        }

	    if($render === "id"){
            return $wpAttachment->getId();
        }

        if($render === "url"){
            return $wpAttachment->getSrc();
        }

        return $this->addBeforeAndAfter('<a href="'.$wpAttachment->getSrc().'" target="_blank">'.$label.'</a>');
    }
}