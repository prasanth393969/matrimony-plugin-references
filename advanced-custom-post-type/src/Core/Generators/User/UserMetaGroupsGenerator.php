<?php

namespace ACPT\Core\Generators\User;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\AbstractGenerator;
use ACPT\Core\Repository\MetaRepository;

class UserMetaGroupsGenerator extends AbstractGenerator
{
	/**
	 * Generate meta boxes related to users
	 */
	public function generate()
	{
		try {
			$metaGroups = MetaRepository::get([
				'belongsTo' => MetaTypes::USER,
                'clonedFields' => true,
			]);

			if(!empty($metaGroups)){
				$generator = new UserMetaBoxGenerator($metaGroups);
				$generator->generate();
			}
		} catch (\Exception $exception) {
			// do nothing
            do_action("acpt/error", $exception);
		}
	}
}

