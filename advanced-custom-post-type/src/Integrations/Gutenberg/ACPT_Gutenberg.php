<?php

namespace ACPT\Integrations\Gutenberg;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Generators\AbstractACPTBlockGenerator;
use ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Generators\BasicBlockGenerator;
use ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Generators\RelationalBlockGenerator;
use ACPT\Integrations\Gutenberg\Generators\ACPTBlocks\Generators\RepeaterBlockGenerator;
use ACPT\Integrations\Gutenberg\Generators\DynamicBlocks\DynamicBlocksGenerator;

class ACPT_Gutenberg extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "gutenberg";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function runIntegration()
	{
        if (!function_exists( 'register_block_type' ) ) {
            // Block editor is not available.
            return;
        }

        if(ACPT_ENABLE_META){
            add_action( 'init', [new ACPT_Gutenberg(), 'registerACPTBlocks'] );
        }

        if(ACPT_ENABLE_BLOCKS and ACPT_IS_LICENSE_VALID){
            add_action( 'init', [new ACPT_Gutenberg(), 'registerDynamicBlocks'] );
        }
	}

    /**
     * Register Dynamic blocks
     */
    public function registerDynamicBlocks()
    {
        $generator = new DynamicBlocksGenerator();
        $generator->generate();
    }

	/**
	 * Register Gutenberg blocks
	 */
	public function registerACPTBlocks()
	{
	    $generators = [
            BasicBlockGenerator::class,
            RepeaterBlockGenerator::class,
            RelationalBlockGenerator::class,
        ];

        foreach ($generators as $generator){
            /** @var AbstractACPTBlockGenerator $g */
	        $g = new $generator();
            $g->generate();
        }
	}
}
