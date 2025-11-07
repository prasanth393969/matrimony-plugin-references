<?php

namespace ACPT\Core\Data\Import;

use ACPT\Constants\FormatterFormat;
use ACPT\Core\Data\Import\Importer\MetadataImporterInterface;
use ACPT\Core\Data\Import\Importer\MetadataJsonImporter;
use ACPT\Core\Data\Import\Importer\MetadataXMLImporter;
use ACPT\Core\Data\Import\Importer\MetadataYamlImporter;

class MetadataImport
{
	/**
	 * @param $newItemId
	 * @param $format
	 * @param $data
	 *
	 * @throws \Exception
	 */
	public static function import($newItemId, $format, $data)
	{
		if(!in_array($format, FormatterFormat::ALLOWED_FORMATS)){
			throw new \Exception($format . ' is not supported format');
		}

		$importer = self::getImporterInstance($format);
		$importer->importItem($newItemId, $data);
	}

	/**
	 * @param $format
	 *
	 * @return MetadataImporterInterface
	 */
	private static function getImporterInstance($format)
	{
		switch ($format){
			case FormatterFormat::JSON_FORMAT:
				return new MetadataJsonImporter();

			case FormatterFormat::XML_FORMAT;
				return new MetadataXMLImporter();

			case FormatterFormat::YAML_FORMAT:
				return new MetadataYamlImporter();
		}
	}
}