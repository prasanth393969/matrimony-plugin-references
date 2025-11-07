<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

class UrlField extends AbstractField
{
    public function render()
    {
        if(!$this->isFieldVisible()){
            return null;
        }

        $target = ($this->payload->target !== null) ? $this->payload->target : '_blank';
	    $rawData = $this->fetchRawData();

	    if(!is_array($rawData)){
	        return null;
        }

	    if(!isset($rawData['value'])){
		    return null;
	    }

        return $this->renderUrl($rawData, $target);
    }

	/**
	 * @param $rawData
	 * @param $target
	 *
	 * @return string
	 */
	private function renderUrl($rawData, $target)
	{
		$label = isset($rawData['label']) ? $rawData['label'] : $rawData['value'];
		$label = $this->addBeforeAndAfter($label);

        $render = $this->metaBoxFieldModel->getAdvancedOption("render") ?? "html";

        if($render === "label"){
            return $label;
        }

        if($render === "url"){
            return $rawData['value'];
        }

		return '<a href="'.$rawData['value'].'" target="'.$target.'">'.$label.'</a>';
	}
}