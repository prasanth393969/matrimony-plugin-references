<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Repository\DynamicBlockRepository;

class DeleteBlockCommand implements CommandInterface
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
		DynamicBlockRepository::delete($this->id);
	}
}