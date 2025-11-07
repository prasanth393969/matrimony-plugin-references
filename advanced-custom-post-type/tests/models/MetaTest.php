<?php

namespace ACPT\Tests;

use ACPT\Constants\Logic;
use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldAdvancedOptionModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Models\Validation\ValidationRuleModel;

class MetaTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function cannot_create_a_group()
	{
		try {
			new MetaGroupModel(Uuid::v4(), 'èèèè');
		} catch (\Exception $exception){
			$this->assertEquals($exception->getMessage(), 'èèèè is not valid name');
		}
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_create_a_group_with_boxes()
	{
		$groupFromArray = MetaGroupModel::hydrateFromArray([
			'name' => 'from-array',
		]);

		$this->assertEquals($groupFromArray->getName(), 'from-array');

		$group = new MetaGroupModel(Uuid::v4(), 'prova', 'èèèèèè');

		$this->assertEquals($group->getName(), 'prova');
		$this->assertEquals($group->getLabel(), 'èèèèèè');
		$this->assertEquals($group->getUIName(), 'èèèèèè');

		// 1. Add/remove belongs

		try {
			$belong = new BelongModel(
				Uuid::v4(),
				'not-allowed',
				1
			);

			$group->addBelong($belong);
		} catch (\Exception $exception){
			$this->assertEquals($exception->getMessage(), 'not-allowed is not allowed');
		}

		$belong = new BelongModel(
			Uuid::v4(),
			MetaTypes::CUSTOM_POST_TYPE,
			12
		);

		$group->addBelong($belong);

		$this->assertEquals(1, count($group->getBelongs()));

		$group->removeBelong($belong);

		$this->assertEquals(0, count($group->getBelongs()));

		// 2. Add/remove boxes
		$box = new MetaBoxModel(Uuid::v4(), $group, 'box-name', 1);
		$box2 = new MetaBoxModel(Uuid::v4(), $group, 'box-name-2', 2, 'èèèèèè');

		$group->addBox($box);
		$group->addBox($box2);

		$this->assertEquals(2, count($group->getBoxes()));
		$this->assertInstanceOf(MetaBoxModel::class, $group->getBox('box-name'));

		$group->removeBox($box);
		$group->removeBox($box2);

		$this->assertEquals(0, count($group->getBoxes()));
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_create_a_group_with_fields()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'prova', 'èèèèèè');
		$box = new MetaBoxModel(Uuid::v4(), $group, 'box-name', 1);

		try {
			new MetaFieldModel(Uuid::v4(), $box, 'field', 'not-allowed', false, false, 1);
		} catch (\Exception $exception){
			$this->assertEquals($exception->getMessage(), 'not-allowed is not a valid field type for this meta box field');
		}

		$belong = new BelongModel(
			Uuid::v4(),
			MetaTypes::CUSTOM_POST_TYPE,
			1,
			Logic::AND
		);

		$group->addBelong($belong);

		$belong2 = new BelongModel(
			Uuid::v4(),
			MetaTypes::USER,
			1
		);

		$group->addBelong($belong2);

		$field = MetaFieldModel::hydrateFromArray([
			'box' => $box,
			'name' => 'field-name',
			'type' => MetaFieldModel::TEXT_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'sort' => 1,
			'defaultValue' => null,
			'description' => null
		]);

		$field2 = MetaFieldModel::hydrateFromArray([
			'box' => $box,
			'name' => 'field-name-2',
			'type' => MetaFieldModel::TEXT_TYPE,
			'showInArchive' => true,
			'isRequired' => false,
			'sort' => 2,
			'defaultValue' => null,
			'description' => null
		]);

		$box->addField($field);
		$box->addField($field2);

		$this->assertEquals(2, count($box->getFields()));

		$box->removeField($field);

		$this->assertEquals(1, count($box->getFields()));

		$group->addBox($box);

		$this->assertEquals(1, count($group->getBoxes()));
		$this->assertInstanceOf(MetaBoxModel::class, $group->getBox('box-name'));

		$miniArray = $group->arrayRepresentation('mini');

		$this->assertEquals($miniArray['belongs'][0]['belongsTo'], MetaTypes::CUSTOM_POST_TYPE);
		$this->assertEquals($miniArray['belongs'][1]['belongsTo'], MetaTypes::USER);
		$this->assertEquals($miniArray['boxes'][0]['name'], 'box-name');
		$this->assertEquals($miniArray['boxes'][0]['count'], 1);

		$group->removeBox($box);

		$this->assertEquals(0, count($group->getBoxes()));
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function meta_field_array_representation()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'prova', 'èèèèèè');
		$box = new MetaBoxModel(Uuid::v4(), $group, 'box-name', 1, 'èèèèèè');
		$field = MetaFieldModel::hydrateFromArray([
			'box' => $box,
			'name' => 'field-name',
			'type' => MetaFieldModel::TEXT_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'sort' => 1,
			'defaultValue' => null,
			'description' => null
		]);

		$validationRule = ValidationRuleModel::hydrateFromArray([
			'condition' => ValidationRuleModel::NOT_EQUALS,
			'value' => '123',
			'sort' => 1,
			'message' => 'message',
		]);

		$validationRule2 = ValidationRuleModel::hydrateFromArray([
			'condition' => ValidationRuleModel::MAX_LENGTH,
			'value' => '20',
			'sort' => 2,
			'message' => 'message',
		]);

		$field->addValidationRule($validationRule);
		$field->addValidationRule($validationRule2);

		$advancedOption = MetaFieldAdvancedOptionModel::hydrateFromArray([
			'metaField' => $field,
			'key' => 'max',
			'value' => '123'
		]);

		$field->addAdvancedOption($advancedOption);

		$box->addField($field);
		$group->addBox($box);

		$belong = new BelongModel(
			Uuid::v4(),
			MetaTypes::CUSTOM_POST_TYPE,
			1,
			Logic::AND,
		Operator::EQUALS,
			'page'
		);

		$group->addBelong($belong);

		$arrayRepresentation = $group->arrayRepresentation();

		$this->assertEquals('page', $arrayRepresentation['belongs'][0]['find']);
		$this->assertEquals(MetaTypes::CUSTOM_POST_TYPE, $arrayRepresentation['belongs'][0]['belongsTo']);

		$this->assertEquals('box-name', $arrayRepresentation['boxes'][0]['name']);
		$this->assertEquals('èèèèèè', $arrayRepresentation['boxes'][0]['label']);
		$this->assertEquals('èèèèèè', $arrayRepresentation['boxes'][0]['UIName']);
		$this->assertEquals('field-name', $arrayRepresentation['boxes'][0]['fields'][0]['name']);
		$this->assertEquals(2, count($arrayRepresentation['boxes'][0]['fields'][0]['validationRules']));
		$this->assertEquals(1, count($arrayRepresentation['boxes'][0]['fields'][0]['advancedOptions']));
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function duplicate_meta_field()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'prova', 'èèèèèè');
		$box = new MetaBoxModel(Uuid::v4(), $group, 'box-name', 1);
		$field = MetaFieldModel::hydrateFromArray([
			'box' => $box,
			'name' => 'field-name',
			'type' => MetaFieldModel::TEXT_TYPE,
			'showInArchive' => false,
			'isRequired' => false,
			'sort' => 1,
			'defaultValue' => null,
			'description' => null
		]);

		$validationRule = ValidationRuleModel::hydrateFromArray([
			'condition' => ValidationRuleModel::NOT_EQUALS,
			'value' => '123',
			'message' => 'message',
			'sort' => 1
		]);

		$validationRule2 = ValidationRuleModel::hydrateFromArray([
			'condition' => ValidationRuleModel::MAX_LENGTH,
			'value' => '20',
			'message' => 'message',
			'sort' => 2
		]);

		$field->addValidationRule($validationRule);
		$field->addValidationRule($validationRule2);

		$duplicate = $field->duplicate();

		$this->assertEquals($duplicate->getName(), $field->getName());
		$this->assertEquals(count($duplicate->getValidationRules()), count($field->getValidationRules()));
	}
}
