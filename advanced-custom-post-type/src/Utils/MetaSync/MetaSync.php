<?php

namespace ACPT\Utils\MetaSync;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaBoxModel;
use ACPT\Core\Models\Meta\MetaFieldModel;

class MetaSync
{
	/**
	 * @param MetaBoxModel $metaBoxModel
	 *
	 * @throws \Exception
	 */
	public static function syncBox(MetaBoxModel $metaBoxModel)
	{
		$group = $metaBoxModel->getGroup();

		foreach ($group->getBelongs() as $belong){
			switch ($belong->getBelongsTo()){
				case MetaTypes::CUSTOM_POST_TYPE:
					PostMetaSync::syncBox($metaBoxModel, $belong->getFind());
					break;

				case MetaTypes::TAXONOMY:
					TaxonomyMetaSync::syncBox($metaBoxModel, $belong->getFind());
					break;

				case MetaTypes::OPTION_PAGE:
					OptionPageMetaSync::syncBox($metaBoxModel, $belong->getFind());
					break;

				case MetaTypes::USER:
					UserMetaSync::syncBox($metaBoxModel);
					break;
			}
		}
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 *
	 * @throws \Exception
	 */
	public static function syncField(MetaFieldModel $fieldModel)
	{
		$group = $fieldModel->getBox()->getGroup();

		foreach ($group->getBelongs() as $belong){
			switch ($belong->getBelongsTo()){
				case MetaTypes::CUSTOM_POST_TYPE:
					PostMetaSync::syncField($fieldModel, $belong->getFind());
					break;

				case MetaTypes::TAXONOMY:
					TaxonomyMetaSync::syncField($fieldModel, $belong->getFind());
					break;

				case MetaTypes::OPTION_PAGE:
					OptionPageMetaSync::syncField($fieldModel, $belong->getFind());
					break;

				case MetaTypes::USER:
					UserMetaSync::syncField($fieldModel);
					break;
			}
		}
	}
}