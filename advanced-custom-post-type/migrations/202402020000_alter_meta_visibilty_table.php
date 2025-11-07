<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterVisibility extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		$queries = [];

		if(!ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY), 'back_end')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY)."` ADD COLUMN `back_end` TINYINT(1) NOT NULL DEFAULT 1";
		}

		if(!ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY), 'front_end')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY)."` ADD COLUMN `front_end` TINYINT(1) NOT NULL DEFAULT 1";
		}

		return $queries;
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY)."` DROP COLUMN `back_end`",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY)."` DROP COLUMN `front_end`",
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




