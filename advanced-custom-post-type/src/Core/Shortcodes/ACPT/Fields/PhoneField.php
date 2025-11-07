<?php

namespace ACPT\Core\Shortcodes\ACPT\Fields;

use ACPT\Utils\PHP\Phone;

class PhoneField extends AbstractField
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

	    $phone = $rawData['value'];
	    $dial = isset($rawData['dial']) ? $rawData['dial'] : null;

        return $this->renderPhone($phone, $dial);
    }

	/**
	 * @param $phone
	 * @param null $dial
	 *
	 * @return string
	 */
    private function renderPhone($phone, $dial = null)
    {
    	if(!empty($dial)){
		    $phone = "+".$dial. " " .$phone;
	    }

    	$format = $this->payload->phoneFormat ?? Phone::FORMAT_E164;
    	$phone = Phone::format($phone, $dial, $format);

    	if($this->payload->render === 'text'){
    		return $phone;
	    }

    	return $this->addBeforeAndAfter('<a href="'.Phone::format($phone, $dial, Phone::FORMAT_RFC3966).'" target="_blank">'.$phone.'</a>');
    }
}