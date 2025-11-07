<?php

namespace ACPT\Tests;

use ACPT\Core\Generators\Meta\TableFieldGenerator;

class TableFieldGeneratorTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function empty()
	{
		$this->makeAnAssertionAboutTheJSON([], false);
	}

	/**
	 * @test
	 */
	public function missingFields()
	{
		$this->makeAnAssertionAboutTheJSON([
			'not_allowed' => 'value'
		], false);
	}

	/**
	 * @test
	 */
	public function emptyFields()
	{
		$this->makeAnAssertionAboutTheJSON([
			'settings' => [],
			'data' => [],
		], false);
	}

	/**
	 * @test
	 */
	public function wrongLayout()
	{
		$this->makeAnAssertionAboutTheJSON([
			'settings' => [
				'layout' => 'wrongLayout',
				'header' => false,
				'footer' => false,
				'columns' => 3,
				'rows' => 5,
			],
			'data' => [],
		], false);
	}

	/**
	 * @test
	 */
	public function minimumValidStructure()
	{
		$this->makeAnAssertionAboutTheJSON([
			'settings' => [
				'layout' => 'vertical',
				'header' => false,
				'footer' => false,
				'columns' => 3,
				'rows' => 5,
			],
			'data' => [],
		], true);
	}

	/**
	 * @test
	 */
	public function invalidData()
	{
		$this->makeAnAssertionAboutTheJSON([
			'settings' => [
				'layout' => 'vertical',
				'header' => false,
				'footer' => false,
				'columns' => 3,
				'rows' => 5,
			],
			'data' => [
				[
					'value' => 'ciao',
					'settings' => []
				],
			],
		], false);

		$this->makeAnAssertionAboutTheJSON([
			'settings' => [
				'layout' => 'vertical',
				'header' => false,
				'footer' => false,
				'columns' => 3,
				'rows' => 5,
			],
			'data' => [
				[
					[
						'cavolo' => 'ciao',
						'settings' => []
					],
					[
						'cavolo' => 'miao',
						'settings' => []
					],
				]
			],
		], false);
	}

	/**
	 * @test
	 */
	public function completeValidStructure()
	{
		$this->makeAnAssertionAboutTheJSON([
			'settings' => [
				'layout' => 'vertical',
				'header' => false,
				'footer' => false,
				'columns' => 2,
				'rows' => 2,
			],
			'data' => [
				0 => [
					0 => [
						'value' => 'ciao',
						'settings' => [],
					],
					1 => [
						'value' => 'ciao',
						'settings' => [],
					],
				],
				1 => [
					0 => [
						'value' => 'ciao',
						'settings' => [],
					],
					1 => [
						'value' => 'ciao',
						'settings' => [],
					],
				]
			],
		], true);
	}

	/**
	 * @param array $json
	 * @param bool $expected
	 */
	private function makeAnAssertionAboutTheJSON($json = [], $expected = true)
	{
		$generator = new TableFieldGenerator(json_encode($json));
		$isValid = $generator->theJSONIsValid();

		$this->assertEquals($isValid, $expected);
	}
}