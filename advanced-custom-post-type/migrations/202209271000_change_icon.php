<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class ChangeIcon extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE))){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE)."` CHANGE COLUMN `icon` `icon` VARCHAR(255) NOT NULL ";
		} else {
			$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE."` CHANGE COLUMN `icon` `icon` VARCHAR(255) NOT NULL ";
		}

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE))){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE)."` CHANGE COLUMN `icon` `icon` VARCHAR(255) NOT NULL ";
		} else {
			$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_OPTION_PAGE."` CHANGE COLUMN `icon` `icon` VARCHAR(255) NOT NULL ";
		}

		return $queries;
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE)."` CHANGE COLUMN `icon` `icon` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE."` CHANGE COLUMN `icon` `icon` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE)."` CHANGE COLUMN `icon` `icon` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::TABLE_OPTION_PAGE."` CHANGE COLUMN `icon` `icon` VARCHAR(50) NOT NULL ",
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