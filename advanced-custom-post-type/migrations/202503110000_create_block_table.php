<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateBlockTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            title VARCHAR(255) NOT NULL,
	            block_name VARCHAR(50) NOT NULL,
	            category VARCHAR(50) NOT NULL,
	            icon TEXT NOT NULL,
	            keywords TEXT DEFAULT NULL,
	            css TEXT DEFAULT NULL,
	            callback TEXT DEFAULT NULL,
	            post_types TEXT DEFAULT NULL,
	            supports TEXT DEFAULT NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
            "CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            block_id VARCHAR(50) NOT NULL,
	            control_name VARCHAR(50) NOT NULL,
	            label VARCHAR(255) NOT NULL,
	            control_type VARCHAR(50) NOT NULL,
	            description TEXT DEFAULT NULL,
	            default_value TEXT DEFAULT NULL,
	            options TEXT DEFAULT NULL,
	            settings TEXT DEFAULT NULL,
	            sort INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL)),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.24-beta-1';
	}
}




