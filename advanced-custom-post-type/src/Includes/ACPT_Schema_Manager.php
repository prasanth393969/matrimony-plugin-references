<?php

namespace ACPT\Includes;

use ACPT\Utils\PHP\FS;

class ACPT_Schema_Manager
{
    /**
     * Creates the schema
     *
     * @param $newVersion
     * @param null $oldVersion
     *
     * @return bool
     */
    public static function up($newVersion, $oldVersion = null)
    {
	    global $wpdb;
        $migrations = self::getMigrations();

        foreach ($migrations as $migration){
        	if($migration instanceof ACPT_Schema_Migration){
                if(self::isTheMigrationNeeded($migration->version(), $newVersion, $oldVersion)){
	                $up = $migration->up();

	                foreach ($up as $query){
		                $wpdb->query($query);
	                }
                }
	        }
        }

        self::removeLegacyTables();

	    return empty($wpdb->last_error);
    }

	/**
	 * Run a migration only when it is required
	 *
	 * @param $migrationVersion
	 * @param $newVersion
	 * @param null $oldVersion
	 *
	 * @return bool
	 */
    private static function isTheMigrationNeeded($migrationVersion, $newVersion, $oldVersion = null)
    {
    	if(!ACPT_DB::checkIfSchemaExists()){
    		return true;
	    }

    	return (
		    version_compare($migrationVersion, $oldVersion, ">=") == true and
		    version_compare($migrationVersion, $newVersion, "<=") == true
	    );
    }

	/**
	 * Destroy the schema
	 *
	 * @return bool
	 * @throws \Exception
	 */
    public static function down()
    {
	    global $wpdb;

	    $tables = [
		    ACPT_DB::TABLE_API_KEYS,
		    ACPT_DB::TABLE_BELONG,
            ACPT_DB::TABLE_BLOCK,
            ACPT_DB::TABLE_BLOCK_CONTROL,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_IMPORT,
		    ACPT_DB::TABLE_DATASET,
		    ACPT_DB::TABLE_DATASET_ITEM,
		    ACPT_DB::TABLE_FORM,
		    ACPT_DB::TABLE_FORM_SUBMISSION,
		    ACPT_DB::TABLE_FORM_FIELD,
		    ACPT_DB::TABLE_FORM_METADATA,
		    ACPT_DB::TABLE_META_GROUP_BELONG,
		    ACPT_DB::TABLE_META_ADVANCED_OPTION,
		    ACPT_DB::TABLE_META_BLOCK,
		    ACPT_DB::TABLE_META_BOX,
		    ACPT_DB::TABLE_META_BOX_VISIBILITY,
		    ACPT_DB::TABLE_META_FIELD,
		    ACPT_DB::TABLE_META_GROUP,
		    ACPT_DB::TABLE_META_OPTION,
		    ACPT_DB::TABLE_META_RELATION,
		    ACPT_DB::TABLE_META_VISIBILITY,
		    ACPT_DB::TABLE_OPTION_PAGE,
		    ACPT_DB::TABLE_PERMISSION,
		    ACPT_DB::TABLE_SETTINGS,
		    ACPT_DB::TABLE_TAXONOMY,
		    ACPT_DB::TABLE_TAXONOMY_PIVOT,
		    ACPT_DB::TABLE_TEMPLATE,
		    ACPT_DB::TABLE_VALIDATION_RULE,
		    ACPT_DB::TABLE_VALIDATION_RULE_FIELD_PIVOT,
		    ACPT_DB::TABLE_VALIDATION_RULE_FORM_FIELD_PIVOT,
		    ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA,
		    ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD,
		    ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION,
	    ];

	    try {
	    	ACPT_DB::startTransaction();

		    foreach ($tables as $table){
			    ACPT_DB::dropTable($table);
		    }

		    ACPT_DB::commitTransaction();

		    return empty($wpdb->last_error);
	    } catch (\Exception $exception){
            do_action("acpt/error", $exception);
	    	ACPT_DB::rollbackTransaction();

	    	return false;
	    }
    }

	/**
	 * Remove legacy tables
	 */
    private static function removeLegacyTables()
    {
    	global $wpdb;

    	$tables = [
		    ACPT_DB::TABLE_API_KEYS,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_OPTION,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_BLOCK,
		    ACPT_DB::TABLE_CUSTOM_POST_TYPE_IMPORT,
		    ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE,
		    ACPT_DB::TABLE_OPTION_PAGE,
		    ACPT_DB::TABLE_OPTION_PAGE_META_BOX,
		    ACPT_DB::TABLE_TAXONOMY,
		    ACPT_DB::TABLE_TAXONOMY_META_BOX,
		    ACPT_DB::TABLE_TAXONOMY_PIVOT,
		    ACPT_DB::TABLE_SETTINGS,
		    ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA,
		    ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD,
		    ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION,
		    ACPT_DB::TABLE_USER_META_BOX,
		    ACPT_DB::TABLE_USER_META_FIELD,
		    ACPT_DB::TABLE_USER_META_FIELD_OPTION
	    ];

	    foreach ($tables as $table){
		    $wpdb->query("DROP TABLE IF EXISTS `".$table."`;");
	    }
    }

	/**
	 * @return ACPT_Schema_Migration[]|array
	 */
	private static function getMigrations()
    {
	    $migrationsDir = plugin_dir_path(__FILE__) . '/../../migrations';
	    $migrations = [];
	    $classes = FS::getDirClasses($migrationsDir);

	    foreach ($classes as $class){
		    if(is_subclass_of($class, ACPT_Schema_Migration::class)){

			    /** @var ACPT_Schema_Migration $migrationInstance */
			    $migrationInstance = new $class;
			    $migrations[] = $migrationInstance;
		    }
	    }

	    return $migrations;
    }
}