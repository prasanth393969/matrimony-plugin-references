<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateFormTables extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            form_name VARCHAR(".ACPT_DB::keyLength().") UNIQUE NOT NULL,
	            label VARCHAR(255) NULL,
	            form_action VARCHAR(12) NOT NULL,
	            form_key VARCHAR(12) UNIQUE NOT NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_METADATA)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            form_id VARCHAR(36) NOT NULL,
	            meta_key VARCHAR(255) NULL,
	            meta_value TEXT NOT NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_FIELD)."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            form_id VARCHAR(36) NOT NULL,
	            meta_field_id VARCHAR(36) NULL,
	            field_group VARCHAR(36) NOT NULL,
	            field_type VARCHAR(36) NOT NULL,
	            field_key VARCHAR(12) UNIQUE NOT NULL,
	            field_name VARCHAR(255) NOT NULL,
	            field_label VARCHAR(255) NULL,
	            description TEXT NULL,
	            extra TEXT NULL,
	            settings TEXT NULL,
	            required TINYINT(1) NOT NULL,
	            sort INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_VALIDATION_RULE_FORM_FIELD_PIVOT)."` (
	            field_id VARCHAR(36) NOT NULL,
	            rule_id VARCHAR(36) NOT NULL,
	            PRIMARY KEY( `field_id`, `rule_id`)
	        ) ".ACPT_DB::getCharsetCollation().";",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM)),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_FIELD)),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_METADATA)),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_VALIDATION_RULE_FORM_FIELD_PIVOT)),
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




