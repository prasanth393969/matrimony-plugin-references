<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\IP;

class IPTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_detect_null_ip_address_from_cli()
	{
		$address = IP::getClientIP();

		$this->assertNull($address);
	}
}