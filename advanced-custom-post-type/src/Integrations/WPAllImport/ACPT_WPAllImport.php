<?php

namespace ACPT\Integrations\WPAllImport;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\WPAllImport\Addon\WPAIAddon;

class ACPT_WPAllImport extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "wp-all-import-pro";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = is_plugin_active( 'wp-all-import-pro/wp-all-import-pro.php' ) or is_plugin_active( 'wp-all-import/wp-all-import.php' );

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
		WPAIAddon::getInstance();
	}
}