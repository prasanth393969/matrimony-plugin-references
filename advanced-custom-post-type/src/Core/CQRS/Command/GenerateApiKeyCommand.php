<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\ApiKey\ApiKeyModel;
use ACPT\Core\Repository\ApiRepository;

class GenerateApiKeyCommand implements CommandInterface
{
	/**
	 * @var
	 */
	private $uid;

	/**
	 * GenerateApiKeyCommand constructor.
	 *
	 * @param $uid
	 */
	public function __construct($uid)
	{
		$this->uid = $uid;
	}

	/**
	 * @return ApiKeyModel|mixed
	 * @throws \Exception
	 */
	public function execute()
	{
		$apiKey = Strings::randomString();
		$apiSecret = Strings::randomString();
		$now = new \DateTime();

		$apiKeyModel = ApiKeyModel::hydrateFromArray([
			'uid' => $this->uid,
			'key' => $apiKey,
			'secret' => $apiSecret,
			'createdAt' => $now,
		]);

		ApiRepository::save($apiKeyModel);

		return $apiKeyModel;
	}
}