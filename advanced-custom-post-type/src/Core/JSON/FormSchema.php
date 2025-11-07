<?php

namespace ACPT\Core\JSON;

use ACPT\Constants\FormAction;

class FormSchema extends AbstractJSONSchema
{
	/**
	 * @inheritDoc
	 */
	function toArray()
	{
		return [
			'type' => 'object',
			'additionalProperties' => false,
			'properties' => [
				'id' => [
					'type' => 'string',
					'format' => 'uuid',
					'readOnly' => true,
				],
				'name' => [
					'type' => 'string',
				],
				'key' => [
					'type' => 'string',
				],
				'label' => [
					'type' => 'string',
				],
				'action' => [
					'type' => 'string',
					'enum' => FormAction::ALLOWED_VALUES,
					'example' => FormAction::CUSTOM,
				],
				'fields' => [
					'type' => 'array',
					'items' => (new FormFieldSchema())->toArray(),
				],
				'meta' => [
					'type' => 'array',
					'items' => (new FormMetadataSchema())->toArray(),
				],
			],
			'required' => [
				'name',
				'key',
				'action',
			]
		];
	}
}