<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\GeoLocation;

class GeoLocationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function return_null()
    {
        $address = "dsadasdasdsa";
        $coordinates =  GeoLocation::getCoordinates($address);

        $this->assertNull( $coordinates['lat']);
        $this->assertNull( $coordinates['lng']);
    }

    /**
     * @test
     */
    public function can_fetch_geo_data()
    {
        $address = "Via Latina 94, 00179 Roma";
        $coordinates =  GeoLocation::getCoordinates($address);

        $this->assertArrayHasKey('lat', $coordinates);
        $this->assertArrayHasKey('lng', $coordinates);
    }

	/**
	 * @test
	 */
	public function can_fetch_city()
	{
		$city = GeoLocation::getCity(-23.549070899999997, -46.64947817411696);

		$this->assertEquals($city, "São Paulo");
	}

    /**
     * @test
     */
    public function can_fetch_country()
    {
        $country = GeoLocation::getCountry(-23.549070899999997, -46.64947817411696);

        $this->assertContains($country, ["Brazil", "Brasil"]);
    }
}
