<?php

namespace ACPT\Core\Repository;

use ACPT\Core\Models\Permission\PermissionModel;
use ACPT\Includes\ACPT_DB;

class PermissionRepository extends AbstractRepository
{
	/**
	 * @param $id
	 *
	 * @throws \Exception
	 */
	public static function delete($id)
	{
		ACPT_DB::executeQueryOrThrowException("DELETE FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_PERMISSION)."` WHERE id = %s;", [$id]);
		ACPT_DB::invalidateCacheTag(self::class);
	}

	/**
	 * @param $entityId
	 *
	 * @throws \Exception
	 */
	public static function deleteByEntityId($entityId)
	{
		ACPT_DB::executeQueryOrThrowException("DELETE FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_PERMISSION)."` WHERE entity_id = %s;", [$entityId]);
		ACPT_DB::invalidateCacheTag(self::class);
	}

	/**
	 * @param PermissionModel $permission
	 *
	 * @throws \Exception
	 */
	public static function save(PermissionModel $permission)
	{
		$sql = "
            INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_PERMISSION)."` 
            (`id`,
            `entity_id` ,
            `user_role`,
            `permissions`,
            `sort`
            ) VALUES (
                %s,
                %s,
                %s,
                %s,
                %d
            ) ON DUPLICATE KEY UPDATE 
                `entity_id` = %s,
                `user_role` = %s,
                `permissions` = %s,
                `sort` = %d
        ;";

		ACPT_DB::executeQueryOrThrowException($sql, [
			$permission->getId(),
			$permission->getEntityId(),
			$permission->getUserRole(),
			serialize($permission->getPermissions()),
			$permission->getSort(),
			$permission->getEntityId(),
			$permission->getUserRole(),
			serialize($permission->getPermissions()),
			$permission->getSort(),
		]);
		ACPT_DB::invalidateCacheTag(self::class);
	}

	/**
	 * @param $entityId
	 *
	 * @return PermissionModel[]|array
	 */
	public static function getByEntityId($entityId)
	{
		$query = "SELECT
            p.id,
            p.entity_id,
            p.user_role,
            p.permissions,
            p.sort
        FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_PERMISSION)."` p
        WHERE entity_id = %s ORDER BY sort ASC";

		$permissions = ACPT_DB::getResults($query, [$entityId]);
		$permissionModels = [];

		foreach ($permissions as $permission){
			$permissionModels[] = self::hydrateModel($permission);
		}

		return $permissionModels;
	}

	/**
	 * @param $id
	 *
	 * @return PermissionModel|null
	 */
	public static function getById($id)
	{
		$query = "SELECT
            p.id,
            p.entity_id,
            p.user_role,
            p.permissions,
            p.sort
        FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_PERMISSION)."` p
        WHERE id = %s";

		$permissions = ACPT_DB::getResults($query, [$id]);

		if(count($permissions) === 1){
			return self::hydrateModel($permissions[0]);
		}

		return null;
	}

	/**
	 * @param $rawData
	 *
	 * @return PermissionModel|null
	 */
	private static function hydrateModel($rawData)
	{
		try {
			return new PermissionModel(
				$rawData->id,
				$rawData->entity_id,
				$rawData->user_role,
				unserialize($rawData->permissions),
				$rawData->sort
			);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return null;
		}
	}
}