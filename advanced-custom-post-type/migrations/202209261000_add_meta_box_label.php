<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AddMetaBoxLabel extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX), 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
			}
		} else {
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX, 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
			}
		}

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_META_BOX))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_META_BOX), 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_META_BOX)."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
			}
		} else {
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_TAXONOMY_META_BOX, 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_TAXONOMY_META_BOX."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
			}
		}

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX), 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX)."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
			}
		} else {
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_USER_META_BOX, 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_USER_META_BOX."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
			}
		}

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX), 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX)."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
			}
		} else {
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_OPTION_PAGE_META_BOX, 'meta_box_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_OPTION_PAGE_META_BOX."` ADD `meta_box_label` VARCHAR(255) DEFAULT NULL ";
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
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."` DROP COLUMN `meta_box_label` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX."` DROP COLUMN `meta_box_label` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_META_BOX)."` DROP COLUMN `meta_box_label` ",
			"ALTER TABLE `".ACPT_DB::TABLE_TAXONOMY_META_BOX."` DROP COLUMN `meta_box_label` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX)."` DROP COLUMN `meta_box_label` ",
			"ALTER TABLE `".ACPT_DB::TABLE_USER_META_BOX."` DROP COLUMN `meta_box_label` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX)."` DROP COLUMN `meta_box_label` ",
			"ALTER TABLE `".ACPT_DB::TABLE_OPTION_PAGE_META_BOX."` DROP COLUMN `meta_box_label` ",
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