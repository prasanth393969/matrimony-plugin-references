<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterRelationshipTable extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		$queries = [];

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION), 'related_post_type')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION)."` CHANGE `related_post_type` `relation_from` VARCHAR(255) NOT NULL ";
		}

		if(!ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION), 'relation_to')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION)."` ADD `relation_to` VARCHAR(255) NOT NULL ";
		}

		return $queries;
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION)."` CHANGE `relation_from` `related_post_type` VARCHAR(20) NOT NULL",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION)."` DROP COLUMN `relation_to` ",
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




