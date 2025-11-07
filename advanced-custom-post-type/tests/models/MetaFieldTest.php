<?php

namespace ACPT\Tests;

use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;

class MetaFieldTest extends AbstractTestCase
{
	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_hydrate_basic_meta_field()
	{
		$data = [
			"id" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
			"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
			"db_name" => "member-info_credits-left",
			"ui_name" => "מידע חברות - Credits-left",
			"name" => "credits-left",
			"type" => "Number",
			"defaultValue" => "0",
			"description" => "",
			"isRequired" => false,
			"showInArchive" => false,
			"quickEdit" => false,
			"filterableInAdmin" => false,
			"sort" => 1,
			"advancedOptions" => [
				[
					"id" => "1fb8f90f-812c-4e02-9166-4d48c85107a4",
					"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
					"fieldId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
					"key" => "label",
					"value" => "value of advanced option"
				]
			],
			"options" => [
			],
			"relations" => [
			],
			"blocks" => [
			],
			"blockId" => null,
			"blockName" => null,
			"validationRules" => [
				[
					"id" => "e89c4309-916c-4b5a-b63d-4acc4f833be9",
					"condition" => "=",
					"message" => "message",
					"value" => "44"
				],
				[
					"id" => "c9a0e2bb-8506-4527-a708-973b451a5bf7",
					"condition" => "blank",
					"message" => "message",
					"value" => null
				]
			],
			"visibilityConditions" => [
				[
					"id" => "8542ab54-d0f9-43aa-a892-9e678f6c3b97",
					"type" => "{\"type\":\"VALUE\",\"value\":\"VALUE\"}",
					"operator" => "=",
					"value" => "33"
				]
			],
			"hasManyRelation" => false,
			"hasChildren" => false,
			"children" => [
			],
			"parentId" => null,
			"parentName" => null,
			"isATextualField" => true,
			"isFilterable" => true
		];

		$arrayOfFieldNames = [];
		$arrayOfBlockNames = [];
		$metaFieldModel = MetaFieldModel::fullHydrateFromArray($this->getMetaBox(),0, $data, $arrayOfFieldNames, $arrayOfBlockNames);

		$this->assertInstanceOf(MetaFieldModel::class, $metaFieldModel);
		$this->assertEquals($metaFieldModel->getAdvancedOption('label'), 'value of advanced option');
		$this->assertCount(2, $metaFieldModel->getValidationRules());
		$this->assertCount(1, $metaFieldModel->getVisibilityConditions());
		$this->assertEquals($metaFieldModel->getVisibilityConditions()[0]->getType(), [
			'type' => 'VALUE',
			'value' => 'VALUE',
		]);
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_hydrate_meta_field_with_children()
	{
		$data = [
			"id" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
			"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
			"db_name" => "member-info_credits-left",
			"ui_name" => "מידע חברות - Credits-left",
			"name" => "credits-left",
			"type" => "Repeater",
			"defaultValue" => "0",
			"description" => "",
			"isRequired" => false,
			"showInArchive" => false,
			"quickEdit" => false,
			"filterableInAdmin" => false,
			"sort" => 1,
			"advancedOptions" => [
				[
					"id" => "1fb8f90f-812c-4e02-9166-4d48c85107a4",
					"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
					"fieldId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
					"key" => "label",
					"value" => "קרדיטים שנשארו."
				]
			],
			"options" => [
			],
			"relations" => [
			],
			"blocks" => [
			],
			"blockId" => null,
			"blockName" => null,
			"validationRules" => [
			],
			"visibilityConditions" => [
			],
			"hasManyRelation" => false,
			"hasChildren" => false,
			"children" => [
				[
					"id" => "121d86e0-dd52-49db-b2d4-3fa3efd0bf8a",
					"showInArchive" => false,
					"filterableInAdmin" => false,
					"quickEdit" => false,
					"isRequired" => false,
					"name" => "sample",
					"type" => "Text",
					"defaultValue" => "",
					"description" => "",
					"advancedOptions" => [
						[
							"id" => "57084fc5-a8e5-4196-98b6-1c0cc19c8f60",
							"key" => "before",
							"value" => "<p>"
						],
						[
							"id" => "3776f22f-d343-4a94-80dc-3211edfbf262",
							"key" => "after",
							"value" => "</p>"
						],
					],
					"validationRules" => [
						[
							"id" => "af8eb9c2-a75e-4569-adf1-860dab6df65f",
							"condition" => "=",
							"value" => "23",
							"message" => "message",
						]
					]
				]
			],
			"parentId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
		];

		$arrayOfFieldNames = [];
		$arrayOfBlockNames = [];
		$metaFieldModel = MetaFieldModel::fullHydrateFromArray($this->getMetaBox(), 0, $data, $arrayOfFieldNames, $arrayOfBlockNames);

		$this->assertCount(1, $metaFieldModel->getChildren());
		$this->assertCount(2, $metaFieldModel->getChildren()[0]->getAdvancedOptions());
		$this->assertCount(1, $metaFieldModel->getChildren()[0]->getValidationRules());
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_hydrate_meta_field_with_blocks()
	{
		$data = [
			"id" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
			"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
			"db_name" => "member-info_credits-left",
			"ui_name" => "מידע חברות - Credits-left",
			"name" => "credits-left",
			"type" => "FlexibleContent",
			"defaultValue" => "0",
			"description" => "",
			"isRequired" => false,
			"showInArchive" => false,
			"quickEdit" => false,
			"filterableInAdmin" => false,
			"sort" => 1,
			"advancedOptions" => [
				[
					"id" => "1fb8f90f-812c-4e02-9166-4d48c85107a4",
					"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
					"fieldId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
					"key" => "label",
					"value" => "קרדיטים שנשארו."
				]
			],
			"options" => [
			],
			"relations" => [
			],
			"blocks" => [
				[
					"id" => "eecdd944-247a-42a0-b45b-3ed69db6705a",
					"name" => "new_block",
					"label" => "block label",
					"fields" => [
						[
							"id" => "e5d49d5f-11aa-4b81-a38c-b48a759098d0",
							"parentId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
							"blockId" => "eecdd944-247a-42a0-b45b-3ed69db6705a",
							"showInArchive" => false,
							"filterableInAdmin" => false,
							"quickEdit" => false,
							"isRequired" => false,
							"name" => "number",
							"type" => "Textarea",
							"defaultValue" => "",
							"description" => ""
						],
						[
							"id" => "71aeeb9a-0ce5-4067-9738-3dc145b3c17c",
							"parentId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
							"blockId" => "eecdd944-247a-42a0-b45b-3ed69db6705a",
							"showInArchive" => false,
							"filterableInAdmin" => false,
							"quickEdit" => false,
							"isRequired" => false,
							"name" => "altro",
							"type" => "Text",
							"defaultValue" => "",
							"description" => ""
						]
					]
				],
				[
					"id" => "ae2791e2-405b-40f6-9d65-d0c67e30cd87",
					"name" => "block_2",
					"label" => "block label",
					"fields" => [
					]
				]
			],
			"blockId" => null,
			"blockName" => null,
			"validationRules" => [
			],
			"visibilityConditions" => [
			],
			"hasManyRelation" => false,
			"hasChildren" => false,
			"children" => [
			],
			"parentId" => null,
			"parentName" => null,
			"isATextualField" => true,
			"isFilterable" => true
		];

		$arrayOfFieldNames = [];
		$arrayOfBlockNames = [];
		$metaFieldModel = MetaFieldModel::fullHydrateFromArray($this->getMetaBox(), 0, $data, $arrayOfFieldNames, $arrayOfBlockNames);

		$this->assertCount(2, $metaFieldModel->getBlocks());
		$this->assertCount(2, $metaFieldModel->getBlocks()[0]->getFields());
		$this->assertNull($metaFieldModel->getBlocks()[0]->getFields()[0]->getParentId());
		$this->assertEquals("eecdd944-247a-42a0-b45b-3ed69db6705a", $metaFieldModel->getBlocks()[0]->getFields()[0]->getBlockId());
	}

	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_hydrate_meta_deeply_nested_field()
	{
		$data = [
			"id" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
			"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
			"db_name" => "member-info_credits-left",
			"ui_name" => "מידע חברות - Credits-left",
			"name" => "credits-left",
			"type" => "FlexibleContent",
			"defaultValue" => "0",
			"description" => "",
			"isRequired" => false,
			"showInArchive" => false,
			"quickEdit" => false,
			"filterableInAdmin" => false,
			"sort" => 1,
			"advancedOptions" => [
				[
					"id" => "1fb8f90f-812c-4e02-9166-4d48c85107a4",
					"boxId" => "b260e9b8-8102-4feb-97f4-c7e9315f36f2",
					"fieldId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
					"key" => "label",
					"value" => "קרדיטים שנשארו."
				]
			],
			"options" => [
			],
			"relations" => [
			],
			"blocks" => [
				[
					"id" => "7b7a4312-569c-47f5-9c62-eae38584b1a1",
					"name" => "new_block",
					"label" => "",
					"fields" => [
						[
							"id" => "4d8084f7-199c-4666-a7a3-0fd1c3b81c1d",
							"parentId" => "136cf931-aa7f-41b0-aa0f-306c0c094925",
							"blockId" => "7b7a4312-569c-47f5-9c62-eae38584b1a1",
							"showInArchive" => false,
							"filterableInAdmin" => false,
							"quickEdit" => false,
							"isRequired" => false,
							"name" => "meta_box_field",
							"type" => "Repeater",
							"defaultValue" => "",
							"description" => "",
							"children" => [
								[
									"id" => "b064e5fe-8bf3-4aa5-9d57-73c486dd852f",
									"parentId" => "4d8084f7-199c-4666-a7a3-0fd1c3b81c1d",
									"blockId" => "",
									"showInArchive" => false,
									"filterableInAdmin" => false,
									"quickEdit" => false,
									"isRequired" => false,
									"name" => "meta_box_field",
									"type" => "Repeater",
									"defaultValue" => "",
									"description" => "",
									"children" => [
										[
											"id" => "2f79eafc-cdb2-414f-93c7-b9e6bc7b54c5",
											"parentId" => "b064e5fe-8bf3-4aa5-9d57-73c486dd852f",
											"blockId" => "",
											"showInArchive" => false,
											"filterableInAdmin" => false,
											"quickEdit" => false,
											"isRequired" => false,
											"name" => "meta_box_field",
											"type" => "Url",
											"defaultValue" => "",
											"description" => "",
											"children" => [
											]
										]
									]
								]
							]
						]
					]
				]
			],
			"blockId" => null,
			"blockName" => null,
			"validationRules" => [
			],
			"visibilityConditions" => [
			],
			"hasManyRelation" => false,
			"hasChildren" => false,
			"children" => [
			],
			"parentId" => null,
			"parentName" => null,
			"isATextualField" => true,
			"isFilterable" => true
		];

		$arrayOfFieldNames = [];
		$arrayOfBlockNames = [];
		$metaFieldModel = MetaFieldModel::fullHydrateFromArray($this->getMetaBox(), 0, $data, $arrayOfFieldNames, $arrayOfBlockNames);

		$this->assertCount(1, $metaFieldModel->getBlocks());
		$this->assertCount(1, $metaFieldModel->getBlocks()[0]->getFields());
		$this->assertCount(1, $metaFieldModel->getBlocks()[0]->getFields()[0]->getChildren());
		$this->assertCount(1, $metaFieldModel->getBlocks()[0]->getFields()[0]->getChildren()[0]->getChildren());
	}

	/**
	 * @return MetaBoxModel
	 * @throws \Exception
	 */
	private function getMetaBox()
	{
		$group = MetaGroupModel::hydrateFromArray([
			'name' => 'meta_group'
		]);

		return MetaBoxModel::hydrateFromArray([
			'group' => $group,
			'name' => 'meta_box',
			'sort' => 1,
		]);
	}
}