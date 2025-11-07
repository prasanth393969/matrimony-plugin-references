<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class CreateWooCommerceProductDataFieldTable extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		return [
			"CREATE TABLE IF NOT EXISTS `".ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD."` (
	            id VARCHAR(36) UNIQUE NOT NULL,
	            product_data_id VARCHAR(36) NOT NULL,
	            field_name VARCHAR(50) NOT NULL,
	            field_type VARCHAR(50) NOT NULL,
	            field_default_value VARCHAR(50) DEFAULT NULL,
	            field_description TEXT DEFAULT NULL,
	            required TINYINT(1) NOT NULL,
	            sort INT(11),
	            PRIMARY KEY(id)
	        ) ".ACPT_DB::getCharsetCollation().";",
			$this->renameTableQuery(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD),
		];
	}

	/**
	 * @return array
	 */
	public function down(): array
	{
		return [
			$this->deleteTableQuery(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD),
			$this->deleteTableQuery(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD)),
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