<?php

use ACPT\Core\CQRS\Command\DeleteBlockCommand;
use ACPT\Core\CQRS\Command\SaveBlockCommand;
use ACPT\Core\Repository\DynamicBlockRepository;

if( !function_exists('save_acpt_block') )
{
    function save_acpt_block($args = [])
    {
        if(!isset($args['name'])){
            return false;
        }

        try {
            if(!isset($args['id'])){
                $block = DynamicBlockRepository::getByName($args['name']);

                if(!empty($block)){
                    $args['id'] = $block->getId();
                }
            }

            $command = new SaveBlockCommand($args);
            $command->execute();

            return true;
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return false;
        }
    }
}

if( !function_exists('delete_acpt_block') )
{
    function delete_acpt_block($blockName)
    {
        try {
            $block = DynamicBlockRepository::getByName($blockName);

            if(empty($block)){
                return false;
            }

            $command = new DeleteBlockCommand($block->getId());
            $command->execute();

            return true;
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return false;
        }
    }
}

if( !function_exists('get_acpt_block_object') )
{
    function get_acpt_block_object($blockName)
    {
        try {
            $block = DynamicBlockRepository::getByName($blockName);

            if(empty($block)){
                return false;
            }

            return $block->toStdObject();
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return null;
        }
    }
}
