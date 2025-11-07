<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\Url;

class UrlTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function getLastPartOfUrl()
	{
		$urls = [
			'string' => '',
			'' => '',
			'https://wpml.org/tutorials/2023/06/local-wordpress-development-with-docker-phpstorm-xdebug/' => 'local-wordpress-development-with-docker-phpstorm-xdebug',
			'https://stackoverflow.com/questions/7395049/get-last-part-of-url-php' => 'get-last-part-of-url-php',
			'https://github.com/mauretto78/advanced-custom-post-type/pull/83' => '83',
			'https://stackoverflow.com' => '',
		];

		foreach ($urls as $url => $slug){
			$calculatedSlug = Url::getLastPartOfUrl($url);
			$this->assertEquals($calculatedSlug, $slug);
		}
	}
}