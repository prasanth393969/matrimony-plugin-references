<?php

namespace ACPT\Integrations\Breakdance\Provider;

use ACPT\Core\Models\Meta\MetaFieldBlockModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Traits\CollectionsTrait;
use ACPT\Integrations\Breakdance\Constants\BreakdanceField;
use ACPT\Integrations\Breakdance\Provider\Blocks\ACPTBlock;
use ACPT\Integrations\Breakdance\Provider\Fields\ACPTField;
use ACPT\Integrations\Breakdance\Provider\Fields\ACPTFieldInterface;

class BreakdanceProvider
{
	/**
	 * Register ACPT fields
	 *
	 * @see https://github.com/soflyy/breakdance-sample-dynamic-data/tree/master
	 *
	 * @throws \Exception
	 */
	public static function init()
	{
		// fetch settings
		$settings = self::fetchSettings();
		$fields = $settings['fields'];
 		$blocks = $settings['blocks'];
 		$slugs = [];

		foreach ($fields as $field){
		    /** @var MetaFieldModel $f */
            foreach ($field['fields'] as $f){

                // get the count of fields with the same label. In case of collision,
                // the string _{count} will be appended
                $slug = ACPTField::label($f);
                $slugs[$slug] = (isset($slugs[$slug])) ? ($slugs[$slug]+1) : 1;

                $breakdanceField = self::getBreakdanceField($f, $field['belongsTo'], $field['find'], $slugs[$slug]);
                $breakdanceFieldAsUrl = self::getBreakdanceFieldAsUrl($f, $field['belongsTo'], $field['find'], $slugs[$slug]);

                if($breakdanceField !== null){
                    \Breakdance\DynamicData\registerField($breakdanceField);
                }

                if($breakdanceFieldAsUrl !== null){
                    \Breakdance\DynamicData\registerField($breakdanceFieldAsUrl);
                }
            }
		}

		foreach ($blocks as $block){
            foreach ($block['blocks'] as $b){
                $breakdanceField = self::getBreakdanceBlock($b, $field['belongsTo'], $field['find']);

                if($breakdanceField !== null){
                    \Breakdance\DynamicData\registerField($breakdanceField);
                }
            }

		}
	}

	/**
	 * @return MetaFieldModel[]
	 * @throws \Exception
	 */
	private static function fetchSettings()
	{
		$fields = [];
		$blocks = [];

		$fieldGroups = MetaRepository::get([
		    'clonedFields' => true
        ]);

		foreach ($fieldGroups as $fieldGroup){
			if(count($fieldGroup->getBelongs()) > 0){
				foreach ($fieldGroup->getBelongs() as $belong){

					$belongsTo = $belong->getBelongsTo();
					$find = $belong->getFindAsSting();

					$fieldsAndBlocks = self::getFieldsAndBlocks($fieldGroup, $belongsTo, $find);

                    $fields[] = [
                        'belongsTo' => $belongsTo,
                        'find' => $find,
                        'fields' => $fieldsAndBlocks['fields'],
                    ];

                    $blocks[] = [
                        'belongsTo' => $belongsTo,
                        'find' => $find,
                        'blocks' => $fieldsAndBlocks['blocks'],
                    ];
				}
			}
		}
		
		return [
			'blocks' => $blocks,
			'fields' => $fields,
		];
	}

	/**
	 * @param MetaGroupModel $metaGroup
	 * @param $belongsTo
	 * @param $find
	 *
	 * @return array
	 */
	private static function getFieldsAndBlocks(MetaGroupModel $metaGroup, $belongsTo, $find)
	{
		$fields = [];
		$blocks = [];

		foreach ($metaGroup->getBoxes() as $box) {
			foreach ($box->getFields() as $field){

				$field->setBelongsToLabel($belongsTo);
				$field->setFindLabel($find);

				// Exclude the Flexible fields, allow only the blocks
				if($field->getType() !== MetaFieldModel::FLEXIBLE_CONTENT_TYPE){
					if(!self::existsInCollection($field->getId(), $fields)){
						$fields[] = $field;
					}
				}

				// CLONE


				// REPEATER
				if($field->getType() === MetaFieldModel::REPEATER_TYPE and $field->hasChildren()){
					foreach ($field->getChildren() as $childField){
						$childField->setBelongsToLabel($belongsTo);
						$childField->setFindLabel($find);

						if(!self::existsInCollection($childField->getId(), $fields)){
							$fields[] = $childField;
						}
					}
				}

				// FLEXIBLE
				if($field->getType() === MetaFieldModel::FLEXIBLE_CONTENT_TYPE and $field->hasBlocks()){
					foreach ($field->getBlocks() as $blockModel){
						foreach ($blockModel->getFields() as $nestedField){
							$nestedField->setBelongsToLabel($belongsTo);
							$nestedField->setFindLabel($find);

							if(!self::existsInCollection($nestedField->getId(), $fields)){
								$fields[] = $nestedField;
							}
						}

						if(!self::existsInCollection($blockModel->getId(), $blocks)){
							$blocks[] = $blockModel;
						}
					}
				}
			}
		}

		return [
			'blocks' => $blocks,
			'fields' => $fields,
		];
	}

