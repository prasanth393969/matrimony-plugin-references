<?php

use ACPT\Constants\MetaTypes;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Integrations\WPAllExport\Helper\WPAEXmlFormatter;

if(!function_exists('wpae_acpt_export_all_fields'))
{
	/**
	 * Usage in WP All Export:
	 * [wpae_acpt_export_all_fields({ID})]
	 *
	 * @param $postId
	 *
	 * @return string
	 * @throws Exception
	 */
	function wpae_acpt_export_all_fields($postId)
	{
		$postType = get_post_type($postId);
		$metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
			'find' => $postType,
		]);

		$xml = WPAEXmlFormatter::formatMetadata($postId, MetaTypes::CUSTOM_POST_TYPE, $postType, $metaGroups);

		return WPAEXmlFormatter::removeCDATA($xml);
	}
}