<?php

namespace ACPT\Core\Data\Export\Formatter;

use ACPT\Core\Data\Export\DTO\MetadataExportItemDto;

interface MetadataExportFormatterInterface
{
	/**
	 * This function formats a single metadata item
	 *
	 * @param MetadataExportItemDto $dto
	 *
	 * @return mixed
	 */
	public function format(MetadataExportItemDto $dto);
}
