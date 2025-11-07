<?php

namespace ACPT\Integrations\SlimSeo;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\SlimSeo\Provider\SlimSeoProvider;
use ACPT\Integrations\SlimSeo\Provider\SlimSeoSchemaProvider;

class ACPT_SlimSeo extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "slim_seo";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
        if(ACPT_ENABLE_META and is_plugin_active('slim-seo/slim-seo.php')){
            return true;
        }

        return ACPT_ENABLE_META and is_plugin_active( 'slim-seo-schema/slim-seo-schema.php' );
    }

    /**
     * @return mixed|void
     */
    protected function runIntegration()
    {
        new SlimSeoProvider();
        new SlimSeoSchemaProvider();
    }
}
