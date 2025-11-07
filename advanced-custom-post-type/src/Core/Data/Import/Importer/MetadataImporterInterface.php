<?php

namespace ACPT\Core\Data\Import\Importer;

interface MetadataImporterInterface
{
	/**
	 * This function import a single metadata item
	 *
	 * @param $newItemId
	 * @param $data
	 *
	 * @return mixed
	 */
	public function importItem($newItemId, $data);
}