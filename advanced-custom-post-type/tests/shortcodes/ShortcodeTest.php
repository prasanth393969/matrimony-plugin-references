<?php

namespace ACPT\Tests;

use ACPT\Core\Models\Meta\MetaFieldModel;

class ShortcodeTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_display_a_list_with_before()
    {
	    $new_group = save_acpt_meta_group([
		    'name' => 'new-group',
		    'label' => 'New group',
	    ]);

	    $this->assertTrue($new_group);

	    $new_box = save_acpt_meta_box([
		    'groupName' => 'new-group',
		    'name' => 'box_name',
		    'label' => null,
		    'fields' => []
	    ]);

	    $this->assertTrue($new_box);

	    $new_field = save_acpt_meta_field(
            [
	            'groupName' => 'new-group',
                'boxName' => 'box_name',
                'name' => 'list_field',
                'type' => MetaFieldModel::LIST_TYPE,
                'showInArchive' => false,
                'isRequired' => false,
                'advancedOptions' => [
                    [
                        'key' => 'before',
                        'value' => '<p>',
                    ],
                    [
                        'key' => 'after',
                        'value' => '</p>',
                    ],
                ]
            ]
        );

	    $this->assertTrue($new_field);

	    $save_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'list_field',
            'value' => [
                "text text",
                "bla bla",
            ],
        ]);

	    $this->assertTrue($save_acpt_meta_field_value);

        $shortcode = do_shortcode('[acpt list="ul" pid="'.$this->oldest_page_id.'" box="box_name" field="list_field"]');

        $this->assertEquals($shortcode, '<ul><li class="">&lt;p&gt;text text&lt;/p&gt;</li><li class="">&lt;p&gt;bla bla&lt;/p&gt;</li></ul>');

	    $shortcode = do_shortcode('[acpt list="ol" pid="'.$this->oldest_page_id.'" box="box_name" field="list_field"]');

	    $this->assertEquals($shortcode, '<ol><li class="">&lt;p&gt;text text&lt;/p&gt;</li><li class="">&lt;p&gt;bla bla&lt;/p&gt;</li></ol>');

	    $shortcode = do_shortcode('[acpt pid="'.$this->oldest_page_id.'" box="box_name" field="list_field"]');

	    $this->assertEquals($shortcode, '<p>text text</p>,<p>bla bla</p>');
    }

    /**
     * @test
     */
    public function can_display_a_select_multi_shortcode_with_before()
    {
	    $new_field = save_acpt_meta_field(
            [
	            'groupName' => 'new-group',
                'boxName' => 'box_name',
                'name' => 'select_multi',
                'type' => MetaFieldModel::SELECT_MULTI_TYPE,
                'showInArchive' => false,
                'isRequired' => false,
                'options' => [
                    [
                        'value' => 'foo',
                        'label' => 'Label foo',
	                    'isDefault' => false,
                    ],
                    [
                        'value' => 'bar',
                        'label' => 'Label bar',
	                    'isDefault' => false,
                    ],
                    [
                        'value' => 'fuzz',
                        'label' => 'Label fuzz',
	                    'isDefault' => false,
                    ],
                ],
                'advancedOptions' => [
                    [
                        'key' => 'before',
                        'value' => 'Element: ',
                    ],
                ]
            ]
        );

	    $this->assertTrue($new_field);

	    $save_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'select_multi',
            'value' => [
                    "foo",
                    "fuzz",
            ],
        ]);

	    $this->assertTrue($save_acpt_meta_field_value);

        $shortcode = do_shortcode('[acpt pid="'.$this->oldest_page_id.'" box="box_name" field="select_multi"]');

        $this->assertEquals('Element: foo,Element: fuzz', $shortcode);
    }

	/**
	 * @test
	 */
	public function can_display_a_checkbox_shortcode_with_before()
	{
		$new_field = save_acpt_meta_field(
			[
				'groupName' => 'new-group',
				'boxName' => 'box_name',
				'name' => 'checkbox',
				'type' => MetaFieldModel::CHECKBOX_TYPE,
				'showInArchive' => false,
				'isRequired' => false,
				'options' => [
					[
						'value' => 'foo',
						'label' => 'Label foo',
						'isDefault' => false,
					],
					[
						'value' => 'bar',
						'label' => 'Label bar',
						'isDefault' => false,
					],
					[
						'value' => 'fuzz',
						'label' => 'Label fuzz',
						'isDefault' => false,
					],
				],
				'advancedOptions' => [
					[
						'key' => 'before',
						'value' => 'Element: ',
					],
				]
			]
		);

		$this->assertTrue($new_field);

		$save_acpt_meta_field_value = save_acpt_meta_field_value([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'box_name',
			'field_name' => 'checkbox',
			'value' => [
				"foo",
				"fuzz",
			],
		]);

		$this->assertTrue($save_acpt_meta_field_value);

		$shortcode = do_shortcode('[acpt pid="'.$this->oldest_page_id.'" box="box_name" field="checkbox"]');

		$this->assertEquals('Element: foo,Element: fuzz', $shortcode);
	}

    /**
     * @test
     */
    public function can_display_an_email_shortcode_with_before()
    {
	    $new_field = save_acpt_meta_field(
            [
	            'groupName' => 'new-group',
                'boxName' => 'box_name',
                'name' => 'email',
                'type' => MetaFieldModel::EMAIL_TYPE,
                'showInArchive' => false,
                'isRequired' => false,
                'advancedOptions' => [
                    [
                        'key' => 'before',
                        'value' => 'Send email to: ',
                    ],
                ]
            ]
        );

	    $this->assertTrue($new_field);

	    $save_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'email',
            'value' => "info@acpt.io",
        ]);

	    $this->assertTrue($save_acpt_meta_field_value);

        $shortcode = do_shortcode('[acpt pid="'.$this->oldest_page_id.'" box="box_name" field="email"]');

        $this->assertEquals('Send email to: <a href="mailto:info@acpt.io">info@acpt.io</a>', $shortcode);

        delete_acpt_meta_box('page', 'Box name');
    }

    /**
     * @test
     */
    public function can_display_a_text_shortcode_with_before_and_after()
    {
	    $new_field = save_acpt_meta_field(
            [
	            'groupName' => 'new-group',
                'boxName' => 'box_name',
                'name' => 'field_name',
                'type' => MetaFieldModel::TEXT_TYPE,
                'showInArchive' => false,
                'isRequired' => false,
                'advancedOptions' => [
                    [
                        'key' => 'before',
                        'value' => '<p>',
                    ],
                    [
                        'key' => 'after',
                        'value' => '</p>',
                    ],
                ]
            ]
        );

	    $this->assertTrue($new_field);

	    $save_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'field_name',
            'value' => "text text",
        ]);

	    $this->assertTrue($save_acpt_meta_field_value);

        $shortcode = do_shortcode('[acpt pid="'.$this->oldest_page_id.'" box="box_name" field="field_name"]');

        $this->assertEquals('<p>text text</p>', $shortcode);

	    delete_acpt_meta_box('new-group', 'box_name');
    }
}