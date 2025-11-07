<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\PHP\Date;

class DateTimeField extends DateField
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
		    $dateFormat = $this->getDefaultDateFormat();
		    $timeFormat = $this->getDefaultTimeFormat();
		    $dateTimeFormat = $dateFormat . ' ' . $timeFormat;

            $savedForm = $rawData['format'] ?? "Y-m-d H:i:s";
		    $date = \DateTime::createFromFormat($savedForm, $rawData['value']);

            if(!$date instanceof \DateTime){
                $date = \DateTime::createFromFormat("Y-m-d H:i:s", $rawData['value']);
            }

		    if(!$date instanceof \DateTime){
		        return null;
            }

		    return $this->addBeforeAndAfter(Date::format($dateTimeFormat, $date));
	    } catch (\Exception $exception){
            do_action("acpt/error", $exception);

		    return null;
	    }
    }
}