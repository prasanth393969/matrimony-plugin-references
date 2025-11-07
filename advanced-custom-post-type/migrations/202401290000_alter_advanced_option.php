<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterAdvancedOption extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		if(ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION))){
			return [
				"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(255) NOT NULL ",
			];
		}

		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(50) NOT NULL ",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.0-beta-rc2';
	}
}




