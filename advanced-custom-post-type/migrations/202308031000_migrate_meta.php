<?php

use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class MigrateMeta extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		ACPT_DB::startTransaction();

		try {
			$this->migrateMetaBoxes();
		} catch (\Exception $exception){
			ACPT_DB::rollbackTransaction();

			return [];
		}

		ACPT_DB::commitTransaction();

		$queries = [];

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."`;";
		}

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."`;";
		}

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION)."`;";
		}

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_OPTION)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."`;";
		}

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY)."`;";
		}

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION)."`;";
		}

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BLOCK))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_BLOCK)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BLOCK)."`;";
		}

		if(!ACPT_DB::tableExists(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE))){
			$queries[] = "RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TEMPLATE)."`;";
		}

		if(ACPT_DB::checkIfColumnExistsInTable(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX), 'post_type')){
			$queries[] = "ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` DROP COLUMN `post_type` ";
		}

		$queries = array_merge($queries, [
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_META_BOX)."`",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX)."`",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX)."`",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_OPTION)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_BLOCK)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_FIELD)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_FIELD_OPTION)."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE)."`;",

			// legacy tables
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_TAXONOMY_META_BOX."`",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_OPTION_PAGE_META_BOX."`",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_USER_META_BOX."`",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_OPTION."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TYPE_BLOCK."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_USER_META_FIELD."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_USER_META_FIELD_OPTION."`;",
			"DROP TABLE IF EXISTS `".ACPT_DB::TABLE_CUSTOM_POST_TEMPLATE."`;",
		]);

		return $queries;
	}

	/**
	 * @throws Exception
	 */
	private function migrateMetaBoxes()
	{
		ACPT_DB::flushCache();

		// CPTs
		$sql = "SELECT post_name FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE)."`";
		foreach (ACPT_DB::getResults($sql) as $result){
			$this->copyCustomPostTypeBoxes($result->post_name);
		}

		// Taxonomies
		$sql = "SELECT slug FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY)."`";
		foreach (ACPT_DB::getResults($sql) as $taxonomy){
			$this->copyTaxonomyBoxes($taxonomy->slug);
		}

		// OP
		$sql = "SELECT menu_slug FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE)."`";
		foreach (ACPT_DB::getResults($sql) as $optionPage){
			$this->copyOptionPageBoxes($optionPage->menu_slug);
		}

		// User
		$this->copyUserBoxes();

		// copy field label
		$this->copyMetaFieldLabel();

		// migrate op meta
		$this->migrateOptionPageFields();
	}

	/**
	 * @param string $postName
	 *
	 * @throws Exception
	 */
	private function copyCustomPostTypeBoxes(string $postName)
	{
		if(!ACPT_DB::tableIsEmpty(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX))){
			$group = MetaGroupModel::hydrateFromArray([
				'name' => $postName.'-meta-group',
				'label' => $postName.' meta group',
			]);

			$belong = BelongModel::hydrateFromArray([
				'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
				'operator' => Operator::EQUALS,
				'find' => $postName,
				'logic' => null,
				'sort' => 1
			]);

			$group->addBelong($belong);
			$this->saveMetaGroup($group);

			$sql = "UPDATE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."` SET group_id = %s WHERE post_type = %s;";
			ACPT_DB::executeQueryOrThrowException($sql, [
				$group->getId(),
				$postName
			]);
		}
	}

	/**
	 * @param string $taxonomySlug
	 *
	 * @throws Exception
	 */
	private function copyTaxonomyBoxes(string $taxonomySlug)
	{
		if(!ACPT_DB::tableIsEmpty(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_META_BOX))){
			$group = MetaGroupModel::hydrateFromArray([
				'name' => $taxonomySlug.'-meta-group',
				'label' => $taxonomySlug.' meta group',
			]);

			$belong = BelongModel::hydrateFromArray([
				'belongsTo' => MetaTypes::TAXONOMY,
				'operator' => Operator::EQUALS,
				'find' => $taxonomySlug,
				'logic' => null,
				'sort' => 1
			]);

			$group->addBelong($belong);
			$this->saveMetaGroup($group);

			$sql = "SELECT * FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_TAXONOMY_META_BOX)."` WHERE taxonomy = %s";

			$taxonomyBoxes = ACPT_DB::getResults($sql, [
				$taxonomySlug
			]);

			foreach ($taxonomyBoxes as $taxonomyBox){

				$sql = "
                INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."`
                (
                    `id`,
                    `group_id`,
                    `post_type`,
                    `meta_box_name`,
                    `meta_box_label`,
                    `sort`
                ) VALUES (
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %d
                ) ON DUPLICATE KEY UPDATE
                    `group_id` = %s,
                    `post_type` = %s,
                    `meta_box_name` = %s,
                    `meta_box_label` = %s,
                    `sort` = %d
            ;";

				ACPT_DB::executeQueryOrThrowException($sql, [
					$taxonomyBox->id,
					$group->getId(),
					'tax',
					$taxonomyBox->meta_box_name,
					$taxonomyBox->meta_box_label,
					$taxonomyBox->sort,
					$group->getId(),
					'tax',
					$taxonomyBox->meta_box_name,
					$taxonomyBox->meta_box_label,
					$taxonomyBox->sort
				]);
			}
		}
	}

	/**
	 * @param string $menuSlug
	 *
	 * @throws Exception
	 */
	private function copyOptionPageBoxes(string $menuSlug)
	{
		if(!ACPT_DB::tableIsEmpty(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX))){
			$group = MetaGroupModel::hydrateFromArray([
				'name' => $menuSlug.'-meta-group',
				'label' => $menuSlug.' meta group',
			]);

			$belong = BelongModel::hydrateFromArray([
				'belongsTo' => MetaTypes::OPTION_PAGE,
				'operator' => Operator::EQUALS,
				'find' => $menuSlug,
				'logic' => null,
				'sort' => 1
			]);

			$group->addBelong($belong);
			$this->saveMetaGroup($group);

			$sql = "SELECT * FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX)."` WHERE page = %s";

			$opBoxes = ACPT_DB::getResults($sql, [
				$menuSlug
			]);

			foreach ($opBoxes as $opBox){

				$sql = "
                INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."`
                (
                    `id`,
                    `group_id`,
                    `post_type`,
                    `meta_box_name`,
                    `meta_box_label`,
                    `sort`
                ) VALUES (
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %d
                ) ON DUPLICATE KEY UPDATE
                    `group_id` = %s,
                    `post_type` = %s,
                    `meta_box_name` = %s,
                    `meta_box_label` = %s,
                    `sort` = %d
            ;";

				ACPT_DB::executeQueryOrThrowException($sql, [
					$opBox->id,
					$group->getId(),
					'page',
					$opBox->meta_box_name,
					$opBox->meta_box_label,
					$opBox->sort,
					$group->getId(),
					'page',
					$opBox->meta_box_name,
					$opBox->meta_box_label,
					$opBox->sort
				]);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	private function copyUserBoxes()
	{
		if(!ACPT_DB::tableIsEmpty(ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX))){
			$group = MetaGroupModel::hydrateFromArray([
				'name' => 'user-meta-group',
				'label' => 'User meta group',
			]);

			$belong = BelongModel::hydrateFromArray([
				'belongsTo' => MetaTypes::USER,
				'sort' => 1
			]);

			$group->addBelong($belong);
			$this->saveMetaGroup($group);

			$sql = "SELECT * FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_USER_META_BOX)."`";

			$userBoxes = ACPT_DB::getResults($sql, []);

			foreach ($userBoxes as $userBox){

				$sql = "
                INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."`
                (
                    `id`,
                    `group_id`,
                    `post_type`,
                    `meta_box_name`,
                    `meta_box_label`,
                    `sort`
                ) VALUES (
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    %d
                ) ON DUPLICATE KEY UPDATE
                    `group_id` = %s,
                    `post_type` = %s,
                    `meta_box_name` = %s,
                    `meta_box_label` = %s,
                    `sort` = %d
            ;";

				ACPT_DB::executeQueryOrThrowException($sql, [
					$userBox->id,
					$group->getId(),
					'user',
					$userBox->meta_box_name,
					$userBox->meta_box_label,
					$userBox->sort,
					$group->getId(),
					'user',
					$userBox->meta_box_name,
					$userBox->meta_box_label,
					$userBox->sort
				]);
			}
		}
	}

	/**
	 * @param MetaGroupModel $group
	 *
	 * @throws Exception
	 */
	private function saveMetaGroup(MetaGroupModel $group)
	{
		$sql = "
            INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP)."` 
            (
				`id`,
				`group_name`,
				`label`
            ) VALUES (
                %s,
                %s,
                %s
            ) ON DUPLICATE KEY UPDATE 
                `group_name` = %s,
                `label` = %s
        ;";

		ACPT_DB::executeQueryOrThrowException($sql, [
			$group->getId(),
			$group->getName(),
			$group->getLabel(),
			$group->getName(),
			$group->getLabel(),
		]);

		foreach($group->getBelongs() as $belong){

			// check if group exists
			$sql = "SELECT * FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP)."` WHERE id = %s";
			$check = ACPT_DB::getResults($sql, [$group->getId()]);

			if(count($check) == 1){
				$sql = "
	                INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BELONG)."`
	                (
	                    `id`,
	                    `belongs`,
	                    `operator`,
	                    `find`,
	                    `sort`
	                ) VALUES (
	                    %s,
	                    %s,
	                    %s,
	                    %s,
	                    %d
	                ) ON DUPLICATE KEY UPDATE
	                    `belongs` = %s,
	                    `operator` = %s,
	                    `find` = %s,
	                    `sort` = %d
	                ;";

				ACPT_DB::executeQueryOrThrowException($sql, [
					$belong->getId(),
					$belong->getBelongsTo(),
					$belong->getOperator(),
					$belong->getFind(),
					$belong->getSort(),
					$belong->getBelongsTo(),
					$belong->getOperator(),
					$belong->getFind(),
					$belong->getSort(),
				]);

				$sql = "
	                INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_GROUP_BELONG)."`
	                (
	                    `group_id`,
	                    `belong_id`
	                ) VALUES (
	                    %s,
	                    %s
	                ) ON DUPLICATE KEY UPDATE
	                    `group_id` = %s,
	                    `belong_id` = %s
	                ;";

				ACPT_DB::executeQueryOrThrowException($sql, [
					$group->getId(),
					$belong->getId(),
					$group->getId(),
					$belong->getId(),
				]);
			}
		}
	}

	/**
	 * @throws Exception
	 */
	private function copyMetaFieldLabel()
	{
		$sql = "SELECT * FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION)."` WHERE `option_key` = %s;";
		$options = ACPT_DB::getResults($sql, ["label"]);

		foreach ($options as $option){

			$sql = "UPDATE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` SET `field_label` = %s WHERE id = %s;";

			ACPT_DB::executeQueryOrThrowException($sql, [
				$option->option_value,
				$option->meta_field_id
			]);
		}
	}

	/**
	 * @throws Exception
	 */
	private function migrateOptionPageFields()
	{
		$optionPages = OptionPageRepository::get([]);

		$this->migrateOptionPageFieldValues($optionPages);
	}

	/**
	 * @param OptionPageModel[] $optionPages
	 */
	private function migrateOptionPageFieldValues($optionPages = [])
	{
		foreach ($optionPages as $optionPage){
			$sql = "SELECT * FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_OPTION_PAGE_META_BOX)."` WHERE page = %s";
			$opBoxes = ACPT_DB::getResults($sql, [
				$optionPage->getMenuSlug()
			]);

			foreach ($opBoxes as $opBox){
				$sql = "SELECT * FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."` WHERE meta_box_id = %s";
				$opFields = ACPT_DB::getResults($sql, [
					$opBox->id
				]);

				foreach ($opFields as $opField){
					$oldKey = Strings::toDBFormat( $opBox->meta_box_name ) . '_' . Strings::toDBFormat($opField->field_name );
					$newKey = Strings::toDBFormat( $optionPage->getMenuSlug() ) . "_" . $oldKey;

					if(!empty(get_option($oldKey))){
						update_option($newKey, get_option($oldKey));
					}
				}
			}

			if($optionPage->hasChildren()){
				$this->migrateOptionPageFieldValues($optionPage->getChildren());
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function down(): array
	{
		return [
			"ALTER TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` ADD `post_type` VARCHAR(20) DEFAULT NULL ",
			"RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BOX)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_META_BOX)."`;",
			"RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_FIELD)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_FIELD)."`;",
			"RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_ADVANCED_OPTION)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_ADVANCED_OPTION)."`;",
			"RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_OPTION)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_OPTION)."`;",
			"RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_VISIBILITY)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_VISIBILITY)."`;",
			"RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_RELATION)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_RELATION)."`;",
			"RENAME TABLE `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_META_BLOCK)."` TO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_CUSTOM_POST_TYPE_BLOCK)."`;",
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




