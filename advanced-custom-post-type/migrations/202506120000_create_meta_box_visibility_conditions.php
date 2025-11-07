<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateMetaBoxVisibilityConditions extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
            "CREATE TABLE IF NOT EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX_VISIBILITY)."` (
                `id` VARCHAR(36) NOT NULL,
                `meta_box_id` VARCHAR(36)  NOT NULL,
                `visibility_type` TEXT NOT NULL,
                `operator` VARCHAR(20) NOT NULL,
                `visibility_value` VARCHAR(255) NOT NULL,
                `logic` VARCHAR(3) DEFAULT NULL,
                `sort` INT DEFAULT NULL,
                `back_end` TINYINT(1) NOT NULL DEFAULT '1',
                `front_end` TINYINT(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`)
            ) ".ACPT_DB::getCharsetCollation().";",
        ];
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
                $this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX_VISIBILITY)),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.29-beta-2';
	}
}




