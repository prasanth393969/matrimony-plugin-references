<?php

use ACPT\Constants\MetaTypes;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Schema_Migration;

class FixMigrationFromV1 extends ACPT_Schema_Migration
{
	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function up(): array
	{
		try {
			ACPT_DB::startTransaction();
			$this->migrateOptionPageFields();
			ACPT_DB::commitTransaction();

			return [];
		} catch (\Exception $exception){
			ACPT_DB::rollbackTransaction();

			return [];
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
	 * @param array $optionPages
	 *
	 * @throws Exception
	 */
	private function migrateOptionPageFieldValues($optionPages = [])
	{
		foreach ($optionPages as $optionPage){

			$groups = MetaRepository::get([
				'belongsTo' => MetaTypes::OPTION_PAGE,
				'find' => $optionPage->getMenuSlug()
			]);

			foreach ($groups as $group){
				foreach ($group->getBoxes() as $boxModel){
					foreach ($boxModel->getFields() as $field){
						$oldKey = Strings::toDBFormat( $boxModel->getName() ) . '_' . Strings::toDBFormat($field->getName() );
						$newKey = Strings::toDBFormat( $optionPage->getMenuSlug() ) . "_" . $oldKey;

						$extras = [
							'id',
							'type',
							'label',
							'currency',
							'weight',
							'length',
							'lat',
							'lng',
						];

						foreach ($extras as $extra){
							if(!empty(get_option($oldKey.'_'.$extra))){
								update_option($newKey.'_'.$extra, get_option($oldKey.'_'.$extra));
							}
						}
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
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function version(): string
	{
		return '2.0.2';
	}
}




