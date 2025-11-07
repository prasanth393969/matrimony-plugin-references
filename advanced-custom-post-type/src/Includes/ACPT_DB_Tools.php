<?php

namespace ACPT\Includes;

use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Integrations\WooCommerce\ACPT_WooCommerce;
use ACPT\Utils\Wordpress\Transient;

abstract class ACPT_DB_Tools
{
    const HEALTH_CHECK_TRANSIENT_KEY = 'acpt_health_check';
    const HEALTH_CHECK_TRANSIENT_TTL = 86400; // 1 day

    /**
     * Run health check and repair DB
     * (once a day)
     *
     * @return bool
     * @throws \Exception
     */
    public static function runHealthCheck()
    {
        if(!Transient::has(self::HEALTH_CHECK_TRANSIENT_KEY)){
            $healthCheckIssues = ACPT_DB_Tools::healthCheck();

            if(!empty($healthCheckIssues)){
                $repair = ACPT_DB_Tools::repair($healthCheckIssues);
                Transient::set(self::HEALTH_CHECK_TRANSIENT_KEY, $repair, self::HEALTH_CHECK_TRANSIENT_TTL );

                return $repair;
            }

            Transient::set(self::HEALTH_CHECK_TRANSIENT_KEY, true, self::HEALTH_CHECK_TRANSIENT_TTL );

            return true;
        }

        return true;
    }

    /**
     * @return mixed
     */
    private static function deleteTransient()
    {
        return delete_transient( self::HEALTH_CHECK_TRANSIENT_KEY);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function healthCheck()
    {
        $issues = [];

        // check schema
        foreach (ACPT_Schema::get() as $tableName => $specs){
            if(self::tableShouldBeIncludedInHealthCheck($tableName)){
                $create = $specs['create'];
                $columns = $specs['columns'];

                if(false === ACPT_DB::tableExists($tableName)){
                    $issues[$tableName] = [
                            'create' => $create,
                    ];
                } else {
                    foreach ($columns as $column => $desc){
                        if(false === ACPT_DB::checkIfColumnExistsInTable($tableName, $column)){
                            $desc['reason'] = "add_column";
                            $issues[$tableName]['columns'][$column] = $desc;
                        }

                        if(false === ACPT_DB::checkColumnType($tableName, $column, $desc['type'])){
                            $desc['reason'] = "change_column_type";
                            $issues[$tableName]['columns'][$column] = $desc;
                        }
                    }
                }
            }
        }

        // check native post types
        $postTypes = [
            'page',
            'post',
            'attachment'
        ];

        foreach ($postTypes as $postType){
            $postTypeModel = CustomPostTypeRepository::get(['postType' => $postType]);

            if(!isset($postTypeModel[0])){
                $issues['sync'] = true;
            }
        }

        // check native taxonomies
        $taxonomies = [
            'category',
            'post_tag',
        ];

        foreach ($taxonomies as $taxonomy){
            $taxonomyModel = TaxonomyRepository::get(['taxonomy' => $taxonomy]);

            if(!isset($taxonomyModel[0])){
                $issues['sync'] = true;
            }
        }

        return $issues;
    }

    /**
     * @param $tableName
     *
     * @return bool
     */
    private static function tableShouldBeIncludedInHealthCheck( $tableName)
    {
        if(!ACPT_DELETE_UNUSED_TABLES){
            return true;
        }

        switch ($tableName){
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP_BELONG):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BLOCK):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY):
                return ACPT_ENABLE_META;

            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_SUBMISSION):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_FIELD):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_FORM_METADATA):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_VALIDATION_RULE_FORM_FIELD_PIVOT):
                return ACPT_ENABLE_FORMS;

            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE):
                return ACPT_ENABLE_PAGES;

            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE):
                return ACPT_ENABLE_CPT;

            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_PIVOT):
                return ACPT_ENABLE_TAX;

            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL):
                return ACPT_ENABLE_BLOCKS;

            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_FIELD):
            case ACPT_DB::prefixedTableName(ACPT_DB::TABLE_WOOCOMMERCE_PRODUCT_DATA_OPTION):
                return ACPT_WooCommerce::active();
        }

        return true;
    }

    /**
     * @param array $issues
     *
     * @return bool
     */
    public static function repair($issues)
    {
        if(empty($issues)){
            return true;
        }

        try {
            foreach ($issues as $table => $issue){
                if(isset($issue['create'])){
                    self::repairTable($table, $issue['create']);
                } elseif(isset($issue['columns'])){
                    self::repairColumns($table, $issue['columns']);
                }
            }

            if(isset($issues['sync']) and $issues['sync'] === true){
                ACPT_DB::sync();
                unset($issues['sync']);
            }

            return true;
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);

            return false;
        }
    }

    /**
     * @param $table
     * @param $create
     *
     * @throws \Exception
     */
    private static function repairTable($table, $create)
    {
        global $wpdb;

        if(!$wpdb->query($create)){
            throw new \Exception("Repairing table ".$table." failed");
        }
    }

    /**
     * @param $table
     * @param $columns
     *
     * @throws \Exception
     */
    private static function repairColumns($table, $columns)
    {
        if(empty($columns)){
            return;
        }

        global $wpdb;

        foreach ($columns as $column => $specs){
            $query = self::alterTableQuery($table, $column, $specs);

            if(!$wpdb->query($query)){
                throw new \Exception("Repairing column ".$column." in table ".$table." failed");
            }
        }
    }

    /**
     * @param $table
     * @param $column
     * @param $specs
     *
     * @return string
     */
    private static function alterTableQuery($table, $column, $specs)
    {
        $type = $specs['type'];
        $unique = $specs['unique'];
        $length = $specs['length'];
        $nullable = $specs['nullable'];
        $default = $specs['default'];
        $reason = $specs['reason'];

        switch ($reason){
            default:
            case "add_column":
                $query = "ALTER TABLE `".$table."` ADD COLUMN `".$column."` " . $type;

                if($length){
                    $query .= "(".$length.") ";
                }

                break;

            case "change_column_type":
                $query = "ALTER TABLE `".$table."` CHANGE COLUMN `".$column."` `".$column."` " . $type;
                break;
        }

        if($unique){
            $query .= "UNIQUE ";
        }

        if($nullable === false){
            $query .= " NOT NULL";
        }

        if(!empty($default)){
            $query .= " DEFAULT " . $default;
        }

        return $query;
    }
}