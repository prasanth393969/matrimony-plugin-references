<?php

use ACPT\Constants\MetaTypes;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Integrations\WPAllExport\Helper\WPAEXmlFormatter;

if(!function_exists('wpae_acpt_export_all_user_fields'))
{
	/**
	 * Usage in WP All Export:
	 * [wpae_acpt_export_all_user_fields({ID})]
	 *
	 * @param $userId
	 *
	 * @return string
	 * @throws Exception
	 */
	function wpae_acpt_export_all_user_fields($userId)
	{
		$metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::USER,
		]);

		$xml = WPAEXmlFormatter::formatMetadata($userId, MetaTypes::USER, null, $metaGroups);

		return WPAEXmlFormatter::removeCDATA($xml);
	}
}