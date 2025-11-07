<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\Assert;

class AssertTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function canDetectUrl()
	{
		$validUrls = [
			'https://acpt.site4web.eu/wp-content/uploads/Шар-2-1.png',
			'https://acpt.site4web.eu/wp-content/uploads/image.png',
			'https://acpt.site4web.eu/wp-content/uploads/のはでしたコンサート.png',
		];

		$invalidUrls = [
			'acpt.site4web.eu/wp-content/uploads/Шар-2-1.png',
			'wp-content/uploads/image.png',
			'wp-content/uploads/のはでしたコンサート.png',
		];

		foreach ($validUrls as $validUrl){
			Assert::url($validUrl);
			$this->assertEquals(1,1);
		}

		foreach ($invalidUrls as $invalidUrl){
			try {
				Assert::url($invalidUrl);
			} catch (\Exception $exception){
				$this->assertEquals($exception->getMessage(),"Expected a value to be a valid url. Got: \"".$invalidUrl."\"");
			}
		}
	}
}
