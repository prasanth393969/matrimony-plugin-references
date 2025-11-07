<?php

namespace ACPT\Tests;

use ACPT\Utils\Data\DataAggregator;

class DataAggregatorTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function canAggregateData()
	{
		$savedData = array (
			'image' =>
				array (
					0 =>
						array (
							'id' => '3887',
							'original_name' => 'image',
							'type' => 'Image',
							'value' => 'http://localhost:8000/wp-content/uploads/2023/05/image1-59.jpg',
						),
					1 =>
						array (
							'id' => '3500',
							'original_name' => 'image',
							'type' => 'Image',
							'value' => 'http://localhost:8000/wp-content/uploads/2023/05/image5.jpg',
						),
					2 =>
						array (
							'id' => '3498',
							'original_name' => 'image',
							'type' => 'Image',
							'value' => 'http://localhost:8000/wp-content/uploads/2023/05/image3.jpg',
						),
				),
		);

		$aggregateData = DataAggregator::aggregateNestedFieldsData($savedData);

		$this->assertCount(3, $aggregateData);
		$this->assertEquals($aggregateData[0][0]['key'], 'image');
		$this->assertEquals($aggregateData[0][0]['type'], 'Image');
		$this->assertEquals($aggregateData[0][0]['value'], 'http://localhost:8000/wp-content/uploads/2023/05/image1-59.jpg');
		$this->assertEquals($aggregateData[1][0]['key'], 'image');
		$this->assertEquals($aggregateData[1][0]['type'], 'Image');
		$this->assertEquals($aggregateData[1][0]['value'], 'http://localhost:8000/wp-content/uploads/2023/05/image5.jpg');
		$this->assertEquals($aggregateData[2][0]['key'], 'image');
		$this->assertEquals($aggregateData[2][0]['type'], 'Image');
		$this->assertEquals($aggregateData[2][0]['value'], 'http://localhost:8000/wp-content/uploads/2023/05/image3.jpg');
	}
}