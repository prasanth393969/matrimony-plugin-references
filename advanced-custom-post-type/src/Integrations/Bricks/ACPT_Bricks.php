<?php

namespace ACPT\Integrations\Bricks;

use ACPT\Includes\ACPT_Plugin;
use ACPT\Integrations\AbstractIntegration;

class ACPT_Bricks extends AbstractIntegration
{
	const MINIMUM_BRICKS_VERSION = '1.6.2';

    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "bricks";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
        $theme = wp_get_theme();

		if(( 'Bricks' == $theme->name or 'Bricks' == $theme->parent_theme )){
			return ACPT_ENABLE_META == 1 and $this->checkThemeVersion($theme, self::MINIMUM_BRICKS_VERSION);
		}

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function runIntegration()
    {
        // Dynamic data provider
	    add_filter( 'bricks/dynamic_data/register_providers', function( $providers ) {

		    require_once ACPT_PLUGIN_DIR_PATH . '/src/Integrations/Bricks/providers/provider-acpt.php';

		    if ( class_exists( ACPT_Plugin::class ) ) {
			    $providers[] = 'acpt';
		    }

		    return $providers;
	    } );

	    // Query filters
	    add_filter('bricks/query_filters_cf_integration/register_providers', function ($providers){
            require_once ACPT_PLUGIN_DIR_PATH . '/src/Integrations/Bricks/query-filters/field-acpt.php';

            if ( class_exists( ACPT_Plugin::class ) ) {
                $providers[] = 'acpt';
            }

            return $providers;
        });
    }
}
