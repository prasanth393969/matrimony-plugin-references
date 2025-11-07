<?php

namespace ACPT\Utils\PHP;

use ACPT\Core\Helper\Strings;

class Date
{
    /**
     * This function convert any date string to a DateTime object
     * (NOT IN USE)
     *
     * @param string $date
     *
     * @return \DateTime|null
     */
    public static function objectFromString(string $date)
    {
        try {
            $formats = self::formats($date);
            $timeFormats = $formats['timeFormats'];
            $dateFormats = $formats['dateFormats'];

            foreach ($dateFormats as $format){
                $dateTime = \DateTime::createFromFormat($format, $date);

                if($dateTime instanceof \DateTime and $dateTime->format($format) === $date){
                    return $dateTime;
                }

                foreach ($timeFormats as $timeFormat){
                    $completeFormat = $format . " " . $timeFormat;
                    $dateTime = \DateTime::createFromFormat($completeFormat, $date);

                    if($dateTime instanceof \DateTime and $dateTime->format($completeFormat) === $date){
                        return $dateTime;
                    }
                }
            }

            foreach ($timeFormats as $timeFormat){
                $completeFormat = $timeFormat;
                $dateTime = \DateTime::createFromFormat($completeFormat, $date);

                if($dateTime instanceof \DateTime and $dateTime->format($completeFormat) === $date){
                    return $dateTime;
                }
            }

        } catch (\Exception $exception){
            return null;
        }
    }

    /**
     * @param $string
     *
     * @return array
     */
    private static function formats($string)
    {
        $timeFormats = [];
        $dateFormats = [];

        if(Strings::contains("/", $string)){
            $dateFormats = [
                "d/m/y",
                "d/m/Y",
                "m/d/y",
                "m/d/Y",
            ];
        } elseif(Strings::contains(".", $string)){
            $dateFormats = [
                "d.m.y",
                "d.m.Y",
            ];
        } elseif(Strings::contains("-", $string)){
            $dateFormats = [
                "Y-m-d",
                "y-m-d",
                "d-M-y",
                "d-M-Y",
            ];
        } elseif(Strings::contains(",", $string)){
            $dateFormats = [
                "F j, Y",
                "j F, Y",
            ];
        } elseif(Strings::contains(" ", $string) and !Strings::contains(":", $string)){
            $dateFormats = [
                "d M y",
                "d M Y",
            ];
        }

        if(Strings::contains(":", $string)){
            $timeFormats = [
                "H:i:s",
                "H:i",
                "g:i a",
                "g:i A",
            ];
        }

        return [
            'dateFormats' => $dateFormats,
            'timeFormats' => $timeFormats,
        ];
    }

    /**
     * @param $format
     *
     * @return string
     */
    public static function convertTimeFormatForJS($format)
    {
        $format = str_replace("i", "mm", $format);
        $format = str_replace("H", "HH", $format);
        $format = str_replace("g", "hh", $format);
        $format = str_replace("s", "ss", $format);

        return $format;
    }

    /**
     * @param $format
     *
     * @return string
     */
    public static function convertDateFormatForJS($format): string
    {
        $format = str_replace("d", "DD", $format);
        $format = str_replace("j", "DD", $format);
        $format = str_replace("M", "MMM", $format);
        $format = str_replace("m", "MM", $format);
        $format = str_replace("F", "MMMM", $format);
        $format = str_replace("Y", "YYYY", $format);
        $format = str_replace("y", "YY", $format);

        return $format;
    }

	/**
	 * @param $format
	 *
	 * @return bool
	 */
	public static function isDateFormatValid($format): bool
	{
		try {
			$dateTime = new \DateTime();
			$check = \DateTime::createFromFormat($format, $dateTime->format($format));

			return $check !== false;
		} catch (\Exception $exception){
			return false;
		}
	}

	/**
	 * @param $format
	 * @param $value
	 *
	 * @return string|null
	 */
	public static function format($format, $value)
	{
	    if(!self::isDateFormatValid($format)){
	        return ($value instanceof \DateTime) ? $value->format("Y-m-d H:i:s") : $value;
        }

	    if($value instanceof \DateTime){
            return date_i18n($format, $value->getTimestamp());
        }

		if(!is_string($value)){
			return null;
		}

		return date_i18n($format, strtotime($value));
	}
}