<?php

namespace ACPT\Tests;

use ACPT\Core\Models\DynamicBlock\DynamicBlockModel;

class BlockModelTest extends AbstractTestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function toArray()
    {
        $block = DynamicBlockModel::hydrateFromArray([
            'name' => 'new-block',
            'title' => 'New block',
            'category' => 'text',
            'icon' => 'menu',
            'css' => null,
            'callback' => "<div>Hello world! This is {{test}}!</div>",
            'keywords' => ['acpt', 'twig'],
            'postTypes' => ['post', 'page'],
            'supports' => [
                'min' => 1,
                'max' => 11,
                'step' => 1,
            ],
        ]);

        $std = $block->toStdObject();

        $this->assertEquals($std->keywords, ['acpt', 'twig']);
        $this->assertEquals($std->postTypes, ['post', 'page']);
        $this->assertEquals($std->supports->min, 1);
        $this->assertEquals($std->supports->max, 11);
        $this->assertEquals($std->supports->step, 1);
    }
}