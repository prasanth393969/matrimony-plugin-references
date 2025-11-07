<?php

namespace ACPT\Tests;

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Relationships;
use ACPT\Core\Models\Meta\MetaFieldModel;

class PostFieldValuesTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_add_acpt_meta_field_value()
    {
	    $groupName = 'page';
	    $save_acpt_meta_group = save_acpt_meta_group([
		    'name' => $groupName,
		    'belongs' => [
			    [
				    'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
				    'operator'  => "=",
				    "find"      => "page",
			    ],
		    ],
	    ]);

	    $this->assertTrue($save_acpt_meta_group);

	    $save_meta_box = save_acpt_meta_box([
		    'groupName' => $groupName,
		    'name' => 'box_name',
		    'label' => 'box label',
		    'fields' => [
			    [
				    'name' => 'post_relation',
				    'type' => MetaFieldModel::EMAIL_TYPE,
				    'showInArchive' => false,
				    'isRequired' => false,
			    ],
			    [
				    'name' => 'field_name',
				    'type' => MetaFieldModel::EMAIL_TYPE,
				    'showInArchive' => false,
				    'isRequired' => false,
			    ]
		    ]
	    ]);

	    $this->assertTrue($save_meta_box);

	    $save_meta_box = save_acpt_meta_box([
		    'groupName' => $groupName,
		    'name' => 'box_name',
		    'label' => 'box label',
		    'fields' => [
			    [
				    'name' => 'post_relation',
				    'type' => MetaFieldModel::POST_TYPE,
				    'showInArchive' => false,
				    'isRequired' => false,
				    'relations' => [
					    [
						    'from' => [
							    'type' => MetaTypes::CUSTOM_POST_TYPE,
							    'value' => 'page',
						    ],
						    'to' => [
							    'type' => MetaTypes::CUSTOM_POST_TYPE,
							    'value' => 'post',
						    ],
						    'relationship' =>  Relationships::ONE_TO_ONE_BI,
						    'inversedBoxName' => 'box_name',
						    'inversedFieldName' => 'field_name',
					    ],
				    ]
			    ],
			    [
				    'name' => 'field_name',
				    'type' => MetaFieldModel::POST_TYPE,
				    'showInArchive' => false,
				    'isRequired' => false,
				    'relations' => [
					    [
						    'from' => [
							    'type' => MetaTypes::CUSTOM_POST_TYPE,
							    'value' => 'post',
						    ],
						    'to' => [
							    'type' => MetaTypes::CUSTOM_POST_TYPE,
							    'value' => 'page',
						    ],
						    'relationship' =>  Relationships::ONE_TO_ONE_BI,
						    'inversedBoxName' => 'box_name',
						    'inversedFieldName' => 'post_relation',
					    ],
				    ]
			    ]
		    ]
	    ]);

	    $this->assertTrue($save_meta_box);

        $add_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_post_id,
            'box_name' => 'box_name',
            'field_name' => 'post_relation',
            'value' => $this->oldest_page_id,
        ]);

        $this->assertTrue($add_acpt_meta_field_value);

        $acpt_field = get_acpt_field([
            'post_id' => $this->oldest_post_id,
            'box_name' => 'box_name',
            'field_name' => 'post_relation',
        ]);

        $inversed_acpt_field = get_acpt_field([
            'post_id' => $this->oldest_page_id,
            'box_name' => 'box_name',
            'field_name' => 'field_name',
        ]);

        /** @var \WP_Post $a */
        $a = $acpt_field[0];

        /** @var \WP_Post $b */
	    $b = $inversed_acpt_field[0];

        $this->assertEquals($this->oldest_page_id, $a->ID);
        $this->assertEquals($this->oldest_post_id, $b->ID);
    }

    /**
     * @depends can_add_acpt_meta_field_value
     * @test
     */
    public function can_edit_acpt_meta_field_value()
    {
        $edit_acpt_meta_field_value = save_acpt_meta_field_value([
            'post_id' => $this->oldest_post_id,
            'box_name' => 'box_name',
            'field_name' => 'post_relation',
            'value' => $this->second_oldest_page_id,
        ]);

        $this->assertTrue($edit_acpt_meta_field_value);

        $acpt_field = get_acpt_field([
                'post_id' => $this->oldest_post_id,
                'box_name' => 'box_name',
                'field_name' => 'post_relation',
        ]);

	    /** @var \WP_Post $a */
	    $a = $acpt_field[0];

        $this->assertEquals($this->second_oldest_page_id, $a->ID);
    }

    /**
     * @depends can_edit_acpt_meta_field_value
     * @test
     */
    public function can_delete_acpt_meta_field_value()
    {
        $delete_acpt_meta_field_value = delete_acpt_meta_field_value([
                'post_id' => $this->oldest_page_id,
                'box_name' => 'box_name',
                'field_name' => 'field_name',
        ]);

        $this->assertTrue($delete_acpt_meta_field_value);

        $delete_acpt_meta_field_value = delete_acpt_meta_field_value([
                'post_id' => $this->oldest_post_id,
                'box_name' => 'box_name',
                'field_name' => 'post_relation',
        ]);

        $this->assertTrue($delete_acpt_meta_field_value);

        $delete_acpt_meta_box = delete_acpt_meta_box('page', 'box_name');

        $this->assertTrue($delete_acpt_meta_box);

        $acpt_field = get_acpt_field([
                'post_id' => $this->oldest_page_id,
                'box_name' => 'box_name',
                'field_name' => 'field_name',
        ]);

        $this->assertNull($acpt_field);

	    $delete_acpt_meta_group = delete_acpt_meta_group('page');

	    $this->assertTrue($delete_acpt_meta_group);
    }
}