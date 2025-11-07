<?php

namespace ACPT\Core\Generators\Taxonomy;

use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Repository\TaxonomyRepository;

class TaxonomyMetaGroupsGenerator extends AbstractGenerator
{
	/**
	 * Generate meta boxes related to taxonomies
	 */
	public function generate()
	{
		try {
			foreach (TaxonomyRepository::get() as $taxonomyModel){
				$taxonomyMetaBoxGenerator = new TaxonomyMetaBoxGenerator($taxonomyModel);
				$taxonomyMetaBoxGenerator->generate();
			}
		} catch (\Exception $exception){
			// do nothing
            do_action("acpt/error", $exception);
		}
	}
}