<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\PHP\Date;

class DateRangeField extends DateField
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
		    $rawValue = $rawData['value'];
		    $rawValue = explode(" - ", $rawValue);
            $savedForm = $rawData['format'] ?? "Y-m-d";

            $dateStart = \DateTime::createFromFormat($savedForm, $rawValue[0]);
            $dateEnd = \DateTime::createFromFormat($savedForm, $rawValue[1]);

            if(!$dateStart instanceof \DateTime){
                $dateStart = \DateTime::createFromFormat("Y-m-d", $rawValue[0]);
            }

            if(!$dateEnd instanceof \DateTime){
                $dateEnd = \DateTime::createFromFormat("Y-m-d", $rawValue[1]);
            }

            if(!$dateStart instanceof \DateTime){
                return null;
            }

            if(!$dateEnd instanceof \DateTime){
                return null;
            }

		    return $this->addBeforeAndAfter(Date::format($dateFormat, $dateStart)) . ' - '. $this->addBeforeAndAfter(Date::format($dateFormat, $dateEnd));
	    } catch (\Exception $exception){
            do_action("acpt/error", $exception);

	    	return null;
	    }
    }
}