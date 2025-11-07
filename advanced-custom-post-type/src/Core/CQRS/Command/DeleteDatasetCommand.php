<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Repository\DatasetRepository;

class DeleteDatasetCommand implements CommandInterface
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * DeleteFormCommand constructor.
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
		DatasetRepository::delete($this->id);
	}
}