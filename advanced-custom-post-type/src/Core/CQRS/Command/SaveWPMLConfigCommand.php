<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Integrations\WPML\Helper\WPMLConfig;
use ACPT\Integrations\WPML\Provider\MetaFieldsProvider;

class SaveWPMLConfigCommand implements CommandInterface
{
	/**
	 * @var array
	 */
    private array $data = [];

	/**
	 * SaveWPMLConfigCommand constructor.
	 *
	 * @param array $data
	 */
	public function __construct($data)
	{
		$this->data = $data;
	}

	/**
	 * @inheritDoc
	 */
	public function execute()
	{
		$data = (isset($this->data['resetDefault']) and $this->data['resetDefault'] === true) ? MetaFieldsProvider::getInstance(true)->getFields() : $this->data;

		WPMLConfig::generate($data);
	}
}