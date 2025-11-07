<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateWooCommerceProductDataOptionTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            product_data_id VARCHAR(36) NOT NULL,
	            product_data_field_id VARCHAR(36) NOT NULL,
	            option_label VARCHAR(50) NOT NULL,
	            option_value VARCHAR(50) NOT NULL,
	            sort INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION)),
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