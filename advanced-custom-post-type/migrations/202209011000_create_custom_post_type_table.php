<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateCustomPostTypeTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE."` (
                `id` varchar(36) NOT NULL,
                `post_name` varchar(20) NOT NULL,
                `singular` varchar(255)  NOT NULL,
                `plural` varchar(255) NOT NULL,
                `icon` text NOT NULL,
                `native` tinyint(1) DEFAULT '0',
                `supports` text,
                `labels` text,
                `settings` text,
	            PRIMARY KEY (`id`),
                UNIQUE KEY `id` (`id`),
                UNIQUE KEY `post_name` (`post_name`)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_CUSTOM_POST_TYPE),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_CUSTOM_POST_TYPE),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE)),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '1.0.197';
	}
}