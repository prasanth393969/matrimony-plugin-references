<?php

namespace ACPT\Integrations\SlimSeo\Provider;

class SlimSeoProvider extends AbstractSlimSeoProvider
{
    /**
     * Run the integration
     * @see https://docs.wpslimseo.com/slim-seo-schema/integrations/acf/
     */
    public function init(): void
    {
        add_filter( 'slim_seo_variables', [ $this, 'addVariables' ] );
        add_filter( 'slim_seo_data', [ $this, 'addData' ], 10, 3 );
    }
}
