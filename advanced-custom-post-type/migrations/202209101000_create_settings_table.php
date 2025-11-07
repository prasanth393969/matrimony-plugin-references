<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateSettingsTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_SETTINGS."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            meta_key VARCHAR(32) UNIQUE NOT NULL,
	            meta_value VARCHAR(255) NOT NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_SETTINGS),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_SETTINGS),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_SETTINGS)),
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