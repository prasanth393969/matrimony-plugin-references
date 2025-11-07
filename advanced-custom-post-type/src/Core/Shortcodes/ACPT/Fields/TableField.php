<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Core\Generators\Meta\TableFieldGenerator;
use ACPT\Core\Helper\Strings;

class TableField extends AbstractField
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

		$value = $rawData['value'];

		if(is_array($value)){
			$value = json_encode($value);
		}

		if(!Strings::isJson($value)){
			return null;
		}

		$generator = new TableFieldGenerator($value);

		return $generator->generate();
	}
}