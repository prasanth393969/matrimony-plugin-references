<?php

namespace ACPT\Tests;

use ACPT\Constants\Logic;
use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;
use ACPT\Constants\Visibility;
use ACPT\Core\Helper\MetaFactory;
use ACPT\Core\Models\Meta\MetaFieldVisibilityModel;
use ACPT\Utils\Checker\FieldVisibilityChecker;


class FieldVisibilityCheckerTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function simpleLiveTest()
	{
		$field = MetaFactory::createField();

		$visibilityCondition = MetaFieldVisibilityModel::hydrateFromArray([
			'metaField' => $field,
			'type' => [
				'type' => 'VALUE',
				'value' => 'VALUE',
			],
		    'operator' => Operator::EQUALS,
	        'value' => "33",
	        'sort' => 1,
            'logic' => Logic::OR,
		    'backEnd' => true,
		    'frontEnd' => true
		]);

		$visibilityCondition2 = MetaFieldVisibilityModel::hydrateFromArray([
			'metaField' => $field,
			'type' => [
				'type' => 'VALUE',
				'value' => 'VALUE',
			],
			'operator' => Operator::EQUALS,
			'value' => "66",
			'sort' => 2,
			'logic' => Logic::OR,
			'backEnd' => true,
			'frontEnd' => true
		]);

		$field->addVisibilityCondition($visibilityCondition);
		$field->addVisibilityCondition($visibilityCondition2);

		$liveData = [
			[
				'id' => $field->getDbName(),
				'formId' => $field->getId(),
				'value' => '66'
			]
		];

		$checker = FieldVisibilityChecker::check(
			Visibility::IS_BACKEND,
			"2",
				MetaTypes::CUSTOM_POST_TYPE,
			$field,
			$liveData
		);

		$this->assertTrue($checker);

		$liveData = [
			[
				'id' => $field->getDbName(),
				'formId' => $field->getId(),
				'value' => '656565'
			]
		];

		$checker = FieldVisibilityChecker::check(
			Visibility::IS_BACKEND,
			"2",
			MetaTypes::CUSTOM_POST_TYPE,
			$field,
			$liveData
		);

		$this->assertFalse($checker);
	}

	/**
	 * @test
	 * @throws \ReflectionException
	 */
	public function liveTestWithTwoFields()
	{
		$field = MetaFactory::createField();
		$field2 = MetaFactory::createField();

		$visibilityCondition = MetaFieldVisibilityModel::hydrateFromArray([
			'metaField' => $field,
			'type' => [
				'type' => 'VALUE',
				'value' => 'VALUE',
			],
			'operator' => Operator::EQUALS,
			'value' => "33",
			'sort' => 1,
			'logic' => Logic::AND,
			'backEnd' => true,
			'frontEnd' => true
		]);

		$visibilityCondition2 = MetaFieldVisibilityModel::hydrateFromArray([
			'metaField' => $field,
			'type' => [
				'type' => 'OTHER_FIELDS',
				'value' => $field2,
			],
			'operator' => Operator::EQUALS,
			'value' => "66",
			'sort' => 2,
			'logic' => null,
			'backEnd' => true,
			'frontEnd' => true
		]);

		$field->addVisibilityCondition($visibilityCondition);
		$field->addVisibilityCondition($visibilityCondition2);

		$liveData = [
			[
				'id' => $field->getDbName(),
				'formId' => $field->getId(),
				'value' => '543543543'
			],
			[
				'id' => $field2->getDbName(),
				'formId' => $field2->getId(),
				'value' => '66'
			]
		];

		$checker = FieldVisibilityChecker::check(
			Visibility::IS_BACKEND,
			"2",
			MetaTypes::CUSTOM_POST_TYPE,
			$field,
			$liveData
		);

		$this->assertFalse($checker);

		$liveData = [
			[
				'id' => $field->getDbName(),
				'formId' => $field->getId(),
				'value' => '33'
			],
			[
				'id' => $field2->getDbName(),
				'formId' => $field2->getId(),
				'value' => '66'
			]
		];

		$checker = FieldVisibilityChecker::check(
			Visibility::IS_BACKEND,
			"2",
			MetaTypes::CUSTOM_POST_TYPE,
			$field,
			$liveData
		);

		$this->assertTrue($checker);
	}

	/**
	 * @test
	 * @throws \ReflectionException
	 */
	public function liveTestWithTwoFieldsWithOrLogic()
	{
		$field = MetaFactory::createField();
		$field2 = MetaFactory::createField();

		$visibilityCondition = MetaFieldVisibilityModel::hydrateFromArray([
			'metaField' => $field,
			'type' => [
				'type' => 'VALUE',
				'value' => 'VALUE',
			],
			'operator' => Operator::EQUALS,
			'value' => "33",
			'sort' => 1,
			'logic' => Logic::OR,
			'backEnd' => true,
			'frontEnd' => true
		]);

		$visibilityCondition2 = MetaFieldVisibilityModel::hydrateFromArray([
			'metaField' => $field,
			'type' => [
				'type' => 'OTHER_FIELDS',
				'value' => $field2,
			],
			'operator' => Operator::EQUALS,
			'value' => "66",
			'sort' => 2,
			'logic' => null,
			'backEnd' => true,
			'frontEnd' => true
		]);

		$field->addVisibilityCondition($visibilityCondition);
		$field->addVisibilityCondition($visibilityCondition2);

		$liveData = [
			[
				'id' => $field->getDbName(),
				'formId' => $field->getId(),
				'value' => '543543543'
			],
			[
				'id' => $field2->getDbName(),
				'formId' => $field2->getId(),
				'value' => '66'
			]
		];

		$checker = FieldVisibilityChecker::check(
			Visibility::IS_BACKEND,
			"2",
			MetaTypes::CUSTOM_POST_TYPE,
			$field,
			$liveData
		);

		$this->assertTrue($checker);

		$liveData = [
			[
				'id' => $field->getDbName(),
				'formId' => $field->getId(),
				'value' => '33'
			],
			[
				'id' => $field2->getDbName(),
				'formId' => $field2->getId(),
				'value' => '54545353534'
			]
		];

		$checker = FieldVisibilityChecker::check(
			Visibility::IS_BACKEND,
			"2",
			MetaTypes::CUSTOM_POST_TYPE,
			$field,
			$liveData
		);

		$this->assertTrue($checker);

		$liveData = [
			[
				'id' => $field->getDbName(),
				'formId' => $field->getId(),
				'value' => 'fffdsew'
			],
			[
				'id' => $field2->getDbName(),
				'formId' => $field2->getId(),
				'value' => '54545353534'
			]
		];

		$checker = FieldVisibilityChecker::check(
			Visibility::IS_BACKEND,
			"2",
			MetaTypes::CUSTOM_POST_TYPE,
			$field,
			$liveData
		);

		$this->assertFalse($checker);
	}
}