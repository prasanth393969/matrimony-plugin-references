<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\DTO\BelongModel;
use ACPT\Core\Models\Validation\ValidationRuleModel;

class ValidationRuleTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function cannot_create_a_wrong_validation_rule()
	{
		try {
			new ValidationRuleModel(Uuid::v4(), 'not-allowed', 123, "message");
		} catch (\Exception $exception){
			$this->assertEquals($exception->getMessage(), 'not-allowed is not a valid rule');
		}

		try {
			new ValidationRuleModel(Uuid::v4(), ValidationRuleModel::MAX_LENGTH, 1, "message");
		} catch (\Exception $exception){
			$this->assertEquals($exception->getMessage(), 'Validation value cannot be null');
		}
	}

	/**
	 * @test
	 */
	public function can_create_a_validation_rule()
	{
		$rule = new ValidationRuleModel(Uuid::v4(), ValidationRuleModel::MAX_LENGTH, 12,"1234", "123");
		$rule2 = new ValidationRuleModel(Uuid::v4(), ValidationRuleModel::IS_NOT_BLANK, 23, "message", "321");

		$this->assertInstanceOf(ValidationRuleModel::class, $rule);
		$this->assertInstanceOf(ValidationRuleModel::class, $rule2);
	}
}