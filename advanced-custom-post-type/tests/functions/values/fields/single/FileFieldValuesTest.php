<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class FileFieldValuesTest extends AbstractTestCase
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
						    'name' => 'file',
						    'label' => 'file',
						    'type' => MetaFieldModel::FILE_TYPE,
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
			    'field_name' => 'file',
			    'value' => [
				    'label' => 'This is a label',
				    'url' => "http://gfdgfdgdfgfdgfd.com/not-existing.txt",
			    ],
		    ]);

		    $this->assertFalse($add_acpt_meta_field_value);

		    // not a generic File
		    $image = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image1.jpg');
		    $imageUrl = $image['url'];

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'file',
			    'value' => [
				    'label' => 'This is a label',
				    'url' => $imageUrl,
			    ],
		    ]);

		    $this->assertFalse($add_acpt_meta_field_value);

		    $this->deleteFile($imageUrl);

		    $pdf = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/dummy.pdf');
		    $url = $pdf['url'];

		    $add_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'file',
			    'value' => [
				    'label' => 'This is a label',
				    'url' => $url
			    ],
		    ]);

		    $this->assertTrue($add_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'file',
		    ]);

		    $file = $acpt_field['file'];

		    $this->assertNotNull($file);
		    $this->assertNotEmpty(get_post_meta($this->oldest_page_id, 'box_name_file_id', true));
		    $this->assertEquals('This is a label', $acpt_field['label']);

		    $this->deleteFile($url);
	    }
    }

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
    public function can_edit_acpt_meta_field_value()
    {
	    $txt = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/github.txt');
	    $url = $txt['url'];

	    foreach ($this->dataProvider() as $key => $value){

		    $edit_acpt_meta_field_value = save_acpt_meta_field_value([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'file',
			    'value' => [
				    'label' => 'This is a label',
				    'url' => $url
			    ],
		    ]);

		    $this->assertTrue($edit_acpt_meta_field_value);

		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'file',
		    ]);

		    $file = $acpt_field['file'];

		    $this->assertNotNull($file);
		    $this->assertEquals('This is a label', $acpt_field['label']);
	    }

	    return $url;
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
			    'field_name' => 'file',
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
			    'field_name' => 'file',
		    ]);

		    $this->assertTrue($delete_acpt_meta_field_value);
	    }

	    $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

	    $this->assertTrue($delete_acpt_meta_box);

	    foreach ($this->dataProvider() as $key => $value){
		    $acpt_field = get_acpt_field([
			    $key => $value,
			    'box_name' => 'box_name',
			    'field_name' => 'file',
		    ]);

		    $this->assertNull($acpt_field);
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_acpt_option_page);
    }
}