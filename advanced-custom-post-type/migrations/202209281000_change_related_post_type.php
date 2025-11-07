<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class ChangeRelatedPostType extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

		if($this->existsTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION))){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION)."` CHANGE COLUMN `related_post_type` `related_post_type` TEXT NOT NULL ";
		} else {
			$queries[] = "ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION."` CHANGE COLUMN `related_post_type` `related_post_type` TEXT NOT NULL ";
		}

		return $queries;
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION)."` CHANGE COLUMN `related_post_type` `related_post_type` TEXT NOT NULL ",
			"ALTER TABLE `".ACPT_DB::TABLE_CUSTOM_POST_TYPE."` CHANGE COLUMN `icon` `icon` VARCHAR(20) NOT NULL ",
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