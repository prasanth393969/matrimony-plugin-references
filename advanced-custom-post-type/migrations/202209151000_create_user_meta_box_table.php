<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateUserMetaBoxTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		// user meta field (FROM v.1.0.140 THIS TABLE IS NO LONGER USED)
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_USER_META_BOX."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            meta_box_name VARCHAR(50) NOT NULL,
	            sort INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_USER_META_BOX),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_USER_META_BOX),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX)),
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