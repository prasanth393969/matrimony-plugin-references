<?php

namespace ACPT\Tests;

use ACPT\Utils\Data\Formatter\Driver\XMLFormatter;

class XMLBeautifierTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_beautify_xml()
	{
		$xml = '<?xml version="1.0"?><root><foo><bar>baz</bar></foo></root>';
		$beautified = XMLFormatter::beautify($xml);
		$expected = '<?xml version="1.0"?>
<root>
  <foo>
    <bar>baz</bar>
  </foo>
</root>
';

		$this->assertEquals($expected, $beautified);
	}
}