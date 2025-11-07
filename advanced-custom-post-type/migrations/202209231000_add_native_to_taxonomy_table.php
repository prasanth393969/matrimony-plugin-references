<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AddNativeToTaxonomyTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY))){
			if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY), 'native') ) {
				return [
					"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY)."` ADD `native` TINYINT(1) NULL DEFAULT NULL ",
				];
			}
		}

		if(false === ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::TABLE_TAXONOMY, 'native') ) {
			return [
				"ALTER TABLE `".ACPT_DB::TABLE_TAXONOMY."` ADD `native` TINYINT(1) NULL DEFAULT NULL ",
			];
		}

		return [];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY)."` DROP COLUMN `native` ",
			"ALTER TABLE `".ACPT_DB::TABLE_TAXONOMY."` DROP COLUMN `native` ",
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