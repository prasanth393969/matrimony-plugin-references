<?php

namespace ACPT\Integrations\Zion;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Zion\Provider\ZionProvider;

class ACPT_Zion extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "zion";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = is_plugin_active('zionbuilder-pro/zionbuilder-pro.php') or is_plugin_active('zionbuilder/zionbuilder.php');

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
		$provider = new ZionProvider();
		$provider->init();
	}
}