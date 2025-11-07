<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AddSettingsToToMetaBox extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

		if(!ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX), 'settings')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` ADD `settings`TEXT DEFAULT NULL";
		}

		return $queries;
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` DROP COLUMN `settings` ",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.29-beta-1';
	}
}




