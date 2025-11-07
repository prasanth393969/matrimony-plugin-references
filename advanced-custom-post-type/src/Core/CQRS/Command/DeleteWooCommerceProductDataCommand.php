<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Repository\WooCommerceProductDataRepository;

class DeleteWooCommerceProductDataCommand implements CommandInterface
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * DeleteTemplateCommand constructor.
	 *
	 * @param $id
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function execute()
	{
		WooCommerceProductDataRepository::delete($this->id);
	}
}