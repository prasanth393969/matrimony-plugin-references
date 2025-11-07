<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterMetaFieldDefaultValue extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

        if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD), 'field_default_value')){
            $queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` CHANGE COLUMN `field_default_value` `field_default_value` TEXT DEFAULT NULL ";
        }

		return $queries;
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
            "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` CHANGE COLUMN `field_default_value` `field_default_value` VARCHAR(255) DEFAULT NULL ",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.40';
	}
}




