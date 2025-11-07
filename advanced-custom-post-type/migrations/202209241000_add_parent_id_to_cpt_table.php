<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AddParentIdToCustomPostTypeTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD), 'parent_id')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` ADD `parent_id` VARCHAR(36) DEFAULT NULL ";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD), 'block_id')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` ADD `block_id` VARCHAR(36) DEFAULT NULL ";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD), 'quick_edit')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` ADD `quick_edit` TINYINT(1) DEFAULT NULL ";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD), 'filter_in_admin')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` ADD `filter_in_admin` TINYINT(1) DEFAULT NULL ";
			}
		} else {
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD, 'parent_id')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` ADD `parent_id` VARCHAR(36) DEFAULT NULL ";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD, 'block_id')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` ADD `block_id` VARCHAR(36) DEFAULT NULL ";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD, 'quick_edit')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` ADD `quick_edit` TINYINT(1) DEFAULT NULL ";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD, 'filter_in_admin')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` ADD `filter_in_admin` TINYINT(1) DEFAULT NULL ";
			}
		}

		return $queries;
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` DROP COLUMN `parent_id` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` DROP COLUMN `parent_id` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` DROP COLUMN `block_id` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` DROP COLUMN `block_id` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` DROP COLUMN `quick_edit` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` DROP COLUMN `quick_edit` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` DROP COLUMN `filter_in_admin` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` DROP COLUMN `filter_in_admin` ",
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