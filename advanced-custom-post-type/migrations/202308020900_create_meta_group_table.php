<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateMetaGroupsTable extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            group_name VARCHAR(".ACPT_DB::keyLength().") UNIQUE NOT NULL,
	            label VARCHAR(255) NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BELONG)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            belongs VARCHAR(36) NOT NULL,
	            operator VARCHAR(20) NULL,
	            find VARCHAR(255) NULL,
	            logic VARCHAR(3) DEFAULT NULL,
	            sort INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP_BELONG)."` (
	            group_id VARCHAR(36) NOT NULL,
	            belong_id VARCHAR(36) NOT NULL,
	             PRIMARY KEY( `group_id`, `belong_id`)
	        ) ".ACPT_DB::getCharsetCollation().";",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP)),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BELONG)),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP_BELONG)),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.0-beta-rc1';
	}
}