<?php

namespace ACPT\Core\Helper;

use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;

class MetaFactory
{
	/**
	 * @return MetaGroupModel
	 */
	public static function createGroup(): MetaGroupModel
	{
		$name = Strings::randomString();

		return new MetaGroupModel(Uuid::v4(), $name);
	}

	/**
	 * @return MetaBoxModel
	 */
	public static function createBox(): MetaBoxModel
	{
		$name = Strings::randomString();
		$group = self::createGroup();

		return new MetaBoxModel(Uuid::v4(), $group, $name, 1);
	}

	/**
	 * @return MetaFieldModel
	 * @throws \ReflectionException
	 */
	public static function createField(): MetaFieldModel
	{
		$name = Strings::randomString();
		$box = self::createBox();

		return new MetaFieldModel(Uuid::v4(), $box, $name, MetaFieldModel::TEXT_TYPE, true, true, 11);
	}
}