<?php

namespace ACPT\Integrations\WPML;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\WPML\Helper\WPMLChecker;
use ACPT\Integrations\WPML\Helper\WPMLConfig;

class ACPT_WPML extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "wpml";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = WPMLChecker::isActive();

		if(!$isActive){
			return false;
		}

		if(ACPT_ENABLE_META != 1){
			$isActive = false;
		}

		return $isActive;
	}

	/**
	 * @inheritDoc
	 */
	protected function runIntegration()
	{
        if(!WPMLConfig::fileExists()){
            WPMLConfig::generateFromCache();
        }
	}
}
