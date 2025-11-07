<?php

namespace ACPT\Integrations\SlimSeo\Provider;

class SlimSeoSchemaProvider extends AbstractSlimSeoProvider
{
    /**
     * Run the integration
     * @see https://docs.wpslimseo.com/slim-seo-schema/integrations/acf/
     */
    public function init(): void
    {
        add_filter( 'slim_seo_schema_variables', [ $this, 'addVariables' ] );
        add_filter( 'slim_seo_schema_data', [ $this, 'addSchemaData' ] );
    }
}
