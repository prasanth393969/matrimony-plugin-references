<?php

namespace ACPT\Integrations\GenerateBlocks;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\GenerateBlocks\Provider\DynamicDataProvider;
use ACPT\Integrations\GenerateBlocks\Provider\DynamicTags;

class ACPT_GenerateBlocks extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "generate_blocks";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
        $isActive = is_plugin_active( 'generateblocks/plugin.php' );

        if(!$isActive){
            return false;
        }

        return ACPT_ENABLE_META == 1 and $isActive;
    }

    /**
     * @inheritDoc
     */
    protected function runIntegration()
    {
        include_once __DIR__. "/../../../../generateblocks/includes/utils/class-singleton.php";
        include_once __DIR__. "/../../../../generateblocks/includes/dynamic-tags/class-dynamic-tag-callbacks.php";
        include_once __DIR__. "/../../../../generateblocks/includes/dynamic-tags/class-register-dynamic-tag.php";

        DynamicDataProvider::get_instance()->init();

        // if GB PRO is activated
        if( is_plugin_active( 'generateblocks-pro/plugin.php' )){
            include_once __DIR__. "/../../../../generateblocks-pro/includes/class-singleton.php";
            DynamicTags::get_instance()->init();
        }
    }
}