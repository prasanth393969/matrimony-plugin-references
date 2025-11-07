<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;

class FetchElementsQuery implements QueryInterface
{
	/**
	 * @var string
	 */
	private $belongsTo;

	/**
	 * @var null
	 */
	private $exclude;

	/**
	 * FetchElementsQuery constructor.
	 *
	 * @param $belongsTo
	 * @param null $exclude
	 */
	public function __construct($belongsTo, $exclude = null)
	{
		$this->belongsTo = $belongsTo;
		$this->exclude = $exclude;
	}

	/**
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function execute()
	{
		$result = [];

		if($this->belongsTo === MetaTypes::CUSTOM_POST_TYPE){
			$cpts = CustomPostTypeRepository::get([
				'exclude' => $this->exclude
			]);

			foreach ($cpts as $cpt){
				$result[] = [
					'label' => $cpt->getName(),
					'value' => $cpt->getName()
				];
			}
		}

		if($this->belongsTo === MetaTypes::TAXONOMY){
			$taxs = TaxonomyRepository::get([
				'exclude' => $this->exclude
			]);

			foreach ($taxs as $tax){
				$result[] = [
					'label' => $tax->getSlug(),
					'value' => $tax->getSlug()
				];
			}
		}

		if($this->belongsTo === MetaTypes::OPTION_PAGE){
			$pages = OptionPageRepository::get([
			]);

			foreach ($pages as $page){
				if($page->getMenuSlug() !== $this->exclude){
					$result[] = [
						'label' => $page->getPageTitle(),
						'value' => $page->getMenuSlug()
					];
				}

				foreach ($page->getChildren() as $childPage){
					if($childPage->getMenuSlug() !== $this->exclude){
						$result[] = [
							'label' => $childPage->getPageTitle(),
							'value' => $childPage->getMenuSlug()
						];
					}
				}
			}
		}

		return $result;
	}
}
