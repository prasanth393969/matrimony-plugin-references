<?php

namespace ACPT\Integrations\Breakdance;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Breakdance\Provider\BreakdanceProvider;

class ACPT_Breakdance extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "breakdance";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
        if(!ACPT_ENABLE_META){
            return false;
        }

        $isActive = is_plugin_active( 'breakdance/plugin.php' );

        if($isActive){
            return true;
        }

        // Oxygen >= 6.0
        $isOxygen6Active = is_plugin_active( 'oxygen/plugin.php' );

        if($isOxygen6Active){
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function runIntegration()
    {
        add_action('init', function() {

            if (!function_exists('\Breakdance\DynamicData\registerField') or !class_exists('\Breakdance\DynamicData\Field')) {
                return;
            }

            BreakdanceProvider::init();
        });
    }
}
