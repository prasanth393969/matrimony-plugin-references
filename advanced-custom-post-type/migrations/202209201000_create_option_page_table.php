<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateOptionPageTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_OPTION_PAGE."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            page_title VARCHAR(64) NOT NULL,
	            menu_title VARCHAR(64) NOT NULL,
	            capability VARCHAR(64) NOT NULL,
	            menu_slug VARCHAR(64) UNIQUE NOT NULL,
	            icon VARCHAR(50) DEFAULT NULL,
	            description TEXT,
	            parent_id VARCHAR(36) DEFAULT NULL,
	            sort INT(11),
	            page_position INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_OPTION_PAGE),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_OPTION_PAGE),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE)),
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