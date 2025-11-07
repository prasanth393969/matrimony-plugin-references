<?php

namespace ACPT\Core\CQRS\Command;

interface CommandInterface
{
	/**
	 * @return mixed
	 */
	public function execute();
}