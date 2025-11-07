<?php

use ACPT\Constants\MetaTypes;
use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AddMetaFieldIdToTemplateTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE), 'meta_field_id')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."` ADD `meta_field_id` VARCHAR(36) DEFAULT NULL ";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE), 'belongs_to')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."` ADD `belongs_to` VARCHAR(36) DEFAULT '" . MetaTypes::CUSTOM_POST_TYPE. "'";
			}

			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."` CHANGE `post_type` `find` VARCHAR(20) ";
		} else {
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE, 'meta_field_id')){
				$query[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE."` ADD `meta_field_id` VARCHAR(36) DEFAULT '" . MetaTypes::CUSTOM_POST_TYPE. "'";
			}

			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE, 'belongs_to')){
				$query[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE."` ADD `belongs_to` VARCHAR(36) DEFAULT NULL ";
			}

			$query[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE."` CHANGE `post_type` `find` VARCHAR(20)  ";
		}

		return $queries;
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."` DROP COLUMN `meta_field_id` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE."` DROP COLUMN `meta_field_id` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."` DROP COLUMN `belongs_to` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE."` DROP COLUMN `belongs_to` ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."` CHANGE `find` `post_type` VARCHAR(20) ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE."` CHANGE `find` `post_type` VARCHAR(20)  ",
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