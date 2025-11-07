<?php

namespace ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Generators;

use ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Blocks\RelationalBlockRenderer;

class RelationalBlockGenerator extends AbstractACPTBlockGenerator
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
        register_block_type('advanced-custom-post-type/relational-block', [
            'api_version' => 3,
            'editor_script' => 'block_js',
            'editor_style' => 'block_css',
            'keywords' => [
                'acpt',
                'meta field'
            ],
            'render_callback' => [new RelationalBlockRenderer(), 'render'],
            'attributes'      => [
                'postId'      => [
                    'default' => 99999999999999999,
                    'type'    => 'integer'
                ],
                'field' => [
                    'default' => null,
                    'type'    => 'string'
                ],
                'templateType' => [
                    'default' => null,
                    'type'    => 'string'
                ],
                'template' => [
                    'default' => null,
                    'type'    => 'object'
                ],
            ]
        ]);
    }
}