<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

class RadioField extends AbstractField
{
	public function render()
	{
		if(!$this->isFieldVisible()){
			return null;
		}

		$render = $this->payload->render;
		$rawData = $this->fetchRawData();

		if(!isset($rawData['value'])){
			return null;
		}

		return $this->addBeforeAndAfter($this->renderItem($rawData['value'], $render));
	}

	/**
	 * @param $value
	 * @param null $render
	 *
	 * @return string|null
	 */
	private function renderItem($value, $render = null)
	{
		if($render === 'label' and $this->metaBoxFieldModel !== null){
			return $this->metaBoxFieldModel->getOptionLabel($value);
		}

		return $value;
	}
}