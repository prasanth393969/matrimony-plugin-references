<?php

namespace ACPT\Core\CQRS\Query;

interface QueryInterface
{
	/**
	 * @return mixed
	 */
	public function execute();
}