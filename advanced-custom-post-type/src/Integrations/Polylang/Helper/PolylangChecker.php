<?php

namespace ACPT\Integrations\Polylang\Helper;

class PolylangChecker
{
	/**
	 * @return bool
	 */
	public static function isActive()
	{
		return (
			is_plugin_active( 'polylang-pro/polylang.php' ) or
			is_plugin_active( 'polylang/polylang.php' )
		);
	}
}