	/**
	 * @param $id
	 * @param $collection
	 *
	 * @return bool
	 */
	private static function existsInCollection($id, $collection): bool
	{
		return ( new class { use CollectionsTrait; })->existsInCollection($id, $collection);
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 * @param null $belongsTo
	 * @param null $find
	 * @param int $count
	 *
	 * @return null|ACPTFieldInterface
	 */
	private static function getBreakdanceField(MetaFieldModel $fieldModel, $belongsTo = null, $find = null, $count = 1)
	{
		$fieldType = null;

		switch ($fieldModel->getType()){

            case MetaFieldModel::BARCODE_TYPE:
                $fieldType = BreakdanceField::BARCODE;
                break;

			case MetaFieldModel::COUNTRY_TYPE:
				$fieldType = BreakdanceField::COUNTRY;
				break;

			case MetaFieldModel::CURRENCY_TYPE:
				$fieldType = BreakdanceField::CURRENCY;
				break;

			case MetaFieldModel::DATE_TYPE:
				$fieldType = BreakdanceField::DATE;
				break;

			case MetaFieldModel::DATE_TIME_TYPE:
				$fieldType = BreakdanceField::DATE_TIME;
				break;

			case MetaFieldModel::DATE_RANGE_TYPE:
				$fieldType = BreakdanceField::DATE_RANGE;
				break;

			case MetaFieldModel::EMAIL_TYPE:
				$fieldType = BreakdanceField::EMAIL;
				break;

			case MetaFieldModel::EMBED_TYPE:
				$fieldType = BreakdanceField::OEMBED;
				break;

			case MetaFieldModel::FILE_TYPE:
				$fieldType = BreakdanceField::FILE;
				break;

			case MetaFieldModel::FLEXIBLE_CONTENT_TYPE:
			case MetaFieldModel::REPEATER_TYPE:
				$fieldType = BreakdanceField::REPEATER;
				break;

			case MetaFieldModel::IMAGE_SLIDER_TYPE:
			case MetaFieldModel::GALLERY_TYPE:
				$fieldType = BreakdanceField::GALLERY;
				break;

			case MetaFieldModel::ICON_TYPE:
				$fieldType = BreakdanceField::ICON;
				break;

			case MetaFieldModel::IMAGE_TYPE:
				$fieldType = BreakdanceField::IMAGE;
				break;

			case MetaFieldModel::LENGTH_TYPE:
				$fieldType = BreakdanceField::LENGTH;
				break;

			case MetaFieldModel::NUMBER_TYPE:
			case MetaFieldModel::RANGE_TYPE:
				$fieldType = BreakdanceField::NUMBER;
				break;

			case MetaFieldModel::PHONE_TYPE:
				$fieldType = BreakdanceField::PHONE;
				break;

            case MetaFieldModel::QR_CODE_TYPE:
                $fieldType = BreakdanceField::QR_CODE;
                break;

			case MetaFieldModel::RATING_TYPE:
				$fieldType = BreakdanceField::RATING;
				break;

			case MetaFieldModel::TABLE_TYPE:
				$fieldType = BreakdanceField::TABLE;
				break;

			case MetaFieldModel::TEXTAREA_TYPE:
				$fieldType = BreakdanceField::TEXTAREA;
				break;

			case MetaFieldModel::TIME_TYPE:
				$fieldType = BreakdanceField::TIME;
				break;

			case MetaFieldModel::URL_TYPE:
				$fieldType = BreakdanceField::URL;
				break;

			case MetaFieldModel::VIDEO_TYPE:
				$fieldType = BreakdanceField::VIDEO;
				break;

			case MetaFieldModel::WEIGHT_TYPE:
				$fieldType = BreakdanceField::WEIGHT;
				break;

			case MetaFieldModel::RADIO_TYPE:
			case MetaFieldModel::SELECT_TYPE:
				$fieldType = BreakdanceField::LABEL_VALUE;
				break;

            case MetaFieldModel::LIST_TYPE:
			case MetaFieldModel::CHECKBOX_TYPE:
			case MetaFieldModel::SELECT_MULTI_TYPE:
				$fieldType = BreakdanceField::LIST;
				break;

			default:
				$fieldType = BreakdanceField::STRING;
				break;
		}

		$className = 'ACPT\\Integrations\\Breakdance\\Provider\\Fields\\ACPT'.$fieldType.'Field';

		if(class_exists($className)){
			return new $className($fieldModel, $belongsTo, $find, $count);
		}

		return null;
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 * @param null $belongsTo
	 * @param null $find
	 * @param int $count
     *
	 * @return null|ACPTFieldInterface
	 */
	private static function getBreakdanceFieldAsUrl(MetaFieldModel $fieldModel, $belongsTo = null, $find = null, $count = 1)
	{
		$fieldType = null;

		switch ($fieldModel->getType()){

			case MetaFieldModel::EMAIL_TYPE:
				$fieldType = BreakdanceField::EMAIL;
				break;

			case MetaFieldModel::FILE_TYPE:
				$fieldType = BreakdanceField::FILE;
				break;

			case MetaFieldModel::IMAGE_TYPE:
				$fieldType = BreakdanceField::IMAGE;
				break;

			case MetaFieldModel::PHONE_TYPE:
				$fieldType = BreakdanceField::PHONE;
				break;

            case MetaFieldModel::QR_CODE_TYPE:
                $fieldType = BreakdanceField::QR_CODE;
                break;

			case MetaFieldModel::URL_TYPE:
				$fieldType = BreakdanceField::URL;
				break;

			case MetaFieldModel::VIDEO_TYPE:
				$fieldType = BreakdanceField::VIDEO;
				break;
		}

		$className = 'ACPT\\Integrations\\Breakdance\\Provider\\Fields\\ACPT'.$fieldType.'AsUrlField';

		if(class_exists($className)){
			return new $className($fieldModel, $belongsTo, $find, $count);
		}

		return null;
	}

    /**
     * @param MetaFieldBlockModel $blockModel
     * @param null $belongsTo
     * @param null $find
     * @return ACPTBlock
     */
	private static function getBreakdanceBlock(MetaFieldBlockModel $blockModel, $belongsTo = null, $find = null)
	{
		return new ACPTBlock($blockModel, $belongsTo, $find);
	}
}