<?php

namespace ACPT\Tests;

use ACPT\Utils\Wordpress\Files;

class FilesTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function canDownloadFileFromUrlAndDeleteIt()
	{
		$url = 'https://acpt.io/wp-content/uploads/2021/11/final-export.png';

		$file = Files::downloadFromUrl($url);

		$this->assertNotFalse($file);
		$this->assertTrue(Files::deleteFile($file['url']));
	}

	/**
	 * @test
	 */
	public function canUploadAFileFromPathAndDeleteIt()
	{
		$path = __DIR__.'/../_inc/support/files/image5.jpg';

		$file = Files::uploadFile($path);

		$this->assertNotFalse($file);
		$this->assertTrue(Files::deleteFile($file['url']));
	}
}