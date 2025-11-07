<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Utils\Wordpress\WPAttachment;

class AudioFieldValuesTest extends AbstractTestCase
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
                            'name' => 'audio',
                            'label' => 'audio',
                            'type' => MetaFieldModel::AUDIO_TYPE,
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
                'field_name' => 'audio',
                'value' => "http://localhost:83/wp-content/uploads/not-existing.txt",
            ]);

            $this->assertFalse($add_acpt_meta_field_value);

            // not an audio
            $image = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/image1.jpg');
            $imageUrl = $image['url'];

            $add_acpt_meta_field_value = save_acpt_meta_field_value([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
                'value' => $imageUrl,
            ]);

            $this->assertFalse($add_acpt_meta_field_value);

            // audio
            $audio = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/sample-15s.mp3');
            $audioId = $audio['attachmentId'];
            $audioUrl = $audio['url'];

            $add_acpt_meta_field_value = save_acpt_meta_field_value([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
                'value' => $audioUrl,
            ]);

            $this->assertTrue($add_acpt_meta_field_value);

            $acpt_field = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
            ]);

            $this->assertInstanceOf(WPAttachment::class, $acpt_field);
            $this->assertStringContainsString($acpt_field->getMetadata()['fileformat'], "mp3");
            $this->assertStringContainsString($acpt_field->getMetadata()['mime_type'], "audio/mpeg");

            $acpt_field_raw = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
                'return' => 'raw'
            ]);

            $this->assertIsInt($acpt_field_raw);

            $this->deleteFile($imageUrl);
            $this->deleteFile($audioUrl);
        }
    }

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
    public function can_edit_acpt_meta_field_value()
    {
        $audio = $this->uploadFile(__DIR__.'/../../../../../tests/_inc/support/files/sample-12s.mp3');
        $audioId = $audio['attachmentId'];
        $audioUrl = $audio['url'];

        foreach ($this->dataProvider() as $key => $value){

            $edit_acpt_meta_field_value = save_acpt_meta_field_value([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
                'value' => $audioUrl,
            ]);

            $this->assertTrue($edit_acpt_meta_field_value);

            $acpt_field = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
            ]);

            $this->assertInstanceOf(WPAttachment::class, $acpt_field);
            $this->assertStringContainsString($acpt_field->getMetadata()['fileformat'], "mp3");
            $this->assertStringContainsString($acpt_field->getMetadata()['mime_type'], "audio/mpeg");

            $acpt_field_raw = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
                'return' => 'raw'
            ]);

            $this->assertIsInt($acpt_field_raw);
        }

        return $audioUrl;
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
                'field_name' => 'audio',
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
                'field_name' => 'audio',
            ]);

            $this->assertTrue($delete_acpt_meta_field_value);
        }

        $delete_acpt_meta_box = delete_acpt_meta_box('new-group', 'box_name');

        $this->assertTrue($delete_acpt_meta_box);

        foreach ($this->dataProvider() as $key => $value){
            $acpt_field = get_acpt_field([
                $key => $value,
                'box_name' => 'box_name',
                'field_name' => 'audio',
            ]);

            $this->assertNull($acpt_field);
        }

        $delete_group = delete_acpt_meta_group('new-group');
        $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

        $this->assertTrue($delete_group);
        $this->assertTrue($delete_acpt_option_page);
    }
}

