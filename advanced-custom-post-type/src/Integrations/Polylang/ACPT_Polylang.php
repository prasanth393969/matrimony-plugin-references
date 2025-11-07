<?php

namespace ACPT\Integrations\Polylang;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Polylang\Helper\PolylangChecker;
use ACPT\Integrations\Polylang\Strings\PolylangStrings;
use ACPT\Integrations\WPML\Helper\WPMLConfig;

class ACPT_Polylang extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "polylang";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = PolylangChecker::isActive();

		if(!$isActive){
			return false;
		}

		if(ACPT_ENABLE_META != 1){
			$isActive = false;
		}

		return $isActive;
	}

	/**
	 * @see https://polylang.pro/doc/filter-reference/
	 * @inheritDoc
	 */
	protected function runIntegration()
	{
	    if(!WPMLConfig::fileExists()){
            WPMLConfig::generateFromCache();
        }

		// register PLL strings
		add_action('pll_init', function() {
			try {
				$strings = new PolylangStrings();
				$strings->register();
			} catch (\Exception $exception){
                do_action("acpt/error", $exception);
            }
		});
	}
}
