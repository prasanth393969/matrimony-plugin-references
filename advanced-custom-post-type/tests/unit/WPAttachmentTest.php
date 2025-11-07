<?php

namespace ACPT\Tests;

use ACPT\Utils\Wordpress\WPAttachment;

class WPAttachmentTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function is_empty()
    {
        $url = "http://fdsfdsfdsfds.com/not-existing.txt";
        $metadata = WPAttachment::fromUrl($url);

        $this->assertTrue($metadata->isEmpty());
    }
    
    /**
     * @test
     */
    public function get_metadata_from_image_url()
    {
        $image = $this->uploadFile(__DIR__.'/../../tests/_inc/support/files/image1.jpg');

        $url = $image['url'];
        $wpAttachment = WPAttachment::fromUrl($url);
        $isImage = $wpAttachment->isImage();

        $this->assertNotNull($wpAttachment);
        $this->assertNotNull($wpAttachment->getId());
        $this->assertTrue($isImage);

        $this->deleteFile($url);
    }

    /**
     * @test
     */
    public function get_metadata_from_video_url()
    {
        $video = $this->uploadFile(__DIR__.'/../../tests/_inc/support/files/video1.mp4');

        $url = $video['url'];
        $wpAttachment = WPAttachment::fromUrl($url);
        $isVideo = $wpAttachment->isVideo();

	    $this->assertNotNull($wpAttachment);
	    $this->assertNotNull($wpAttachment->getId());
        $this->assertTrue($isVideo);

        $this->deleteFile($url);
    }

    /**
     * @test
     */
    public function get_empty_array_from_pdf_url()
    {
        $pdf = $this->uploadFile(__DIR__.'/../../tests/_inc/support/files/dummy.pdf');

        $url = $pdf['url'];
	    $wpAttachment = WPAttachment::fromUrl($url);

        $isVideo = $wpAttachment->isVideo();
        $isImage = $wpAttachment->isImage();

	    $this->assertNotNull($wpAttachment);
	    $this->assertNotNull($wpAttachment->getId());
        $this->assertFalse($isVideo);
        $this->assertFalse($isImage);

        $this->deleteFile($url);
    }
}
