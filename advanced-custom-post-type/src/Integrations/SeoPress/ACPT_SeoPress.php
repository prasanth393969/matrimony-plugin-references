<?php

namespace ACPT\Integrations\SeoPress;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\SeoPress\Provider\SeoPressProvider;

class ACPT_SeoPress extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "seopress";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
        if(ACPT_ENABLE_META and is_plugin_active('wp-seopress/seopress.php')){
            return true;
        }

        return ACPT_ENABLE_META and is_plugin_active( 'wp-seopress-pro/seopress-pro.php' );
    }

    /**
     * @see https://www.seopress.org/support/guides/how-to-integrate-advanced-custom-fields-acf-with-seopress/
     */
    protected function runIntegration()
    {
        $provider = new SeoPressProvider();
        $provider->run();
    }
}
