<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Validation\ValidationRuleModel;
use ACPT\Utils\Checker\ValidationRulesChecker;

class ValidationRulesCheckerTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function no_validation()
	{
		$this->assertTrue((new ValidationRulesChecker("23", []))->validate());
	}

	/**
	 * @test
	 */
	public function blank_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::IS_BLANK,
			'message' => 'Value is not blank',
			'sort' => 1,
			'value' => "23",
		]);

		$this->assertFalse((new ValidationRulesChecker("23", [$condition]))->validate());
		$this->assertTrue((new ValidationRulesChecker("", [$condition]))->validate());
	}

	/**
	 * @test
	 */
	public function not_blank_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::IS_NOT_BLANK,
			'message' => 'Value is blank',
			'sort' => 1,
			'value' => "23",
		]);

		$this->assertTrue((new ValidationRulesChecker("23", [$condition]))->validate());
		$this->assertFalse((new ValidationRulesChecker("", [$condition]))->validate());
	}

	/**
	 * @test
	 */
	public function equals_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::EQUALS,
			'message' => 'Value is not equals to 23',
			'sort' => 1,
			'value' => "23",
		]);

		$this->assertTrue((new ValidationRulesChecker("23", [$condition]))->validate());
	}

	/**
	 * @test
	 */
	public function not_equals_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::NOT_EQUALS,
			'message' => 'Value is equals to 342332',
			'sort' => 1,
			'value' => "342332",
		]);

		$this->assertTrue((new ValidationRulesChecker("23", [$condition]))->validate());
	}

	/**
	 * @test
	 */
	public function between_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::GREATER_THAN,
			'message' => 'Value is not greater than 6',
			'sort' => 1,
			'value' => "6",
		]);

		$condition2 = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::LOWER_THAN,
			'message' => 'Value is not lower than 12',
			'sort' => 1,
			'value' => "12",
		]);

		$this->assertTrue((new ValidationRulesChecker("8", [$condition, $condition2]))->validate());
		$this->assertFalse((new ValidationRulesChecker("24", [$condition, $condition2]))->validate());
	}

	/**
	 * @test
	 */
	public function max_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::MAX_LENGTH,
			'message' => 'Maximum value length is 6',
			'sort' => 1,
			'value' => "6",
		]);

		$this->assertTrue((new ValidationRulesChecker("word", [$condition]))->validate());
		$this->assertFalse((new ValidationRulesChecker("long word", [$condition]))->validate());
	}

	/**
	 * @test
	 */
	public function min_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::MIN_LENGTH,
			'message' => 'Minimum value length is 6',
			'sort' => 1,
			'value' => "8",
		]);

		$this->assertFalse((new ValidationRulesChecker("word", [$condition]))->validate());
		$this->assertTrue((new ValidationRulesChecker("long word", [$condition]))->validate());
	}

	/**
	 * @test
	 */
	public function regex_validation()
	{
		$condition = ValidationRuleModel::hydrateFromArray([
			'id' => Uuid::v4(),
			'condition' => ValidationRuleModel::REGEX,
			'message' => 'Not a valid email',
			'sort' => 1,
			'value' => "/^\\S+@\\S+\\.\\S+$/",
		]);

		$this->assertFalse((new ValidationRulesChecker("word", [$condition]))->validate());
		$this->assertTrue((new ValidationRulesChecker("maurocassani1978@gmail.com", [$condition]))->validate());
		$this->assertTrue((new ValidationRulesChecker("info@acpt.io", [$condition]))->validate());
	}
}



