<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Core\Helper\Strings;

class RatingField extends AbstractField
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

		return $this->addBeforeAndAfter(Strings::renderStars($rawData['value']));
	}
}


