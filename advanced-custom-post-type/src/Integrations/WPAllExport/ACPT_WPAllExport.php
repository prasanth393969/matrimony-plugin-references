<?php

namespace ACPT\Integrations\WPAllExport;

use ACPT\Integrations\AbstractIntegration;

class ACPT_WPAllExport extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "wp_all_export_pro";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = is_plugin_active( 'wp-all-export-pro/wp-all-export-pro.php' ) or is_plugin_active( 'wp-all-export/wp-all-export.php' );

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
		require_once ACPT_PLUGIN_DIR_PATH.'/functions/_inc/wp_all_export/wpae_acpt_export_all_fields.php';
		require_once ACPT_PLUGIN_DIR_PATH.'/functions/_inc/wp_all_export/wpae_acpt_export_all_tax_fields.php';
		require_once ACPT_PLUGIN_DIR_PATH.'/functions/_inc/wp_all_export/wpae_acpt_export_all_user_fields.php';
	}
}