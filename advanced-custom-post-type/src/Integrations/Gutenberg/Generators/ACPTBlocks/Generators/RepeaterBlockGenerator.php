<?php

namespace ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Generators;

use ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Blocks\RepeaterLoopBlockRenderer;

class RepeaterBlockGenerator extends AbstractACPTBlockGenerator
{
    /**
     * Generate the ACPT Basic block
     */
    public function generate()
    {
        $this->registerBlock();
    }

    /**
     * Register the block calling register_block_type
     */
    private function registerBlock()
    {
        register_block_type('advanced-custom-post-type/repeater-block', [
            'api_version' => 3,
            'editor_script' => 'block_js',
            'editor_style' => 'block_css',
            'keywords' => [
                'acpt',
                'meta field'
            ],
            'render_callback' => [new RepeaterLoopBlockRenderer(), 'render'],
            'attributes'      => [
                'postId'      => [
                    'default' => 99999999999999999,
                    'type'    => 'integer'
                ],
                'field' => [
                    'default' => null,
                    'type'    => 'string'
                ],
                'block' => [
                    'default' => null,
                    'type'    => 'string'
                ],
            ]
        ]);
    }
}