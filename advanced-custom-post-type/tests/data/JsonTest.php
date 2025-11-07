<?php

namespace ACPT\Tests;

use ACPT\Constants\FormatterFormat;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Data\Export\DTO\MetadataExportItemDto;
use ACPT\Core\Data\Export\MetadataExport;
use ACPT\Core\Data\Import\MetadataImport;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Repository\MetaRepository;

class JsonTest extends AbstractTestCase
{
	/**
	 * @test
	 * @throws \Exception
	 */
	public function can_export_json_file()
	{
		$this->create_meta_field();
		$json = $this->export_json();
		$parsed = json_decode($json, true);

		$this->assertNotEmpty($parsed['acpt_meta']);

		$this->import_json($json);
		$this->delete_meta_field();
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	private function export_json()
	{
		$items = [];

		$item             = new MetadataExportItemDto();
		$item->id         = $this->oldest_page_id;
		$item->find       = 'page';
		$item->belongsTo  = MetaTypes::CUSTOM_POST_TYPE;
		$item->metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
			'find' => 'page',
		]);

		$items[] = $item;

		return MetadataExport::export(FormatterFormat::JSON_FORMAT, $items);
	}

	/**
	 * @param $json
	 *
	 * @throws \Exception
	 */
	private function import_json($json)
	{
		MetadataImport::import($this->second_oldest_page_id, FormatterFormat::JSON_FORMAT, $json);

		$originalValue = get_acpt_field([
			'post_id' => $this->oldest_page_id,
			'box_name' => 'box_name',
			'field_name' => 'field_name',
		]);

		$copiedValue = get_acpt_field([
			'post_id' => $this->second_oldest_page_id,
			'box_name' => 'box_name',
			'field_name' => 'field_name',
		]);

		$this->assertEquals($originalValue, $copiedValue);
	}

	/**
	 * create a meta field
	 */
	private function create_meta_field()
	{
		$groupName = 'page';
		$boxName = 'box_name';
		$boxLabel = 'Box label';
		$fieldName = 'field_name';
		$value = 'text text';

		$save_meta_group = save_acpt_meta_group([
			'name' => $groupName,
			'belongs' => [
				[
					'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
					'operator'  => "=",
					"find"      => "page",
				],
			],
		]);

		$this->assertTrue($save_meta_group);

		$save_meta_box = save_acpt_meta_box([
			'groupName' => $groupName,
			'name' => $boxName,
			'label' => $boxLabel,
			'fields' => []
		]);

		$this->assertTrue($save_meta_box);

		$add_meta_field = save_acpt_meta_field(
			[
				'groupName' => $groupName,
				'boxName' => $boxName,
				'name' => $fieldName,
				'type' => MetaFieldModel::TEXT_TYPE,
				'showInArchive' => false,
				'isRequired' => false,
			]
		);

		$this->assertTrue($add_meta_field);

		$add_acpt_meta_field_value = save_acpt_meta_field_value([
			'post_id' => $this->oldest_page_id,
			'box_name' => $boxName,
			'field_name' => $fieldName,
			'value' => $value,
		]);

		$this->assertTrue($add_acpt_meta_field_value);
	}

	/**
	 * delete the meta field
	 */
	private function delete_meta_field()
	{
		$delete_acpt_meta_box = delete_acpt_meta_box('page', 'box_name');

		$this->assertTrue($delete_acpt_meta_box);

		$delete_acpt_meta_group = delete_acpt_meta_group('page');

		$this->assertTrue($delete_acpt_meta_group);
	}
}