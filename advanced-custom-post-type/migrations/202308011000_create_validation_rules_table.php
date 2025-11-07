<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateValidationRulesTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_VALIDATION_RULE)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            rule_condition VARCHAR(50) NOT NULL,
	            rule_value VARCHAR(255) NULL,
	            message TEXT DEFAULT NULL,
	            sort INT(11),
                PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_VALIDATION_RULE_FIELD_PIVOT)."` (
	            field_id VARCHAR(36) NOT NULL,
	            rule_id VARCHAR(36) NOT NULL,
	            PRIMARY KEY( `field_id`, `rule_id`)
	        ) ".ACPT_DB::getCharsetCollation().";",
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_VALIDATION_RULE)),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_VALIDATION_RULE_FIELD_PIVOT)),
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