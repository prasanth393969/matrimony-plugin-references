<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Utils\PHP\Arrays;

class ArraysTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function simple_test()
	{
		$array = [
			'repeater' => 444
		];

		$expectedValue = 444;
		$value = Arrays::valueFromIndex($array, 'repeater');

		$this->assertEquals($expectedValue, $value);
	}

	/**
	 * @test
	 */
	public function more_complex_test()
	{
		$array = [
			'repeater' => [
				0 => [
					'nested' => [
						0 => [
							'value' => 123
						],
						1 => [
							'value' => 444,
						],
						2 => [
							'value' => 777
						],
					]
				]
			]
		];

		$expectedValue = 777;
		$value = Arrays::valueFromIndex($array, 'repeater[0]nested[2]value');
		$value2 = Arrays::valueFromIndex($array, 'repeater[0]nested[3]value');

		$this->assertEquals($expectedValue, $value);
		$this->assertNull($value2);
	}

	/**
	 * @test
	 */
	public function array_flat()
	{
		$array = [
			'key' => 'value',
			'nested' => [
				'key' => '123'
			],
			'nested2' => [
				'nested3' => [
					'nested4' => '123'
				]
			],
		];

		$arrayFlat = Arrays::arrayFlat($array);
		$expectedValue = [
			'key' => 'value',
			'nested.key' => '123',
			'nested2.nested3.nested4' => '123',
		];

		$this->assertEquals($expectedValue, $arrayFlat);
	}

	/**
	 * @test
	 */
	public function to_plain_text()
	{
		$array = [
			'key' => 'value',
			'nested' => [
				'key' => '123'
			],
			'nested2' => [
				'nested3' => [
					'nested4' => '123'
				]
			],
		];

		$toPlainText = Arrays::toPlainText($array);
		$expectedValue = "'key': 'value', 'nested.key': '123', 'nested2.nested3.nested4': '123'";

		$this->assertEquals($expectedValue, $toPlainText);
	}

	/**
	 * @test
	 */
	public function array_unique_of_entities()
	{
		$uuid = Uuid::v4();
		$model = new MetaGroupModel($uuid, 'name');
		$model2 = new MetaGroupModel($uuid, 'name');

		$entities = [
			$model,
			$model2,
		];

		$this->assertCount(1, Arrays::arrayUniqueOfEntities($entities));
	}

	/**
	 * @test
	 */
	public function array_unique_by_key()
	{
		$array = [
			[
				'name' => 'ciao',
				'label' => 'pippo'
			],
			[
				'name' => 'ciao',
				'label' => 'pippo2'
			],
			[
				'name' => 'ciao',
				'label' => 'pippo33'
			],
		];

		$this->assertCount(1, Arrays::arrayUniqueByKey($array, 'name'));
	}
}