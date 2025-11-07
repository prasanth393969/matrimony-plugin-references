<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateApiKeysTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_API_KEYS."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
                uid INT(11) UNIQUE NOT NULL,
                api_key VARCHAR(36) NOT NULL,
                api_secret VARCHAR(36) NOT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY(`id`, `uid`),
                UNIQUE KEY `api_key_and_secret` (`api_key`, `api_secret`) USING BTREE
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_API_KEYS),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_API_KEYS),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_API_KEYS)),
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