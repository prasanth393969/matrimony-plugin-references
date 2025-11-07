<?php

namespace ACPT\Tests;

use ACPT\Core\Validators\ArgumentsArrayValidator;

class ArgumentsArrayValidatorTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_validate_multiple_keys_values()
	{
		$validator = new ArgumentsArrayValidator();

		$mandatory_keys = [
			'box_name' => [
				'required' => true,
				'type' => 'string'
			]
		];

		$args = [
			'box_name' => 'name',
		];

		$validator->validate($mandatory_keys, $args);

		$this->assertTrue($validator->validate($mandatory_keys, $args));

		$args = [
			'boxName' => 'name',
		];

		$this->assertTrue($validator->validate($mandatory_keys, $args));
	}


	/**
	 * @test
	 */
	public function can_validate_nested_array_values()
	{
		$validator = new ArgumentsArrayValidator();

		$mandatory_keys = [
			'nested' => [
				'required' => true,
				'type' => 'array',
				'rules' => [
					'id' => [
						'required' => true,
						'type' => 'string',
					],
					'description' => [
						'required' => true,
						'type' => 'string',
						'enum' => [
							'text',
							'number',
							'textarea'
						]
					],
					'url' => [
						'required' => false,
						'type' => 'string',
					],
				],
			],
		];

		$args = [
			'nested' => [
				'id' => '1234'
			],
		];

		$this->assertFalse($validator->validate($mandatory_keys, $args));

		$args2 = [
			'nested' => [
				'id' => '4321',
				'description' => 'textarea',
				'url' => 'http://url.com',
			],
		];

		$this->assertTrue($validator->validate($mandatory_keys, $args2));
	}
}

class Dummy_Class
{}