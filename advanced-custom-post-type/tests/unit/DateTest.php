<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\Date;

class DateTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function isDateFormatValid()
	{
		$formats = [
			'cavolo' => false,
			'not allowed' => false,
			'HH:mm:ss.SSS' => true,
			'G:i' => true,
			'd/m/Y' => true,
			'j F Y' => true,
		];

		foreach ($formats as $format => $isValid){
			if($isValid){
				$this->assertTrue(Date::isDateFormatValid($format));
			} else {
				$this->assertFalse(Date::isDateFormatValid($format));
			}
		}
	}

    /**
     * @test
     */
    public function convertTimeFormatForJS()
    {
        $formats = [
            "g:i a" => "hh:mm a",
            "g:i A" => "hh:mm A",
            "H:i:s" => "HH:mm:ss",
            "H:i" => "HH:mm",
        ];

        foreach ($formats as $php => $js){
            $this->assertEquals(Date::convertTimeFormatForJS($php), $js);
        }
    }

    /**
     * @test
     */
    public function convertDateFormatForJS()
    {
        $formats = [
            "F j, Y" => "MMMM DD, YYYY",
            "j F, Y" => "DD MMMM, YYYY",
            "Y-m-d" => "YYYY-MM-DD",
            "y-m-d" => "YY-MM-DD",
            "d-M-y" => "DD-MMM-YY",
            "d-M-Y" => "DD-MMM-YYYY",
            "d M y" => "DD MMM YY",
            "d M Y" => "DD MMM YYYY",
            "d/m/y" => "DD/MM/YY",
            "d/m/Y" => "DD/MM/YYYY",
            "m/d/y" => "MM/DD/YY",
            "m/d/Y" => "MM/DD/YYYY",
            "d.m.y" => "DD.MM.YY",
            "d.m.Y" => "DD.MM.YYYY",
        ];

        foreach ($formats as $php => $js){
            $this->assertEquals(Date::convertDateFormatForJS($php), $js);
        }
    }

    /**
     * @test
     */
	public function objectFromString()
    {
        $dates = [
            "F j, Y" => "July 29, 2025",
            "j F, Y" => "29 July, 2025",
            "Y-m-d" => "1990-10-28",
            "Y-m-d H:i" => "1990-10-28 23:00",
            "Y-m-d H:i:s" => "1990-10-28 23:00:00",
            "y-m-d" => "90-10-28",
            "d-M-y" => "28-Oct-90",
            "d-M-Y" => "28-Oct-1990",
            "d M y" => "28 Oct 90",
            "d M Y" => "28 Oct 1990",
            "d/m/y" => "28/10/90",
            "d/m/Y" => "28/10/1990",
            "m/d/y" => "10/28/90",
            "m/d/Y" => "10/28/1990",
            "d.m.y" => "28.10.90",
            "d.m.Y" => "28.10.1990",
            "H:i"   => "23:00",
            "H:i:s" => "23:00:00",
        ];

        foreach ($dates as $format => $date){
            $obj = Date::objectFromString($date);

            $this->assertEquals($obj->format($format), $date);
        }

        $this->assertNull(Date::objectFromString("bubbole"));
    }
}