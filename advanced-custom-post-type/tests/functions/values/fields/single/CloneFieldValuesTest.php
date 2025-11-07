<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;

class CloneFieldValuesTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_add_acpt_meta_field_value()
    {
        save_acpt_meta_group([
            'name' => 'reusable-group',
            'label' => 'Reusable group',
            'belongs' => [],
            'boxes' => [
                [
                    'name' => 'meta',
                    'label' => 'meta',
                    'fields' => [
                        [
                            'name' => 'text',
                            'label' => 'text',
                            'type' => MetaFieldModel::TEXT_TYPE,
                            'showInArchive' => false,
                            'isRequired' => false,
                            'defaultValue' => null,
                            'description' => "lorem ipsum dolor facium",
                        ],
                        [
                            'name' => 'forged_email',
                            'label' => 'forged email',
                            'type' => MetaFieldModel::EMAIL_TYPE,
                            'showInArchive' => false,
                            'isRequired' => false,
                            'defaultValue' => null,
                            'description' => "lorem ipsum dolor facium",
                        ]
                    ]
                ],
            ]
        ]);

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
						    'name' => 'clone',
						    'label' => 'clone',
						    'type' => MetaFieldModel::CLONE_TYPE,
						    'showInArchive' => false,
						    'isRequired' => false,
						    'clonedFields' => [
						        [
						            'box_name' => 'meta',
						            'field_name' => 'text',
                                ],
                                [
                                    'box_name' => 'meta',
                                    'field_name' => 'forged_email',
                                ]
                            ],
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

            foreach ($this->clonedFields() as $item){
                $add_acpt_meta_field_value = save_acpt_meta_field_value([
                    $key => $value,
                    'box_name' => $item['box_name'],
                    'field_name' => $item['field_name'],
                    'value' => $item['value'],
                    'forged_by' => [
                        'box_name' => 'box_name',
                        'field_name' => 'clone',
                    ],
                ]);

                $this->assertTrue($add_acpt_meta_field_value);

                $acpt_field = get_acpt_field([
                    $key => $value,
                    'box_name' => $item['box_name'],
                    'field_name' => $item['field_name'],
                ]);

                $this->assertEquals($item['value'], $acpt_field);
            }
	    }
    }

    /**
     * @test
     */
    public function can_delete_acpt_meta_field_value()
    {
	    foreach ($this->dataProvider() as $key => $value){
            foreach ($this->clonedFields() as $item){
                $delete_acpt_meta_field_value = delete_acpt_meta_field_value([
                    $key => $value,
                    'box_name' => $item['box_name'],
                    'field_name' => $item['field_name'],
                    'forged_by' => [
                        'box_name' => 'box_name',
                        'field_name' => 'clone',
                    ],
                ]);

                $this->assertTrue($delete_acpt_meta_field_value);
            }
	    }

	    $delete_group = delete_acpt_meta_group('new-group');
	    $delete_reusable_group = delete_acpt_meta_group('reusable-group');
	    $delete_acpt_option_page = delete_acpt_option_page('new-page', true);

	    $this->assertTrue($delete_group);
	    $this->assertTrue($delete_reusable_group);
	    $this->assertTrue($delete_acpt_option_page);
    }

    /**
     * @return array
     */
    private function clonedFields()
    {
        return [
            [
                'box_name' => 'meta',
                'field_name' => 'text',
                'value' => 'This is a cloned text',
            ],
            [
                'box_name' => 'meta',
                'field_name' => 'forged_email',
                'value' => 'mauro@acpt.io'
            ],
        ];
    }
}