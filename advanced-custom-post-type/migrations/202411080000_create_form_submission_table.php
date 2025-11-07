<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateFormSubmissionTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_SUBMISSION)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            form_id VARCHAR(36) NOT NULL,
	            form_action VARCHAR(50) NOT NULL,
	            callback VARCHAR(255) NOT NULL,
	            ip VARCHAR(50) NOT NULL,
	            browser VARCHAR(255) NOT NULL,
	            form_data TEXT DEFAULT NULL,
	            errors TEXT DEFAULT NULL,
	            uid INT(11) DEFAULT NULL,
	            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_SUBMISSION)),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.18';
	}
}




