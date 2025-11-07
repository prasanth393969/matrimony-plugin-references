<?php

namespace ACPT\Utils\ExportCode\Exporter;

use ACPT\Utils\ExportCode\DTO\ExportCodeStringsDto;

abstract class AbstractExportCodeStrings
{
	/**
	 * @param $find
	 *
	 * @return ExportCodeStringsDto
	 */
	public abstract function export($find);
}
