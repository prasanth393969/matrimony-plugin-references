<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldAdvancedOptionModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaFieldOptionModel;
use ACPT\Core\Models\Meta\MetaFieldVisibilityModel;
use ACPT\Core\Models\Meta\MetaGroupModel;

class CustomPostTypeMetaBoxModelTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_duplicate_a_basic_cpt_mb()
	{
		$boxModel = $this->getDummyMetaBoxModel();

		$boxModelField = new MetaFieldModel(
			Uuid::v4(),
			$boxModel,
			'field',
			MetaFieldModel::TEXT_TYPE,
			false,
			false,
			1,
		);

		$boxModel->addField($boxModelField);


		$duplicateModel = $boxModel->duplicate();

		$this->runTests($boxModel, $duplicateModel);
	}

	/**
	 * @test
	 */
	public function can_duplicate_a_cpt_mb_with_options()
	{
		$boxModel = $this->getDummyMetaBoxModel();

		$boxModelField = new MetaFieldModel(
			Uuid::v4(),
			$boxModel,
			'field2',
			MetaFieldModel::SELECT_TYPE,
			false,
			false,
			1,
		);

		$boxModelOption1 = new MetaFieldOptionModel(
			Uuid::v4(),
			$boxModelField,
			'label',
			'value',
			1,
		);

		$boxModelOption2 = new MetaFieldOptionModel(
			Uuid::v4(),
			$boxModelField,
			'label2',
			'value2',
			2,
		);

		$boxModelField->addOption($boxModelOption1);
		$boxModelField->addOption($boxModelOption2);

		$boxModel->addField($boxModelField);

		$duplicateModel = $boxModel->duplicate();

		$this->runTests($boxModel, $duplicateModel);
	}

	/**
	 * @test
	 */
	public function can_duplicate_a_cpt_mb_with_advanced_options()
	{
		$boxModel = $this->getDummyMetaBoxModel();

		$boxModelField = new MetaFieldModel(
			Uuid::v4(),
			$boxModel,
			'field2',
			MetaFieldModel::TEXT_TYPE,
			false,
			false,
			1,
		);

		$boxModelOption1 = new MetaFieldAdvancedOptionModel(
			Uuid::v4(),
			$boxModelField,
			'key',
			'value',
		);

		$boxModelOption2 = new MetaFieldAdvancedOptionModel(
			Uuid::v4(),
			$boxModelField,
			'key2',
			'value2',
		);

		$boxModelField->addAdvancedOption($boxModelOption1);
		$boxModelField->addAdvancedOption($boxModelOption2);

		$boxModel->addField($boxModelField);

		$duplicateModel = $boxModel->duplicate();

		$this->runTests($boxModel, $duplicateModel);
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_duplicate_a_cpt_mb_with_visibility_conditions()
	{
		$boxModel = $this->getDummyMetaBoxModel();

		$boxModelField = new MetaFieldModel(
			Uuid::v4(),
			$boxModel,
			'field2',
			MetaFieldModel::TEXT_TYPE,
			false,
			false,
			1,
		);

		$condition1 = new MetaFieldVisibilityModel(
			Uuid::v4(),
			$boxModelField,
			[
				'type' => 'VALUE'
			],
			'=',
			'123',
			1,
		);

		$condition2 = new MetaFieldVisibilityModel(
			Uuid::v4(),
			$boxModelField,
			[
				'type' => 'VALUE'
			],
			'!=',
			'123',
			2,
		);

		$boxModelField->addVisibilityCondition($condition1);
		$boxModelField->addVisibilityCondition($condition2);

		$boxModel->addField($boxModelField);

		$duplicateModel = $boxModel->duplicate();

		$this->runTests($boxModel, $duplicateModel);
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_duplicate_a_cpt_mb_with_children()
	{
		$boxModel = $this->getDummyMetaBoxModel();

		$boxModelField = new MetaFieldModel(
			Uuid::v4(),
			$boxModel,
			'field2',
			MetaFieldModel::REPEATER_TYPE,
			false,
			false,
			1,
		);

		$child1 = new MetaFieldModel(
			Uuid::v4(),
			$boxModel,
			'field2',
			MetaFieldModel::TEXT_TYPE,
			false,
			false,
			1,
		);

		$child2 = new MetaFieldModel(
			Uuid::v4(),
			$boxModel,
			'field33',
			MetaFieldModel::TEXT_TYPE,
			false,
			false,
			1,
		);

		$boxModelField->addChild($child1);
		$boxModelField->addChild($child2);

		$boxModel->addField($boxModelField);

		$duplicateModel = $boxModel->duplicate();

		$this->runTests($boxModel, $duplicateModel);
	}

	/**
	 * @return MetaBoxModel
	 */
	private function getDummyMetaBoxModel()
	{
		$group = new MetaGroupModel(
			Uuid::v4(),
			'group'
		);

		return new MetaBoxModel(
			Uuid::v4(),
			$group,
			'test',
			1
		);
	}

	/**
	 * @param MetaBoxModel $boxModel
	 * @param MetaBoxModel $duplicateModel
	 */
	protected function runTests(MetaBoxModel $boxModel, MetaBoxModel $duplicateModel)
	{
		$this->assertNotEquals($duplicateModel->getId(), $boxModel->getId());

		foreach ($duplicateModel->getFields() as $i => $duplicatedFieldModel){

			$originalFieldModel = $boxModel->getFields()[$i];

			$this->assertNotEquals($duplicatedFieldModel->getId(), $originalFieldModel->getId());
			$this->assertNotEquals($duplicatedFieldModel->getBox()->getId(), $originalFieldModel->getBox()->getId());

			foreach ($duplicatedFieldModel->getOptions() as $k => $duplicatedOptionModel){

				$originalOptionModel = $originalFieldModel->getOptions()[$k];

				$this->assertNotEquals($duplicatedOptionModel->getId(), $originalOptionModel->getId());
				$this->assertNotEquals($duplicatedOptionModel->getMetaField()->getId(), $originalOptionModel->getMetaField()->getId());
			}

			foreach ($duplicatedFieldModel->getAdvancedOptions() as $a => $duplicatedAdvancedOptionModel){

				$originalAdvancedOptionModel = $originalFieldModel->getAdvancedOptions()[$a];

				$this->assertNotEquals($duplicatedAdvancedOptionModel->getId(), $originalAdvancedOptionModel->getId());
				$this->assertNotEquals($duplicatedAdvancedOptionModel->getMetaField()->getId(), $originalAdvancedOptionModel->getMetaField()->getId());
			}

			foreach ($duplicatedFieldModel->getVisibilityConditions() as $v => $duplicatedVisibilityConditionModel){

				$originalVisibilityConditionModel = $originalFieldModel->getVisibilityConditions()[$v];

				$this->assertNotEquals($duplicatedVisibilityConditionModel->getId(), $originalVisibilityConditionModel->getId());
				$this->assertNotEquals($duplicatedVisibilityConditionModel->getMetaField()->getId(), $originalVisibilityConditionModel->getMetaField()->getId());
			}

			foreach ($duplicatedFieldModel->getChildren() as $c => $duplicatedChildModel){

				$originalChildModel = $originalFieldModel->getChildren()[$c];

				$this->assertNotEquals($duplicatedChildModel->getId(), $originalChildModel->getId());
				$this->assertNotEquals($duplicatedChildModel->getParentId(), $originalChildModel->getParentId());
			}
		}
	}
}