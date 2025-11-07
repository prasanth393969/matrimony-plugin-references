<?php

use ACPT\Core\CQRS\Command\DeleteFormCommand;
use ACPT\Core\CQRS\Command\SaveFormCommand;
use ACPT\Core\CQRS\Command\SaveFormFieldsCommand;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Repository\FormRepository;

if( !function_exists('save_acpt_form') )
{
	function save_acpt_form(array $args)
	{
		if(!isset($args['name'])){
			return false;
		}

		try {
			if(!isset($args['id'])){
				$form = FormRepository::get([
					'formName' => $args['name']
				]);

				if(count($form) === 1){
					$args['id'] = $form[0]->getId();
				}
			}

			$command = new SaveFormCommand($args);
			$command->execute();

			return true;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('delete_acpt_form') )
{
	function delete_acpt_form($formName)
	{
		try {
			$form = FormRepository::get([
				'formName' => $formName
			]);

			if(count($form) !== 1){
				return false;
			}

			$command = new DeleteFormCommand($form[0]->getId());
			$command->execute();

			return true;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('get_acpt_form_object') )
{
	function get_acpt_form_object($formName)
	{
		try {
			$form = FormRepository::get([
				'formName' => $formName,
			]);

			if(count($form) !== 1){
				return null;
			}

			return $form[0]->toStdObject();
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}

if( !function_exists('save_acpt_form_field') )
{
	function save_acpt_form_field(array $args)
	{
		if(!isset($args['formName']) and !isset($args['form_name'])){
			return false;
		}

		try {
			$form = FormRepository::get([
				'formName' => $args['formName'] ?? $args['form_name'],
			]);

			if(count($form) !== 1){
				return false;
			}

			$formModel = $form[0];
			$fieldModel = $formModel->getAField($args['name']);
			$newId = ($fieldModel !== null) ? $fieldModel->getId() : Uuid::v4();
			$isRequired = $args['isRequired'] ?? $args['is_required'];

			$fieldPayload = [
				'key' => @$args['key'],
				'id' => $newId,
				'metaFieldId' => @$args['metaFieldId'],
				'group' => @$args['group'],
				'name' => @$args['name'],
				'label' => @$args['label'],
				'type' => @$args['type'],
				'description' => @$args['description'],
				'isRequired' => (bool)$isRequired,
				'extra' => @$args['extra'],
				'settings' => @$args['settings'],
			];

			$command = new SaveFormFieldsCommand($formModel->getId(), [$fieldPayload]);
			$command->execute();

			return true;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('delete_acpt_form_field') )
{
	function delete_acpt_form_field(array $args)
	{
		if(!isset($args['formName'])){
			return false;
		}

		if(!isset($args['fieldName'])){
			return false;
		}

		try {
			$form = FormRepository::get([
				'formName' => $args['formName'],
			]);

			if(count($form) !== 1){
				return false;
			}

			$formModel = $form[0];
			$formModel->removeAField($args['fieldName']);

			$data = $formModel->toArray();

			$command = new SaveFormCommand($data, true);
			$command->execute();

			return true;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('get_acpt_form_field_object') )
{
	function get_acpt_form_field_object(array $args)
	{
		if(!isset($args['formName'])){
			return false;
		}

		if(!isset($args['fieldName'])){
			return false;
		}

		try {
			$form = FormRepository::get([
				'formName' => $args['formName'],
			]);

			if(count($form) !== 1){
				return null;
			}

			$formModel = $form[0];
			$fieldModel = $formModel->getAField($args['fieldName']);

			if($fieldModel === null){
				return null;
			}

			return $fieldModel->toStdObject();

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}
