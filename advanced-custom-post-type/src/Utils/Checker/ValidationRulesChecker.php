<?php

namespace ACPT\Utils\Checker;

use ACPT\Core\Models\Validation\ValidationRuleModel;

class ValidationRulesChecker
{
	/**
	 * @var array
	 */
	private array $errors = [];

	/**
	 * @var
	 */
	private $value;

	/**
	 * @var ValidationRuleModel[]
	 */
	private array $rules = [];

	/**
	 * ValidationRulesChecker constructor.
	 *
	 * @param $value
	 * @param array $rules
	 */
	public function __construct($value, array $rules = [])
	{
		$this->value = $value;
		$this->rules = $rules;
	}

	/**
	 * @return bool
	 */
	public function validate(): bool
	{
		if(empty($this->rules)){
			return true;
		}

		foreach ($this->rules as $rule){
			if(!$this->validateAgainstSingleRule($this->value, $rule)){
				$this->errors[] = str_replace("{{v}}", $rule->getValue(), $rule->getMessage());
			}
		}

		return $this->isValid();
	}

	/**
	 * @param $value
	 * @param ValidationRuleModel $rule
	 *
	 * @return bool
	 */
	private function validateAgainstSingleRule($value, ValidationRuleModel $rule): bool
	{
		switch ($rule->getCondition()){
			case ValidationRuleModel::IS_BLANK:
				return empty($value);

			case ValidationRuleModel::IS_NOT_BLANK:
				return !empty($value);

			case ValidationRuleModel::EQUALS:
				return $value == $rule->getValue();

			case ValidationRuleModel::NOT_EQUALS:
				return $value !== $rule->getValue();

			case ValidationRuleModel::GREATER_THAN:
				return $value > $rule->getValue();

			case ValidationRuleModel::GREATER_THAN_EQUALS:
				return $value >= $rule->getValue();

			case ValidationRuleModel::LOWER_THAN:
				return $value < $rule->getValue();

			case ValidationRuleModel::LOWER_THAN_EQUALS:
				return $value <= $rule->getValue();

			case ValidationRuleModel::MAX_LENGTH:
				return mb_strlen($value) <= $rule->getValue();

			case ValidationRuleModel::MIN_LENGTH:
				return mb_strlen($value) >= $rule->getValue();

			case ValidationRuleModel::REGEX:

                $pattern = $rule->getValue();

                // Check if the pattern has proper delimiters
                if (@preg_match($pattern, '') === false) {
                    // No valid delimiters – wrap in default delimiter (e.g. "/")
                    $pattern = '/' . str_replace('/', '\/', $pattern) . '/';
                }

                return preg_match($pattern, $value) === 1;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool
	{
		return empty($this->errors);
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}