<?php

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class MigrateRelationshipTable extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		ACPT_DB::startTransaction();

		try {
			$this->migrateRelations();
		} catch (\Exception $exception){
			ACPT_DB::rollbackTransaction();

			return [];
		}

		ACPT_DB::commitTransaction();

		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [];
	}

	/**
	 * Migrate the relations
	 * @throws Exception
	 */
	private function migrateRelations()
	{
		$table = ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION);
		$sql = "
			SELECT 
				r.id, 
				f.field_name,
                b.meta_box_name,
				r.relation_from, 
				r.relation_to, 
				r.relationship, 
				r.inversed_meta_box_name, 
				r.inversed_meta_field_name, 
				g.group_name
			FROM `".$table."` r
				join `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` b ON r.meta_box_id = b.id 
				join `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` f ON r.meta_field_id = f.id 
				join`".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP)."` g ON g.id = b.group_id
		";
		$results = ACPT_DB::getResults($sql);

		foreach ($results as $result){

			// don't touch data if is in V2 format
			if(!$this->isV2Format($result)){
				$sql = "UPDATE `".$table."`
				SET 
					relation_from = %s,
					relation_to = %s
				WHERE id = %s;
			";

				ACPT_DB::executeQueryOrThrowException($sql, [
					$this->convertLegacyFormat(str_replace("-meta-group","", $result->group_name)),
					$this->convertLegacyFormat($result->relation_from),
					$result->id,
				]);

				// postmeta
				global $wpdb;

				$sql = "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s";
				$results = ACPT_DB::getResults($sql, [$result->meta_box_name."_".$result->field_name]);
				$this->migrateMetadata($results);

				if($result->inversed_meta_box_name and $results->inversed_meta_field_name){
					$sql = "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = %s";
					$results = ACPT_DB::getResults($sql, [$result->inversed_meta_box_name."_".$result->inversed_meta_field_name]);
					$this->migrateMetadata($results);
				}
			}
		}
	}

	/**
	 * @param $results
	 *
	 * @throws Exception
	 */
	private function migrateMetadata($results)
	{
		global $wpdb;

		foreach ($results as $result){
			$isSerialized = @unserialize($result->meta_value);
			if ($isSerialized !== false) {
				$data = unserialize($result->meta_value);
				$data = implode(",", $data);
				$sql = "UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_id = %d";
				ACPT_DB::executeQueryOrThrowException($sql, [
					$data,
					$result->meta_id,
				]);
			}
		}
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	private function convertLegacyFormat($value): string
	{
		if(!Strings::isJson($value)){
			return '{"type":"'.MetaTypes::CUSTOM_POST_TYPE.'","value":"'.$value.'"}';
		}

		return $value;
	}

	/**
	 * @param $result
	 *
	 * @return bool
	 */
	private function isV2Format($result): bool
	{
		if(!isset($result->relation_from)){
			return false;
		}

		if(!isset($result->relation_to)){
			return false;
		}

		if(empty($result->relation_from)){
			return false;
		}

		if(empty($result->relation_to)){
			return false;
		}

		return (
			Strings::isJson($result->relation_from) and
			Strings::isJson($result->relation_to)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.0-beta-rc1';
	}
}




