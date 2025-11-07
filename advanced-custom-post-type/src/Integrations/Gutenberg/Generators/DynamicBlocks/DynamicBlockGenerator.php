<?php

namespace ACPT\Integrations\Gutenberg\Generators\DynamicBlocks;

use ACPT\Core\Models\DynamicBlock\DynamicBlockModel;
use ACPT\Utils\PHP\Twig;
use ACPT\Utils\Wordpress\DynamicBlock;

class DynamicBlockGenerator
{
    /**
     * @var DynamicBlockModel
     */
    private DynamicBlockModel $block;

    /**
     * DynamicBlockGenerator constructor.
     * @param DynamicBlockModel $block
     */
    public function __construct(DynamicBlockModel $block)
    {
        $this->block = $block;
    }

    /**
     * Generate the block
     */
    public function generate()
    {
        if($this->isPostTypeEnabled()){
            $this->registerBlock();
            $this->enqueueStyles();
            $this->generateInlineStyle();
            $this->enqueueScripts();
            $this->generateInlineJs();
        }
    }

    /**
     * @return bool
     */
    private function isPostTypeEnabled()
    {
        if(wp_is_serving_rest_request() or wp_doing_ajax()){
            return true;
        }

        $postType = null;

        // BE
        if(isset($_GET['post_id'])){
            $postType = get_post_type($_GET['post_id']);
        }

        if(isset($_GET['post_type'])){
            $postType = $_GET['post_type'];
        }

        if(isset($_GET['post'])){
            $postType = get_post_type($_GET['post']);
        }

        // FE
        $postId = $_GET['p'] ?? url_to_postid($_SERVER['REQUEST_URI']);
        if (!empty($postId)) {
            $postType = get_post_type($postId);
        }

        if(empty($postType)){
            return false;
        }

        return in_array($postType, $this->block->getPostTypes());
    }

    /**
     * Register the block calling register_block_type
     */
    private function registerBlock()
    {
        register_block_type( $this->block->getBlockName(), [
            "apiVersion" => 3,
            "title" => $this->block->getTitle(),
            "name" => $this->block->getBlockName(),
            "keywords" => $this->block->getKeywords(),
            "category" => $this->block->getCategory(),
            "editor_script" => $this->block->getScriptName(),
            'editor_style'  => $this->block->getStyleName(),
            'attributes' => DynamicBlock::attributes($this->block->getControlsArray()),
            'render_callback' => function($attributes = null){
                $twig = Twig::render($this->block->getCallback(), $attributes);

                return DynamicBlock::render($twig, $attributes);
            },
        ]);
    }

    /**
     * Enqueue scripts
     */
    private function enqueueStyles()
    {
        // Extra CSS files
        wp_enqueue_block_style( $this->block->getBlockName(), [
            'handle' => 'custom-acpt-audio-player-css',
            'src'    => plugins_url( ACPT_DEV_MODE ? 'advanced-custom-post-type/assets/static/css/audio-player.css' : 'advanced-custom-post-type/assets/static/css/audio-player.min.css')
        ] );

        // Custom CSS
        if(!empty($this->block->getCSS())){
            wp_register_style($this->block->getStyleName(), '', [], ACPT_PLUGIN_VERSION, true );
            wp_enqueue_style($this->block->getStyleName());
        }
    }

    /**
     * Generate inline the needed JS
     */
    private function generateInlineStyle()
    {
        if(!empty($this->block->getCSS())){
            wp_add_inline_style( $this->block->getStyleName(), $this->block->getCSS());
        }
    }

    /**
     * Enqueue scripts
     */
    private function enqueueScripts()
    {
        wp_register_script($this->block->getScriptName(), '', [
            'wp-blocks',
            'wp-element',
            'wp-editor',
            'wp-components',
            'wp-i18n',
            'wp-server-side-render'
        ], ACPT_PLUGIN_VERSION, true );
        wp_enqueue_script($this->block->getScriptName());
    }

    /**
     * Generate inline the needed JS
     */
    private function generateInlineJs()
    {
        wp_add_inline_script( $this->block->getScriptName(), "
            ( function ( blocks, element, serverSideRender, blockEditor ) {

                const el = element.createElement;
                const registerBlockType = window.wp.blocks.registerBlockType;
                const Fields = window.wp.components;
                const ServerSideRender = serverSideRender;
                const useBlockProps = blockEditor.useBlockProps;
                const InnerBlocks = blockEditor.InnerBlocks;
                const icon = ".$this->block->generateIconSrc().";

                registerBlockType( '".$this->block->getBlockName()."', {
                
                    ".$this->block->iconCodeString()."
                    edit: function (props) {
            
                        var blockProps = useBlockProps();
            
                        return el(
                            wp.element.Fragment,
                            {},
                            el(
                                'div',
                                blockProps,
                                el( ServerSideRender, {
                                    block: '".$this->block->getBlockName()."',
                                    attributes: props.attributes,
                                })
                            ),
                            el(
                                window.wp.editor.InspectorControls,
                                {},
                                el(
                                    Fields.PanelBody,
                                    {},
                                    ".$this->block->controlsToJSElements()."
                                )
                            )
                        );
                    },
                    save: function (props) {
                        return null;
                    },
                    attributes: ".json_encode(DynamicBlock::attributes($this->block->getControlsArray())).",
                    supports: ".$this->block->supportsCodeString().",
                    selectors: {
                        filter: {
                            duotone: '.wp-block-image img'
                        }
                    }
                } );
            } )(
                window.wp.blocks,
                window.wp.element,
                window.wp.serverSideRender,
                window.wp.blockEditor
            );
        ");
    }
}