<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterMetaOption extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		if(!ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION), 'is_default')){
			return [
				"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0",
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
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."` DROP COLUMN `is_default`",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.0-beta-rc3';
	}
}




