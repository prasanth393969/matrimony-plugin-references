<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class ImageFieldValuesTest extends AbstractTestCase
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
			    ],
		    ],
		    'boxes' => [
			    [
				    'name' => 'box_name',
				    'label' => null,
				    'fields' => [
					    [
						    'name' => 'image',
						    'label' => 'image',
						    'type' => MetaFieldModel::IMAGE_TYPE,
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
			    'field_name' => 'image',
			    'value' => "http://localhost:83/wp-content/uploads/not-existing.txt",
		    ]);

		    $this->assertFalse($add_acpt_meta_field_value);

		    // not an image
		    $video = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/video1.mp4');
		    $videoUrl = $video['url'];

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'image',
			    'value' => $videoUrl,
		    ]);

		    $this->assertFalse($add_acpt_meta_field_value);

		    // image
		    $image = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image1.jpg');
		    $imageId = $image['attachmentId'];
		    $imageUrl = $image['url'];

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'image',
			    'value' => $imageId,
		    ]);

		    $this->assertTrue($add_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'image',
		    ]);

		    $this->assertStringContainsString($acpt_field->getMetadata()['file'], $imageUrl);

            $acpt_field_raw = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'image',
                'return' => 'raw'
            ]);

            $this->assertIsInt($acpt_field_raw);

		    $this->deleteFile($imageUrl);
		    $this->deleteFile($videoUrl);
	    }
    }

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
    public function can_edit_acpt_meta_field_value()
    {
	    $image = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image2.jpeg');
        $imageId = $image['attachmentId'];
	    $imageUrl = $image['url'];

	    foreach ($this->dataProvider() as $key => $value){

		    $edit_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'image',
			    'value' => $imageUrl,
		    ]);

		    $this->assertTrue($edit_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'image',
		    ]);

		    $this->assertStringContainsString($acpt_field->getMetadata()['file'], $imageUrl);

            $acpt_field_raw = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'image',
                'return' => 'raw'
            ]);

            $this->assertIsInt($acpt_field_raw);
        }

	    return $imageUrl;
    }

    /**
     * @depends can_edit_acpt_meta_field_value
     * @test
     */
    public function can_display_acpt_meta_field($url)
    {
	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'image',
		    ]);

		    $this->assertStringContainsString($url, $acpt_field);
	    }

	    $this->deleteFile($url);
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
			    'field_name' => 'image',
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);
	    }

	    $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

	    $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'image',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}

