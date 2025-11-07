<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateWooCommerceProductDataTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            product_data_name VARCHAR(32) NOT NULL,
	            icon VARCHAR(255) NOT NULL,
	            visibility TEXT NOT NULL,
	            show_in_ui TINYINT(1) NOT NULL,
	            content TEXT DEFAULT NULL,
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA)),
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