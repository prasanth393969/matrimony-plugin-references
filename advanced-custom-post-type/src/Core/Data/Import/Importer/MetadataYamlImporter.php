<?php

namespace ACPT\Core\Data\Import\Importer;

use Symfony\Component\Yaml\Yaml;

class MetadataYamlImporter extends MetadataJsonImporter implements MetadataImporterInterface
{
	/**
	 * @param $newItemId
	 * @param $data
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function importItem($newItemId, $data)
	{
		$parsed = Yaml::parse($data);

		$this->importParserItem($newItemId, $parsed);
	}
}