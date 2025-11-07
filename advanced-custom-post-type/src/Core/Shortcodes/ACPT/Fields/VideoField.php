<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

class VideoField extends AbstractField
{
    public function render()
    {
        if(!$this->isFieldVisible()){
            return null;
        }

        $width = ($this->payload->width !== null) ? $this->payload->width : '100%';
        $height = ($this->payload->height !== null) ? $this->payload->height : null;

	    $rawData = $this->fetchRawData();

	    if(!isset($rawData['value'])){
		    return null;
	    }

        $wpAttachment = $this->getAttachment($rawData);

        if(empty($wpAttachment)){
            return null;
        }

        if($wpAttachment->isEmpty()){
            return null;
        }

        if($this->payload->preview){
            return $this->addBeforeAndAfter($this->renderVideo($wpAttachment->getSrc(), 100, 65));
        }

        $render = $this->metaBoxFieldModel->getAdvancedOption("render") ?? "html";

        if($render === "id"){
            return $wpAttachment->getId();
        }

        if($render === "url"){
            return $wpAttachment->getSrc();
        }

	    return $this->addBeforeAndAfter($this->renderVideo($wpAttachment->getSrc(), $width, $height));
    }

	/**
	 * @param $src
	 * @param $width
	 * @param $height
	 *
	 * @return string
	 */
    private function renderVideo($src, $width, $height)
    {
    	return '<video width="'.$width.'" height="'.$height.'" controls>
                    <source src="'.$src.'" type="video/mp4">
                    Your browser does not support the video tag.
                </video>';
    }
}