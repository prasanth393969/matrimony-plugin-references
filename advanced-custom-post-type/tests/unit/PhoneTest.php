<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\Phone;

class PhoneTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_format_phone_numbers()
    {
        $phones = [
            [ 'number' => '0117 496 0123', 'dial' => '+44', 'format' => Phone::FORMAT_RFC3966, 'expected' => 'tel:+44-117-496-0123' ],
            [ 'number' => '+39067853631', 'dial' => '+39', 'format' => Phone::FORMAT_RFC3966, 'expected' => 'tel:+39-06-785-3631' ],
            [ 'number' => '+39067853631', 'dial' => null, 'format' => Phone::FORMAT_RFC3966, 'expected' => 'tel:+39-06-785-3631' ],
            [ 'number' => '+39067853631', 'dial' => null, 'format' => Phone::FORMAT_INTERNATIONAL, 'expected' => '+39 06 785 3631' ],
            [ 'number' => '+39067853631', 'dial' => null, 'format' => Phone::FORMAT_NATIONAL, 'expected' => '06 785 3631' ],
            [ 'number' => '+39067853631', 'dial' => '+39', 'format' => Phone::FORMAT_E164, 'expected' => '+39067853631' ],
            [ 'number' => '01 09 75 83 51', 'dial' => '33', 'format' => Phone::FORMAT_RFC3966, 'expected' => 'tel:+33-1-09-75-83-51' ],
            [ 'number' => '+12124567890', 'dial' => '+1', 'format' => Phone::FORMAT_NATIONAL, 'expected' => '(212) 456-7890' ],
            [ 'number' => '+12124567890', 'dial' => null, 'format' => Phone::FORMAT_NATIONAL, 'expected' => '(212) 456-7890' ],
            [ 'number' => '+12124567890', 'dial' => null, 'format' => Phone::FORMAT_E164, 'expected' => '+12124567890' ],
            [ 'number' => '+47 333 78 901', 'dial' => '+47', 'format' => Phone::FORMAT_NATIONAL, 'expected' => '33 37 89 01' ],
            [ 'number' => '+47 333 78 901', 'dial' => '+47', 'format' => Phone::FORMAT_E164, 'expected' => '+4733378901' ],
        ];

        foreach ($phones as $phone){
            $ph = Phone::format($phone['number'], $phone['dial'], $phone['format']);

            $this->assertEquals($ph, $phone['expected']);
        }
    }
}