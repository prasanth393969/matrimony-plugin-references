<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterTemplatesTable extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		$queries = [];

		if(!ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE), 'template_name')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."` ADD `template_name` VARCHAR(50) NOT NULL";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE), 'find')){
			$queries[]  = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."` DROP COLUMN `find`";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE), 'belongs_to')){
			$queries[]  = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."` DROP COLUMN `belongs_to`";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE), 'meta_field_id')){
			$queries[]  = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."` DROP COLUMN `meta_field_id`";
		}

		$queries[] = "CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE_BELONG)."` (
	            template_id VARCHAR(36) NOT NULL,
	            belong_id VARCHAR(36) NOT NULL,
	             PRIMARY KEY( `template_id`, `belong_id`)
	        ) ".ACPT_DB::getCharsetCollation().";";

		return $queries;
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."` DROP COLUMN `template_name` ",
			"ALTER TABLE `".ACPT_DB::TABLE_TEMPLATE."` ADD `find` VARCHAR(20)",
			"ALTER TABLE `".ACPT_DB::TABLE_TEMPLATE."` ADD `belongs_to` VARCHAR(36) DEFAULT NULL",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."` ADD `meta_field_id` VARCHAR(36) DEFAULT NULL",
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE_BELONG)),
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




