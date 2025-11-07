<?php

use ACPT\Core\CQRS\Command\DeleteMetaGroupCommand;
use ACPT\Core\CQRS\Command\SaveMetaGroupCommand;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Utils\Data\Normalizer;
use ACPT\Utils\PHP\Arrays;

if( !function_exists('save_acpt_meta_group') )
{
	/**
	 * @param array $args
	 *
	 * @return bool
	 */
	function save_acpt_meta_group(array $args)
	{
		try {
			$command = new SaveMetaGroupCommand($args);
			$command->execute();

			return true;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('delete_acpt_meta_group') )
{
	/**
	 * @param $groupName
	 *
	 * @return bool
	 */
	function delete_acpt_meta_group($groupName)
	{
		try {
			$group = MetaRepository::get([
				'groupName' => $groupName
			]);

			if(count($group) !== 1){
				return false;
			}

			$command = new DeleteMetaGroupCommand($group[0]->getId());
			$command->execute();

			return true;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('get_acpt_meta_group_objects') )
{
    /**
     * @param array $args
     * @return array|null
     */
	function get_acpt_meta_group_objects($args = [])
	{
		try {
			$groups = MetaRepository::get($args);

			if(empty($groups)){
				return [];
			}

			$groupObjects = [];

			foreach ($groups as $group){
				$groupObjects[] = $group->toStdObject();
			}

			return $groupObjects;
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return [];
		}
	}
}

if( !function_exists('get_acpt_meta_group_object') )
{
	/**
	 * @param $groupName
	 *
	 * @return mixed|null
	 */
	function get_acpt_meta_group_object($groupName)
	{
		try {
			$group = MetaRepository::get([
				'groupName' => $groupName,
			]);

			if(count($group) !== 1){
				return null;
			}

			return $group[0]->toStdObject();
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}

if( !function_exists('save_acpt_meta_box') )
{
	/**
	 * Add or update meta box settings. This function replaces:
	 *
	 * - add_acpt_meta_box
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	function save_acpt_meta_box(array $args)
	{
		try {
			$group = MetaRepository::get([
				'groupName' => @$args['groupName']
			]);

			if(count($group) !== 1){
				return false;
			}

			$box = [
				'name' => $args['name'],
				'new_name' => isset($args['new_name']) ? $args['new_name'] : null,
				'label' => isset($args['label']) ? $args['label'] : null,
				'fields' => isset($args['fields']) ? $args['fields'] : [],
			];

			$data = $group[0]->toArray();
			$foundIndex = Arrays::findIndex($data['boxes'], 'name', $args['name']);

			if($foundIndex !== false){
				$data['boxes'][$foundIndex] = $box;
			} else {
				$data['boxes'][] = $box;
			}

			$command = new SaveMetaGroupCommand($data);
			$command->execute();

			return true;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists( 'delete_acpt_meta_box' ) )
{
	/**
	 * @param $groupName
	 * @param $boxName
	 *
	 * @return bool
	 */
	function delete_acpt_meta_box($groupName, $boxName)
	{
		try {
			$group = MetaRepository::get([
				'groupName' => $groupName
			]);

			if(count($group) !== 1){
				return false;
			}

			$data = $group[0]->toArray();

			foreach ($data['boxes'] as $boxIndex => $box){
				if($box['name'] === $boxName){
					unset($data['boxes'][$boxIndex]);
				}
			}

			$command = new SaveMetaGroupCommand($data);
			$command->execute();

			return true;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists( 'get_acpt_box_object' ) )
{
	/**
	 * @param $boxName
	 *
	 * @return array|null
	 */
	function get_acpt_box_object($boxName)
	{
		try {
			$box = MetaRepository::getMetaBoxByName($boxName);

			if($box === null){
				return null;
			}

			return $box->toStdObject();
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}

if( !function_exists('save_acpt_meta_field') )
{
	/**
	 * Add or update meta field settings. This function replaces:
	 *
	 * - add_acpt_meta_field
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	function save_acpt_meta_field(array $args)
	{
		try {
			$groupName = $args['groupName'] ?? $args['group_name'];
			$boxName = $args['boxName'] ?? $args['box_name'];

			$group = MetaRepository::get([
				'groupName' => @$groupName
			]);

			if(count($group) !== 1){
				return false;
			}

			$field = $args;
			unset($field['groupName']);
			unset($field['group_name']);
			unset($field['boxName']);
			unset($field['box_name']);

			$data = $group[0]->toArray();
			$foundBoxIndex = Arrays::findIndex($data['boxes'], 'name', $boxName);

			if($foundBoxIndex === false){
				return false;
			}

			if(
				isset($args['parentName']) or
				isset($args['parent_name'])
			){
				$parentFieldName = (isset($args['parentName'])) ? $args['parentName'] : $args['parent_name'];
				$parentFieldModel = MetaRepository::getMetaFieldByName([
					'boxName' => @$boxName,
					'fieldName' => $parentFieldName,
				]);

				$field['parentId'] = $parentFieldModel->getId();
				unset($field['parentName']);
			}

			if(
				isset($args['newName']) or
				isset($args['new_name'])
			){
				$field['name'] = isset($args['newName']) ? $args['newName'] : $args['new_name'];
				unset($field['newName']);
				unset($field['new_name']);
			}

			$foundFieldIndex = Arrays::findIndex($data['boxes'][$foundBoxIndex], 'name', $args['name']);

			if($foundFieldIndex !== false){
				$data['boxes'][$foundBoxIndex]['fields'][$foundFieldIndex] = $field;
			} else {
				$data['boxes'][$foundBoxIndex]['fields'][] = $field;
			}

			$command = new SaveMetaGroupCommand($data);
			$groupId = $command->execute();

			return is_string($groupId);

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('delete_acpt_meta_field') )
{
	function delete_acpt_meta_field($groupName, $boxName, $fieldName)
	{
		try {
			$group = MetaRepository::get([
				'groupName' => $groupName
			]);

			if(count($group) !== 1){
				return false;
			}

			$groupModel = $group[0];
			foreach ($groupModel->getBoxes() as $index => $boxModel){
				$boxModel->removeAField($fieldName);
			}

			$data = $groupModel->toArray();

			$command = new SaveMetaGroupCommand($data);
			$command->execute();

			return true;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('get_acpt_meta_field_object') )
{
	function get_acpt_meta_field_object($boxName, $fieldName)
	{
		try {
			$field = MetaRepository::getMetaFieldByName([
				'boxName' => $boxName,
				'fieldName' => $fieldName,
			]);

			if($field === null){
				return null;
			}

			return $field->toStdObject();
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}

if( !function_exists('get_acpt_meta_field_objects') )
{
	function get_acpt_meta_field_objects($belongsTo, $clonedFields = false, $find = null)
	{
		$fields = [];

		try {
			$args = [
				'belongsTo' => $belongsTo,
			];

			if($find !== null){
				$args['find'] = $find;
			}

			if($clonedFields === true){
                $args['clonedFields'] = true;
            }

			$groups = MetaRepository::get($args);

			foreach ($groups as $group){
				foreach ($group->getBoxes() as $box){
					foreach ($box->getFields() as $field){
						$field->setBelongsToLabel($belongsTo);
						$field->setFindLabel($find);
						$fields[] = $field->toStdObject();
					}
				}
			}

			return $fields;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return [];
		}
	}
}

if( !function_exists('get_acpt_meta_field_before_and_after') )
{
	function get_acpt_meta_field_before_and_after($box_name, $field_name)
	{
		$before = null;
		$after = null;
		$field_object = get_acpt_meta_field_object($box_name, $field_name);

		if($field_object === null){
			return [
				'before' => $before,
				'after' => $after,
			];
		}

		$field_object_array = Normalizer::objectToArray($field_object);

		foreach ($field_object_array['advancedOptions'] as $index => $advancedOption){
			if($advancedOption['key'] === 'after'){
				$after = $advancedOption['value'];
			}

			if($advancedOption['key'] === 'before'){
				$before = $advancedOption['value'];
			}
		}

		return [
			'before' => $before,
			'after' => $after,
		];
	}
}




