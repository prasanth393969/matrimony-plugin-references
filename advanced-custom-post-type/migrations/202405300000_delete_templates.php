<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class DeleleTemplateTables extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_TEMPLATE),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE_BELONG)),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"CREATE TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."` (
			  `id` varchar(36) COLLATE utf8mb4_unicode_520_ci NOT NULL,
			  `template_type` varchar(36) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
			  `json` text COLLATE utf8mb4_unicode_520_ci,
			  `html` text COLLATE utf8mb4_unicode_520_ci,
			  `meta` text COLLATE utf8mb4_unicode_520_ci,
			  `template_name` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id` (`id`)
			) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE_BELONG)."` (
	            template_id VARCHAR(36) NOT NULL,
	            belong_id VARCHAR(36) NOT NULL,
	             PRIMARY KEY( `template_id`, `belong_id`)
	        ) ".ACPT_DB::getCharsetCollation().";",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.10';
	}
}




