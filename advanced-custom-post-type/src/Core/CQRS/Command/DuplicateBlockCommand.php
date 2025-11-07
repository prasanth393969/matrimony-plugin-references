<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Repository\DynamicBlockRepository;

class DuplicateBlockCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * DuplicateFormCommand constructor.
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
        $blockModel = DynamicBlockRepository::getById($this->id);

        if(empty($blockModel)){
            throw new \Exception("Block not found");
        }

        $newBlockModel = $blockModel->duplicate();

        DynamicBlockRepository::save($newBlockModel);
    }
}