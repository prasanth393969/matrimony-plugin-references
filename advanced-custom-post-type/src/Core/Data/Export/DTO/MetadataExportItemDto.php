<?php

namespace ACPT\Core\Data\Export\DTO;

use ACPT\Core\Models\Meta\MetaGroupModel;

class MetadataExportItemDto
{
	/**
	 * @var mixed
	 */
	public $id;

	/**
	 * @var string
	 */
	public $belongsTo;

	/**
	 * @var string
	 */
	public $find;

	/**
	 * @var MetaGroupModel[]
	 */
	public $metaGroups = [];
}