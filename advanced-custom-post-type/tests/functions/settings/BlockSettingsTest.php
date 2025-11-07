<?php

namespace ACPT\Tests;

use ACPT\Core\Models\DynamicBlock\DynamicBlockControlModel;
use ACPT\Includes\ACPT_DB;

class BlockSettingsTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_register_edit_and_delete_a_simple_block()
	{
		ACPT_DB::flushCache();

		$new_block = save_acpt_block([
			'name' => 'new-block',
            'title' => 'New block',
            'category' => 'text',
            'icon' => 'menu',
            'css' => null,
            'callback' => "<div>Hello world! This is {{test}}!</div>",
            'keywords' => ['acpt', 'twig'],
            'postTypes' => ['post', 'page'],
            'supports' => [],
            'controls' => [
                [
                    'name' => 'text-control',
                    'label' => 'Text control',
                    'type' => DynamicBlockControlModel::TEXT_TYPE,
                    'default' => 'test',
                    'description' => 'lorem ipsum',
                ],
                [
                    'name' => 'select-control',
                    'label' => 'Select control',
                    'type' => DynamicBlockControlModel::SELECT_TYPE,
                    'default' => 'test',
                    'description' => 'lorem ipsum',
                    'options' => [
                        'test' => 'Test',
                        'test2' => 'Test2',
                        'test3' => 'Test3',
                        'test4' => 'Test4',
                    ],
                ],
            ],
		]);

		$this->assertTrue($new_block);

		$block_object = get_acpt_block_object('new-block');

		$this->assertNotNull($block_object);
		$this->assertEquals($block_object->name, 'new-block');
		$this->assertEquals($block_object->title, 'New block');
		$this->assertEquals($block_object->category, 'text');
		$this->assertEquals($block_object->icon, 'menu');
		$this->assertEquals($block_object->keywords, ['acpt', 'twig']);
		$this->assertEquals($block_object->postTypes, ['post', 'page']);

		$edit_block = save_acpt_block([
            'name' => 'new-block',
            'title' => 'New block modified',
            'category' => 'text',
            'icon' => 'menu',
            'css' => null,
            'callback' => "<div>Hello world! This is {{test}} modified!</div>",
            'keywords' => ['acpt', 'twig'],
            'postTypes' => ['post', 'page'],
            'supports' => [],
            'controls' => [
                [
                    'name' => 'text-control',
                    'label' => 'Text control',
                    'type' => DynamicBlockControlModel::TEXT_TYPE,
                    'default' => 'test',
                    'description' => 'lorem ipsum',
                ],
                [
                    'name' => 'select-control',
                    'label' => 'Select control',
                    'type' => DynamicBlockControlModel::SELECT_TYPE,
                    'default' => 'test',
                    'description' => 'lorem ipsum',
                    'options' => [
                        'test' => 'Test',
                        'test2' => 'Test2',
                        'test3' => 'Test3',
                        'test4' => 'Test4',
                    ],
                ],
            ],
		]);

		$this->assertTrue($edit_block);

        $block_object = get_acpt_block_object('new-block');

        $this->assertEquals($block_object->title, 'New block modified');

		$delete_block = delete_acpt_block('new-block');

		$this->assertTrue($delete_block);
	}
}