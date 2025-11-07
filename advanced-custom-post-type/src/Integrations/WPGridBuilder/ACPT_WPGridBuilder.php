<?php

namespace ACPT\Integrations\WPGridBuilder;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\WPGridBuilder\Provider\WPGridBuilderDataProvider;

class ACPT_WPGridBuilder extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "wp_grid_builder";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = is_plugin_active( 'wp-grid-builder/wp-grid-builder.php' );

		if(!$isActive){
			return false;
		}

		return ACPT_ENABLE_META and $isActive;
	}

	/**
	 * @inheritDoc
	 */
	protected function runIntegration()
	{
		new WPGridBuilderDataProvider();
	}
}
