<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Utils\PHP\Objects;

class ObjectsTest extends AbstractTestCase
{
	/**
	 * @test
	 * @throws \ReflectionException
	 */
	public function to_std_object()
	{
		$group          = new MetaGroupModel(Uuid::v4(), 'group_name');
		$box            = new MetaBoxModel(Uuid::v4(), $group, 'box_name', 12);
		$emailField     = new MetaFieldModel(Uuid::v4(), $box, 'field_name', MetaFieldModel::EMAIL_TYPE, true, true, 12);
		$dateRangeField = new MetaFieldModel(Uuid::v4(), $box, 'range', MetaFieldModel::DATE_RANGE_TYPE, true, true, 12, ["from" => "2022-01-01", "to" => "2022-02-02"]);
		$urlField       = new MetaFieldModel(Uuid::v4(), $box, 'range', MetaFieldModel::URL_TYPE, true, true, 12, ["url" => "https://google.it", "urlLabel" => "Google"]);

		$stdObjEmail     = Objects::cast(\stdClass::class, $emailField);
		$stdObjDateRange = Objects::cast(\stdClass::class, $dateRangeField);
		$stdObjUrl       = Objects::cast(\stdClass::class, $urlField);

		$this->assertInstanceOf(\stdClass::class, $stdObjEmail);
		$this->assertInstanceOf(\stdClass::class, $stdObjEmail->box);
		$this->assertInstanceOf(\stdClass::class, $stdObjEmail->box->group);
		$this->assertEquals('box_name', $stdObjEmail->box->name);
		$this->assertEquals('group_name', $stdObjEmail->box->group->name);

		$this->assertEquals("2022-01-01", $stdObjDateRange->defaultValue->from);
		$this->assertEquals("2022-02-02", $stdObjDateRange->defaultValue->to);

		$this->assertEquals("https://google.it", $stdObjUrl->defaultValue->url);
		$this->assertEquals("Google", $stdObjUrl->defaultValue->urlLabel);
	}
}