<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateMetaVisibilityTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            meta_box_id VARCHAR(36) NOT NULL,
	            meta_field_id VARCHAR(36) NOT NULL,
	            visibility_type TEXT NOT NULL,
	            operator VARCHAR(20) NOT NULL,
	            visibility_value VARCHAR(255) NOT NULL,
	            logic VARCHAR(3) DEFAULT NULL,
	            sort INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY)),
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