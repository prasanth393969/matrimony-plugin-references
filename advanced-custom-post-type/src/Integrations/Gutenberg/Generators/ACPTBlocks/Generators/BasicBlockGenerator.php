<?php

namespace ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Generators;

use ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Blocks\BasicBlockRenderer;

class BasicBlockGenerator extends AbstractACPTBlockGenerator
{
    /**
     * Generate the ACPT Basic block
     */
    public function generate()
    {
        $this->registerBlock();
        $this->enqueueScript();
    }

    /**
     * Register the block calling register_block_type
     */
    private function registerBlock()
    {
        register_block_type( 'advanced-custom-post-type/basic-block', [
            'api_version' => 3,
            'editor_script' => 'block_js',
            'editor_style' => 'block_css',
            'keywords' => [
                'acpt',
                'meta field'
            ],
            'render_callback' => [new BasicBlockRenderer(), 'render'],
            'attributes'      => [
                'postId'      => [
                    'default' => 99999999999999999,
                    'type'    => 'integer'
                ],
                'field'            => [
                    'default' => null,
                    'type'    => 'string'
                ],
                'gradient' => [
                    'default' => null,
                    'type'    => 'string'
                ],
                'backgroundColor' => [
                    'default' => null,
                    'type'    => 'string'
                ],
                'textColor' => [
                    'default' => null,
                    'type'    => 'string'
                ],
                'style' => [
                    'default' => [],
                    'type'    => 'object'
                ],
                'align' => [
                    'default' => 'left',
                    'type'    => 'string'
                ],
                'display' => [
                    'default' => null,
                    'type'    => 'string'
                ],
                "color" => [
                    "type" => 'string',
                    "default" => null
                ],
                "target" => [
                    "type" => 'string',
                    "default" => null
                ],
                "fontSize" => [
                    "type" => 'string',
                    "default" => null
                ],
                "width" => [
                    "type" => 'string',
                    "default" => null
                ],
                "height" => [
                    "type" => 'string',
                    "default" => null
                ],
                "uomFormatDecimalPoints" => [
                    "type" => 'string',
                    "default" => null
                ],
                "uomFormatDecimalSeparator" => [
                    "type" => 'string',
                    "default" => null
                ],
                "uomFormatThousandsSeparator" => [
                    "type" => 'string',
                    "default" => null
                ],
                "uomFormat" => [
                    "type" => 'string',
                    "default" => null
                ],
                "uomPosition" => [
                    "type" => 'string',
                    "default" => null
                ],
                "phoneFormat" => [
                    "type" => 'string',
                    "default" => null
                ],
                "dateFormat" => [
                    "type" => 'string',
                    "default" => null
                ],
                "timeFormat" => [
                    "type" => 'string',
                    "default" => null
                ],
                "audioStyle" => [
                    "type" => 'string',
                    "default" => null
                ],
                "zoom" => [
                    "type" => 'integer',
                    "default" => 14
                ],
                "gap" => [
                    "type" => 'integer',
                    "default" => 20
                ],
                "elements" => [
                    "type" => 'integer',
                    "default" => 3
                ],
                "sort" => [
                    "type" => 'string',
                    "default" => null
                ],
                "border" => [
                    "type" => 'object',
                    "default" => []
                ],
                "borderRadius" => [
                    "type" => 'object',
                    "default" => []
                ],
                "prefix" => [
                    "type" => 'string',
                    "default" => null
                ],
                "suffix" => [
                    "type" => 'string',
                    "default" => null
                ],
                "padding" => [
                    "type" => 'object',
                    "default" => []
                ],
            ]
        ]);
    }

    /**
     * Enqueue scripts
     */
    private function enqueueScript()
    {
        wp_enqueue_block_style( "advanced-custom-post-type/basic-block", [
            'handle' => "advanced-custom-post-type/basic-block",
            'src'    => plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/gallery.css' : 'advanced-custom-post-type/assets/static/css/gallery.min.css')
        ] );
    }
}