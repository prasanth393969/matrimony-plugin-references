<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AddLabelToMetaField extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 */
	public function up(): array
	{
		$queries = [];

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD), 'field_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` ADD `field_label` VARCHAR(255) DEFAULT NULL ";
			}
		} else {
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD, 'field_label')){
				$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` ADD `field_label` VARCHAR(255) DEFAULT NULL ";
			}
		}

		return $queries;
	}


	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` DROP COLUMN `field_label` ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."` DROP COLUMN `field_label` ",
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




