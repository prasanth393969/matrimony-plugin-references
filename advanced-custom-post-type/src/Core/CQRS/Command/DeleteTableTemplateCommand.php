<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Includes\ACPT_DB;

class DeleteTableTemplateCommand implements CommandInterface
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * DeleteMetaGroupCommand constructor.
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
		global $wpdb;

		$query = "DELETE FROM `{$wpdb->prefix}options` where option_id = %d;";
		ACPT_DB::executeQueryOrThrowException($query, [$this->id]);
	}
}