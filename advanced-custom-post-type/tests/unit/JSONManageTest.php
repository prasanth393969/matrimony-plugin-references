<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\JSON;

class JSONManageTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function object_to_escaped_json()
	{
		$object = [
			'type' => 'VALUE',
			'value' => 'VALUE',
		];
		$expected = '{&quot;type&quot;:&quot;VALUE&quot;,&quot;value&quot;:&quot;VALUE&quot;}';

		$this->assertEquals(JSON::arrayToEscapedJson($object), $expected);
	}

	/**
	 * @test
	 */
	public function escaped_json_to_object()
	{
		$expected = [
			'type' => 'VALUE',
			'value' => 'VALUE',
		];
		$json = '{&quot;type&quot;:&quot;VALUE&quot;,&quot;value&quot;:&quot;VALUE&quot;}';

		$this->assertEquals(JSON::escapedJsonToArray($json), $expected);
	}
}
