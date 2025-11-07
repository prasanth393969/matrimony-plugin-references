<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterMetaGroup extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		if(!ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP), 'display')){
			return [
				"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP)."` ADD COLUMN `display` VARCHAR(55) DEFAULT NULL",
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
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP)."` DROP COLUMN `display`",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.1';
	}
}




