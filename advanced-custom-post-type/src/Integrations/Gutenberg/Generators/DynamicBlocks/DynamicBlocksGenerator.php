<?php

namespace ACPT\Integrations\Gutenberg\Generators\DynamicBlocks;

use ACPT\Core\Repository\DynamicBlockRepository;

class DynamicBlocksGenerator
{
    /**
     * @var array
     */
    private array $blocks;

    /**
     * DynamicBlocksGenerator constructor.
     */
    public function __construct()
    {
        $blocks = DynamicBlockRepository::get([]);
        $this->blocks = $blocks;
    }

    /**
     * Generate dynamic gutenberg blocks
     */
    public function generate()
    {
        if(!empty($this->blocks)){
            foreach ($this->blocks as $block){
                $generator = new DynamicBlockGenerator($block);
                $generator->generate();
            }
        }
    }
}