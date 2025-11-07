<?php

namespace ACPT\Integrations\WPML\Provider;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\CustomPostType\CustomPostTypeModel;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Models\Taxonomy\TaxonomyModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Integrations\WPML\Constants\WPMLConstants;
use ACPT\Integrations\WPML\Helper\WPMLConfig;
use ACPT\Utils\Data\Meta;

class MetaFieldsProvider
{
	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private array $fields = [];
	/**
	 * @var bool
	 */
	private bool $resetDefault = false;

	/**
	 * @param bool $resetDefault
	 *
	 * @return MetaFieldsProvider
	 */
	public static function getInstance($resetDefault = false)
	{
		if(
			(isset(self::$instance->resetDefault) and self::$instance->resetDefault !== $resetDefault) or
			self::$instance == null
		){
			self::$instance = new MetaFieldsProvider($resetDefault);
			self::$instance->setFields();
		}

		return self::$instance;
	}

	/**
	 * MetaFieldsProvider constructor.
	 *
	 * @param bool $resetDefault
	 */
	private function __construct($resetDefault = false)
	{
		$this->resetDefault = $resetDefault;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	public function setFields()
	{
		try {
			$this->fields = [
				'custom-types' => $this->postTypes(),
				'taxonomies' => $this->taxonomies(),
				'custom-fields' => $this->postTypeFields(),
				'custom-term-fields' => $this->taxonomyFields(),
				'admin-texts' => $this->optionPageFields(),
			];
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			$this->fields = [];
		}
	}

    /**
     * @return CustomPostTypeModel[]
     * @throws \Exception
     */
    private function postTypes()
    {
        $postTypes = [];

        foreach (CustomPostTypeRepository::get([]) as $postTypeModel){
            if(!$postTypeModel->isNative()){

                if($this->resetDefault === false){
                    $settings = Meta::fetch(WPMLConfig::cacheFieldKey($postTypeModel->getId()), MetaTypes::OPTION_PAGE, WPMLConfig::cacheFieldKey($postTypeModel->getId()));

                    if(!empty($settings) and isset($settings['translate'])){
                        $translate = $settings['translate'];
                    }

                    if(!empty($settings) and isset($settings['display_as_translated'])){
                        $display_as_translated = $settings['display_as_translated'];
                    }

                    if(!empty($settings) and isset($settings['automatic'])){
                        $automatic = $settings['automatic'];
                    }
                }

                $postTypes[$postTypeModel->getName()] = [
                    'id' => $postTypeModel->getId(),
                    'name' => $postTypeModel->getName(),
                    'translate' => $translate ?? 1,
                    'display_as_translated' => $display_as_translated ?? 1,
                    'automatic' => $automatic ?? 1,
                ];
            }
        }

        return $postTypes;
    }

    /**
     * @return TaxonomyModel[]
     * @throws \Exception
     */
    private function taxonomies()
    {
        $taxonomies = [];

        foreach (TaxonomyRepository::get([]) as $taxonomyModel){
            if(!$taxonomyModel->isNative()){

                if($this->resetDefault === false){
                    $settings = Meta::fetch(WPMLConfig::cacheFieldKey($taxonomyModel->getId()), MetaTypes::OPTION_PAGE, WPMLConfig::cacheFieldKey($taxonomyModel->getId()));

                    if(!empty($settings) and isset($settings['translate'])){
                        $translate = $settings['translate'];
                    }

                    if(!empty($settings) and isset($settings['display_as_translated'])){
                        $display_as_translated = $settings['display_as_translated'];
                    }
                }

                $taxonomies[$taxonomyModel->getSlug()] = [
                    'id' => $taxonomyModel->getId(),
                    'slug' => $taxonomyModel->getSlug(),
                    'translate' => $translate ?? 1,
                    'display_as_translated' => $display_as_translated ?? 1,
                ];
            }
        }

        return $taxonomies;
    }

	/**
	 * @return array
	 * @throws \Exception
	 */
	private function postTypeFields()
	{
		$metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE
		]);

