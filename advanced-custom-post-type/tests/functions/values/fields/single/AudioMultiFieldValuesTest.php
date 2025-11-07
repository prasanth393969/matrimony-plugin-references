<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\WPAttachment;

class AudioMultiFieldValuesTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_add_acpt_meta_field_value()
    {
	    $new_group = save_acpt_meta_group([
		    'name' => 'new-group',
		    'label' => 'New group',
		    'belongs' => [
			    [
				    'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
				    'operator'  => "=",
				    "find"      => "page",
				    "logic"     => "OR"
			    ]
		    ],
		    'boxes' => [
			    [
				    'name' => 'box_name',
				    'label' => null,
				    'fields' => [
					    [
						    'name' => 'playlist',
						    'label' => 'playlist',
						    'type' => MetaFieldModel::AUDIO_MULTI_TYPE,
						    'showInArchive' => false,
						    'isRequired' => false,
						    'defaultValue' => null,
						    'description' => "lorem ipsum dolor facium",
					    ]
				    ]
			    ],
		    ],
	    ]);

	    $new_page = register_acpt_option_page([
		    'menu_slug' => 'new-page',
		    'page_title' => 'New page',
		    'menu_title' => 'New page menu title',
		    'icon' => 'admin-appearance',
		    'capability' => 'manage_options',
		    'description' => 'lorem ipsum',
		    'position' => 77,
	    ]);

	    $this->assertTrue($new_group);
	    $this->assertTrue($new_page);

	    foreach ($this->dataProvider() as $key => $value){
		    // not existent file
		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
			    'value' => [
				    "http://fdsfdsfdsfdsfds.com/fdsfdsfds/not-existing.txt"
			    ],
		    ]);

		    $this->assertFalse($add_acpt_meta_field_value);

		    // not an Gallery
		    $video = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/video1.mp4');
		    $videoUrl = $video['url'];

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
			    'value' => [
				    $videoUrl
			    ],
		    ]);

		    $this->assertFalse($add_acpt_meta_field_value);

		    // audio files
		    $audio1 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/sample-12s.mp3');
		    $audio1Id = $audio1['attachmentId'];
		    $audio1Url = $audio1['url'];
		    $audio2 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/sample-15s.mp3');
		    $audio2Id = $audio2['attachmentId'];
		    $audio2Url = $audio2['url'];

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
			    'value' => [
				    $audio1Url,
				    $audio2Url,
			    ],
		    ]);

		    $this->assertTrue($add_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
		    ]);

            $this->assertInstanceOf(WPAttachment::class, $acpt_field[0]);
            $this->assertInstanceOf(WPAttachment::class, $acpt_field[1]);
            $this->assertStringContainsString($acpt_field[0]->getMetadata()['fileformat'], "mp3");
            $this->assertStringContainsString($acpt_field[0]->getMetadata()['mime_type'], "audio/mpeg");
            $this->assertStringContainsString($acpt_field[1]->getMetadata()['fileformat'], "mp3");
            $this->assertStringContainsString($acpt_field[1]->getMetadata()['mime_type'], "audio/mpeg");

            $acpt_field_raw = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'playlist',
                'return' => 'raw',
            ]);

            $this->assertIsInt($acpt_field_raw[0]);
            $this->assertIsInt($acpt_field_raw[1]);

		    $this->deleteFile($audio1Url);
		    $this->deleteFile($audio2Url);
		    $this->deleteFile($videoUrl);
	    }
    }

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
    public function can_edit_acpt_meta_field_value()
    {
        $audio3 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/file_example_MP3_700KB.mp3');
        $audio3Url = $audio3['url'];
        $audio4 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/Free_Test_Data_100KB_MP3.mp3');
        $audio4Url = $audio4['url'];
        $audio5 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/Free_Test_Data_500KB_MP3.mp3');
        $audio5Url = $audio5['url'];

	    foreach ($this->dataProvider() as $key => $value){
		    $edit_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
			    'value' => [
				    $audio3Url,
				    $audio4Url,
				    $audio5Url,
			    ],
		    ]);

		    $this->assertTrue($edit_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
		    ]);

            $this->assertInstanceOf(WPAttachment::class, $acpt_field[0]);
            $this->assertInstanceOf(WPAttachment::class, $acpt_field[1]);
            $this->assertInstanceOf(WPAttachment::class, $acpt_field[2]);
	    }

        return [
            $audio3Url,
            $audio4Url,
            $audio5Url,
        ];
    }

	/**
	 * @depends can_edit_acpt_meta_field_value
	 * @test
	 *
	 * @param $urls
	 */
    public function can_display_acpt_meta_field($urls)
    {
	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
		    ]);

		    foreach ($urls as $url){
			    $this->assertStringContainsString('[playlist type="audio" style="light"', $acpt_field);
		    }
	    }

	    foreach ($urls as $url){
		    $this->deleteFile($url);
	    }
    }

    /**
     * @depends can_edit_acpt_meta_field_value
     * @test
     */
    public function can_delete_acpt_meta_field_value()
    {
	    foreach ($this->dataProvider() as $key => $value){
		    $delete_acpt_meta_field_value = delete_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);
	    }

	    $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

	    $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'playlist',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}