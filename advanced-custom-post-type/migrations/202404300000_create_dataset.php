<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateDataset extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            dataset_name VARCHAR(".ACPT_DB::keyLength().") UNIQUE NOT NULL,
	            label VARCHAR(255) NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            dataset_id VARCHAR(36) NOT NULL,
	            item_label VARCHAR(50) NOT NULL,
	            item_value VARCHAR(50) NOT NULL,
	            is_default TINYINT(1) NOT NULL DEFAULT 0,
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
			$this->deleteTableQuery(ACPT_DB::TABLE_DATASET),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM)),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.6';
	}
}




