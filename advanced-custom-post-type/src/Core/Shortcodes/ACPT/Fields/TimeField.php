<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\PHP\Date;

class TimeField extends DateField
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

	    try {
		    $timeFormat = $this->getDefaultTimeFormat();

            $savedForm = $rawData['format'] ?? "H:i:s";
            $date = \DateTime::createFromFormat($savedForm, $rawData['value']);

            if(!$date instanceof \DateTime){
                $date = \DateTime::createFromFormat("H:i:s", $rawData['value']);
            }

		    if(!$date instanceof \DateTime){
		        return null;
            }

		    return $this->addBeforeAndAfter(Date::format($timeFormat, $date));
	    } catch (\Exception $exception){
            do_action("acpt/error", $exception);

		    return null;
	    }
    }
}