		return $this->formatFields($metaGroups, MetaTypes::CUSTOM_POST_TYPE);
	}

	/**
	 * @param bool $resetDefault
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function taxonomyFields($resetDefault = false)
	{
		$metaGroups = MetaRepository::get([
			'belongsTo' => MetaTypes::TAXONOMY
		]);

		return $this->formatFields($metaGroups, MetaTypes::TAXONOMY);
	}

	/**
	 * @param bool $resetDefault
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function optionPageFields($resetDefault = false)
	{
		$fields = [];
		$pages = OptionPageRepository::get([]);

		foreach ($pages as $page){
			$metaGroups = MetaRepository::get([
				'belongsTo' => MetaTypes::OPTION_PAGE,
				'find' => $page->getMenuSlug()
			]);

			$fields = array_merge($fields, $this->formatFields($metaGroups, MetaTypes::OPTION_PAGE, $page->getMenuSlug()));

			foreach ($page->getChildren() as $child){
				$metaGroups = MetaRepository::get([
					'belongsTo' => MetaTypes::OPTION_PAGE,
					'find' => $child->getMenuSlug()
				]);

				$fields = array_merge($fields, $this->formatFields($metaGroups, MetaTypes::OPTION_PAGE, $child->getMenuSlug()));
			}
		}

		return $fields;
	}

	/**
	 * @param MetaGroupModel[] $metaGroups
	 * @param $belongsTo
	 * @param null $find
	 * @param bool $resetDefault
	 *
	 * @return array
	 */
	private function formatFields(array $metaGroups, $belongsTo, $find = null)
	{
		$fields = [];

		foreach ($metaGroups as $metaGroup){
			foreach ($metaGroup->getBoxes() as $box){
				foreach ($box->getFields() as $field){
					$field->setBelongsToLabel($belongsTo);
					$field->setFindLabel($find);
					$fields[$field->getDbName()] = $this->formatField($field);

					// Add label for URL and File fields
                    if($field->getType() === MetaFieldModel::FILE_TYPE or $field->getType() === MetaFieldModel::URL_TYPE){
                        $fields[$field->getDbName()."_label"] = [
                            'id' => $field->getId()."_label",
                            'type' => MetaFieldModel::TEXT_TYPE,
                            'slug' => $field->getDbName()."_label",
                            'action' => WPMLConstants::ACTION_TRANSLATE,
                            'style' => WPMLConstants::TYPE_LINE,
                            'label' => $field->getUiName()."_label"
                        ];
                    }
				}
			}
		}

		return $fields;
	}

	/**
	 * @param MetaFieldModel $fieldModel
	 *
	 * @return array
	 */
	private function formatField(MetaFieldModel $fieldModel)
	{
		switch ($fieldModel->getType()){

			case MetaFieldModel::TEXT_TYPE:
				$action = WPMLConstants::ACTION_TRANSLATE;
				$style = WPMLConstants::TYPE_LINE;
				break;

			case MetaFieldModel::EDITOR_TYPE:
			case MetaFieldModel::TEXTAREA_TYPE:
				$action = WPMLConstants::ACTION_TRANSLATE;
				$style = WPMLConstants::TYPE_AREA;
				break;

            case MetaFieldModel::IMAGE_TYPE:
            case MetaFieldModel::VIDEO_TYPE:
            case MetaFieldModel::AUDIO_TYPE:
            case MetaFieldModel::AUDIO_MULTI_TYPE:
            case MetaFieldModel::FILE_TYPE:
            case MetaFieldModel::GALLERY_TYPE:
                $action = WPMLConstants::ACTION_IGNORE;
                $style = WPMLConstants::TYPE_LINE;
                break;

			default:
				$action = WPMLConstants::ACTION_COPY;
				$style = WPMLConstants::TYPE_LINE;
		}

		if($this->resetDefault === false){
			$settings = Meta::fetch(WPMLConfig::cacheFieldKey($fieldModel->getId()), MetaTypes::OPTION_PAGE, WPMLConfig::cacheFieldKey($fieldModel->getId()));

			if(!empty($settings) and isset($settings['action'])){
				$action = $settings['action'];
			}

			if(!empty($settings) and isset($settings['style'])){
				$style = $settings['style'];
			}
		}

		return [
			'id' => $fieldModel->getId(),
			'type' => $fieldModel->getType(),
			'slug' => $fieldModel->getDbName(),
			'action' => $action,
			'style' => $style,
			'label' => $fieldModel->getUiName()
		];
	}
}