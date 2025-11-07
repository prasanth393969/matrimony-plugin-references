<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Validators\ArgumentsArrayValidator;
use ACPT\Includes\ACPT_DB;

class CopyMetaBoxCommand extends AbstractCopyCommand implements CommandInterface
{
	/**
	 * @var string
	 */
	private $boxId;

	/**
	 * @var
	 */
	private $targetGroupId;

	/**
	 * @var bool
	 */
	private $delete;

	/**
	 * CopyMetaBoxCommand constructor.
	 *
	 * @param $data
	 */
	public function __construct($data)
	{
		parent::__construct();
		$validationRules = [
			'boxId' => [
				'required' => true,
				'type' => 'string',
			],
			'targetGroupId' => [
				'required' => true,
				'type' => 'string',
			],
			'delete' => [
				'required' => false,
				'type' => 'boolean',
			],
		];

		$validator = new ArgumentsArrayValidator();

		if(!$validator->validate($validationRules, $data)){
			throw new \InvalidArgumentException($validator->errorMessage());
		}

		$this->boxId = $data['boxId'];
		$this->targetGroupId = $data['targetGroupId'];
		$this->delete = $data['delete'] ? $data['delete'] : null;
	}

	/**
	 * @return mixed|void
	 * @throws \Exception
	 */
	public function execute()
	{
		$targetMetaGroup = MetaRepository::get([
			'id' => $this->targetGroupId
		]);

		if(empty($targetMetaGroup)){
			throw new \Exception("Group id not found");
		}

		$metaBox = MetaRepository::getMetaBoxById($this->boxId);

		if(empty($metaBox)){
			throw new \Exception("Box id not found");
		}

		$duplicatedMetaBox = $this->copyBox($metaBox, $targetMetaGroup[0]);

		ACPT_DB::startTransaction();

		MetaRepository::saveMetaBox($duplicatedMetaBox);

		if($this->delete){
			MetaRepository::deleteMetaBoxById([
				'id' => $this->boxId,
			]);
		}

		ACPT_DB::commitTransaction();
	}
}