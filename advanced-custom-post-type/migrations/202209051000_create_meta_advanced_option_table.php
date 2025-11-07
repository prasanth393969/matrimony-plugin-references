<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateMetaAdvancedOptionTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            meta_box_id VARCHAR(36) NOT NULL,
	            meta_field_id VARCHAR(36) NOT NULL,
	            option_key VARCHAR(50) NOT NULL,
	            option_value VARCHAR(50) NOT NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION)),
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