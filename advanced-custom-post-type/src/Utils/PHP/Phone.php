<?php

namespace ACPT\Utils\PHP;

class Phone
{
    const FORMAT_ORIGINAL = 'original';
    const FORMAT_E164 = 'e164';
    const FORMAT_INTERNATIONAL = 'international';
    const FORMAT_NATIONAL = 'national';
    const FORMAT_RFC3966 = 'rfc3966';

    /**
     * Format a phone number
     *
     * @param      $number
     * @param null $dial
     * @param null $format
     *
     * @return string|null
     */
    public static function format($number, $dial = null, $format = null)
    {
        if(!is_scalar($number)){
            return null;
        }

        // Normalizing format
        $allowedFormats = [
            self::FORMAT_ORIGINAL,
            self::FORMAT_E164,
            self::FORMAT_INTERNATIONAL,
            self::FORMAT_NATIONAL,
            self::FORMAT_RFC3966,
        ];

        if(!in_array($format, $allowedFormats)){
            $format = self::FORMAT_E164;
        }

        // Fix for French numbers
        $number = str_replace("+330", "+33", $number);
        $number = str_replace([" ", "-", "(", ")", "#"], "", $number);
        $number = trim($number);

        // PHP < 8.1
        if(version_compare(PHP_VERSION, '8.1.0', '<') ){

            switch ($format){
                case self::FORMAT_RFC3966:
                    return "tel:".self::url($number, $dial);

                case self::FORMAT_NATIONAL:
                    return str_replace($dial, "", $number);

                case self::FORMAT_E164:
                default:
                    return $number;
            }
        }

        if($dial === null){
            $dial = '+1';
        }

        if($format === null){
            $format = self::FORMAT_E164;
        }

        if($format === self::FORMAT_ORIGINAL){
            return $number;
        }

        $dial = str_replace("+", "", $dial);
        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $defaultRegion = $phoneNumberUtil->getRegionCodesForCountryCode((int)$dial);
        $phoneNumberObject = $phoneNumberUtil->parse($number, $defaultRegion[0]);

        switch ($format){

            default:
            case self::FORMAT_E164:
                $f = \libphonenumber\PhoneNumberFormat::E164;
                break;

            case self::FORMAT_INTERNATIONAL:
                $f = \libphonenumber\PhoneNumberFormat::INTERNATIONAL;
                break;

            case self::FORMAT_NATIONAL:
                $f = \libphonenumber\PhoneNumberFormat::NATIONAL;
                break;

            case self::FORMAT_RFC3966:
                $f = \libphonenumber\PhoneNumberFormat::RFC3966;
                break;
        }

        return $phoneNumberUtil->format($phoneNumberObject, $f);
    }

    /**
     * @param $number
     * @param $dial
     *
     * @return string|string[]|null
     */
	public static function url($number, $dial)
	{
		if($number === null){
			return null;
		}

        if($dial === null){
            return null;
        }

		if(!is_string($number)){
			return null;
		}

        $number = "+" . $dial . " " . $number;
		$number = strip_tags($number);
		$number = str_replace([" ", "-", "(", ")", "#"], "", $number);
        $number = trim($number);

		// Fix for French numbers
        $number = str_replace("+330", "+33", $number);

		return $number;
	}
}