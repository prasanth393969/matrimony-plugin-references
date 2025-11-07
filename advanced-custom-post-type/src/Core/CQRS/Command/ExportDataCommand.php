<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\CustomPostType\CustomPostTypeModel;
use ACPT\Core\Models\Form\FormModel;
use ACPT\Core\Models\Meta\MetaGroupModel;
use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Models\Taxonomy\TaxonomyModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\FormRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Utils\Data\Formatter\Formatter;

class ExportDataCommand implements CommandInterface
{
	/**
	 * @var string
	 */
	private string $format;

	/**
	 * @var array
	 */
	private array $data;

	/**
	 * ExportDataCommand constructor.
	 *
	 * @param $format
	 * @param array $data
	 */
	public function __construct($format, array $data)
	{
		$this->format = $format;
		$this->data = $data;
	}

	/**
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function execute()
	{
		$items = [
			MetaTypes::CUSTOM_POST_TYPE => [],
			MetaTypes::TAXONOMY => [],
			MetaTypes::OPTION_PAGE => [],
			MetaTypes::META => [],
			'form' => [],
		];

		$format = $this->format;
		$dataToExport = $this->data;

		foreach ($dataToExport as $type => $data){
			foreach ($data as $datum){

				if($datum['type'] === 'form' and $datum['checked'] === true){

					/** @var FormModel $formModel */
					$formModel = @FormRepository::getById($datum['id']);

					if($formModel !== null){
						$items['form'][] = $formModel->arrayRepresentation();
					}
				}

				if($datum['type'] === MetaTypes::META and $datum['checked'] === true){

					/** @var MetaGroupModel $metaGroupModel */
					$metaGroupModel = @MetaRepository::get([
						'id' => $datum['id']
					])[0];

					if($metaGroupModel !== null){
						$items[MetaTypes::META][] = $metaGroupModel->arrayRepresentation();
					}
				}

				if($datum['type'] === MetaTypes::CUSTOM_POST_TYPE and $datum['checked'] === true){
					/** @var CustomPostTypeModel $customPostTypeModel */
					$customPostTypeModel = @CustomPostTypeRepository::get([
						'id' => $datum['id']
					])[0];

					if($customPostTypeModel !== null){
						$items[MetaTypes::CUSTOM_POST_TYPE][] = $customPostTypeModel->arrayRepresentation();
					}
				}

				if($datum['type'] === MetaTypes::TAXONOMY and $datum['checked'] === true){
					/** @var TaxonomyModel $taxonomyModel */
					$taxonomyModel = @TaxonomyRepository::get([
						'id' => $datum['id']
					])[0];

					if($taxonomyModel !== null){
						$items[MetaTypes::TAXONOMY][] = $taxonomyModel->arrayRepresentation();
					}
				}

				if($datum['type'] === MetaTypes::OPTION_PAGE and $datum['checked'] === true){
					/** @var OptionPageModel $optionPageModel */
					$optionPageModel = @OptionPageRepository::getById($datum['id']);

					if($optionPageModel !== null){
						$items[MetaTypes::OPTION_PAGE][] = $optionPageModel->arrayRepresentation();
					}
				}
			}
		}

		return Formatter::format($format, $items);
	}
}