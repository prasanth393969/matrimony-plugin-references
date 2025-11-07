<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class GalleryFieldValuesTest extends AbstractTestCase
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
						    'name' => 'gallery',
						    'label' => 'gallery',
						    'type' => MetaFieldModel::GALLERY_TYPE,
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
			    'field_name' => 'gallery',
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
			    'field_name' => 'gallery',
			    'value' => [
				    $videoUrl
			    ],
		    ]);

		    $this->assertFalse($add_acpt_meta_field_value);

		    // images
		    $image1 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image1.jpg');
		    $image1Id = $image1['attachmentId'];
		    $image1Url = $image1['url'];
		    $image2 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image2.jpeg');
		    $image2Id = $image2['attachmentId'];
		    $image2Url = $image2['url'];

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'gallery',
			    'value' => [
				    $image1Url,
				    $image2Url,
			    ],
		    ]);

		    $this->assertTrue($add_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'gallery',
		    ]);

		    $this->assertStringContainsString($acpt_field[0]->getMetadata()['file'], $image1Url);
		    $this->assertStringContainsString($acpt_field[1]->getMetadata()['file'], $image2Url);

            $acpt_field_raw = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'gallery',
                'return' => 'raw',
            ]);

            $this->assertIsInt($acpt_field_raw[0]);
            $this->assertIsInt($acpt_field_raw[1]);

		    $this->deleteFile($image1Url);
		    $this->deleteFile($image2Url);
		    $this->deleteFile($videoUrl);
	    }
    }

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
    public function can_edit_acpt_meta_field_value()
    {
        $image3 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image3.jpg');
        $image3Url = $image3['url'];
        $image4 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image4.jpeg');
        $image4Url = $image4['url'];
        $image5 = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image5.jpg');
        $image5Url = $image5['url'];

	    foreach ($this->dataProvider() as $key => $value){
		    $edit_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'gallery',
			    'value' => [
				    $image3Url,
				    $image4Url,
				    $image5Url,
			    ],
		    ]);

		    $this->assertTrue($edit_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'gallery',
		    ]);

		    $this->assertStringContainsString($acpt_field[0]->getMetadata()['file'], $image3Url);
		    $this->assertStringContainsString($acpt_field[1]->getMetadata()['file'], $image4Url);
		    $this->assertStringContainsString($acpt_field[2]->getMetadata()['file'], $image5Url);
	    }

        return [
            $image3Url,
            $image4Url,
            $image5Url,
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
			    'field_name' => 'gallery',
		    ]);

		    foreach ($urls as $url){
			    $this->assertStringContainsString($url, $acpt_field);
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
			    'field_name' => 'gallery',
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);
	    }

	    $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

	    $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'gallery',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}