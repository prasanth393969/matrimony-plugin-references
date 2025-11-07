<?php

use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class AlterVarcharTo255 extends ACPT_Schema_Migration
{
	/**
	 * @return array
	 */
	public function up(): array
	{
		$queries = [];

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM), 'item_label')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM)."` CHANGE COLUMN `item_label` `item_label` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM), 'item_value')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM)."` CHANGE COLUMN `item_value` `item_value` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION), 'option_key')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION)."` CHANGE COLUMN `option_key` `option_key` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION), 'option_value')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BLOCK), 'block_name')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BLOCK)."` CHANGE COLUMN `block_name` `block_name` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX), 'meta_box_name')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` CHANGE COLUMN `meta_box_name` `meta_box_name` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD), 'field_name')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` CHANGE COLUMN `field_name` `field_name` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD), 'field_default_value')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` CHANGE COLUMN `field_default_value` `field_default_value` VARCHAR(255) DEFAULT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION), 'option_label')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."` CHANGE COLUMN `option_label` `option_label` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION), 'option_value')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD), 'field_name')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD)."` CHANGE COLUMN `field_name` `field_name` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD), 'field_default_value')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD)."` CHANGE COLUMN `field_default_value` `field_default_value` VARCHAR(255) DEFAULT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION), 'option_label')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION)."` CHANGE COLUMN `option_label` `option_label` VARCHAR(255) NOT NULL ";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION), 'option_value')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(255) NOT NULL ";
		}

		return $queries;
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM)."` CHANGE COLUMN `item_label` `item_label` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_DATASET_ITEM)."` CHANGE COLUMN `item_value` `item_value` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION)."` CHANGE COLUMN `option_key` `option_key` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BLOCK)."` CHANGE COLUMN `block_name` `block_name` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` CHANGE COLUMN `meta_box_name` `meta_box_name` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` CHANGE COLUMN `field_name` `field_name` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` CHANGE COLUMN `field_default_value` `field_default_value` VARCHAR(50) DEFAULT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."` CHANGE COLUMN `option_label` `option_label` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD)."` CHANGE COLUMN `field_name` `field_name` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD)."` CHANGE COLUMN `field_default_value` `field_default_value` VARCHAR(50) DEFAULT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION)."` CHANGE COLUMN `option_label` `option_label` VARCHAR(50) NOT NULL ",
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION)."` CHANGE COLUMN `option_value` `option_value` VARCHAR(50) NOT NULL ",
		];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.15-beta-2';
	}
}




