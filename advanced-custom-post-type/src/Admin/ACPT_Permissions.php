<?php

namespace ACPT\Admin;

use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Utils\Wordpress\Roles;

class ACPT_Permissions
{
	/**
	 * Save roles permissions
	 */
	public function setPermissions()
	{
		try {
			$allTheRoles = Roles::get('objects');
			$this->setPostPermissions($allTheRoles);
			$this->setOptionPagesPermissions($allTheRoles);
			$this->setTaxonomyPermissions($allTheRoles);
			$this->setMetaFieldsPermissions($allTheRoles);
		} catch (\Exception $exception){
            do_action("acpt/error", $exception);
			// do nothing
		}
	}

	/**
	 * @param \WP_Role[] $allTheRoles
	 *
	 * @throws \Exception
	 */
	private function setPostPermissions($allTheRoles)
	{
	    if(!ACPT_ENABLE_CPT){
	        return;
        }

		$customPostTypes = CustomPostTypeRepository::get([]);

		foreach ($customPostTypes as $customPostType){
			if(!$customPostType->isNative() and $customPostType->hasPermissions()){

				$capabilityType = $customPostType->capabilityType();
				$customPostTypeRoleNames = [];

				// Set specific user roles
				foreach ($customPostType->getPermissions() as $permission){
					$customPostTypeRoleNames[] = $permission->getUserRole();
					$role = get_role($permission->getUserRole());

					if($role !== null){
						$permissions = $permission->getPermissions();

						$edit = $permissions['edit_s'];
						$editPrivate = $permissions['edit_private_s'];
						$editPublished = $permissions['edit_published_s'];
						$editOthers = $permissions['edit_others_s'];
						$publish = $permissions['publish_s'];
						$readPrivate = $permissions['read_private_s'];
						$delete = $permissions['delete_s'];
						$deletePrivate = $permissions['delete_private_s'];
						$deletePublished = $permissions['delete_published_s'];
						$deleteOther = $permissions['delete_others_s'];

						// edit
						if($edit === true){
							$role->add_cap( 'edit_'.$capabilityType );
							$role->add_cap( 'edit_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'edit_'.$capabilityType );
							$role->remove_cap( 'edit_'.$capabilityType.'s' );
						}

						if($editPrivate === true){
							$role->add_cap( 'edit_private_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'edit_private_'.$capabilityType.'s' );
						}

						if($editPublished === true){
							$role->add_cap( 'edit_published_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'edit_published_'.$capabilityType.'s' );
						}

						if($editOthers === true){
							$role->add_cap( 'edit_others_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'edit_others_'.$capabilityType.'s' );
						}

						// publish
						if($publish === true){
							$role->add_cap( 'publish_'.$capabilityType.'s' );
							$role->add_cap( 'publish_'.$capabilityType );
						} else {
							$role->remove_cap( 'publish_'.$capabilityType.'s' );
							$role->remove_cap( 'publish_'.$capabilityType );
						}

						// read
						if($readPrivate === true){

							if($edit === false){
								$role->add_cap( 'edit_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_'.$capabilityType );
							}

							$role->add_cap( 'read_private_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'read_private_'.$capabilityType.'s' );
							$role->remove_cap( 'edit_'.$capabilityType );
							$role->remove_cap( 'edit_'.$capabilityType.'s' );
						}

						// delete
						if($delete === true){
							$role->add_cap( 'delete_'.$capabilityType );
							$role->add_cap( 'delete_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'delete_'.$capabilityType );
							$role->remove_cap( 'delete_'.$capabilityType.'s' );
						}

						if($deletePrivate === true){
							$role->add_cap( 'delete_private_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'delete_private_'.$capabilityType.'s' );
						}

						if($deletePublished === true){
							$role->add_cap( 'delete_published_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'delete_published_'.$capabilityType.'s' );
						}

						if($deleteOther === true){
							$role->add_cap( 'delete_others_'.$capabilityType.'s' );
						} else {
							$role->remove_cap( 'delete_others_'.$capabilityType.'s' );
						}
					}
				}

				// Reset the permissions for the other roles
				foreach ($allTheRoles as $role){
					if(!in_array($role->name, $customPostTypeRoleNames)){

						switch($role->name){
							case "super_admin":
							case "administrator":
							case "editor":
								$role->add_cap( 'edit_'.$capabilityType );
								$role->add_cap( 'edit_'.$capabilityType.'s' );
								$role->add_cap( 'edit_private_'.$capabilityType.'s' );
								$role->add_cap( 'edit_published_'.$capabilityType.'s' );
								$role->add_cap( 'edit_other_'.$capabilityType.'s' );
								$role->add_cap( 'publish_'.$capabilityType );
								$role->add_cap( 'publish_'.$capabilityType.'s' );
								$role->add_cap( 'read_private_'.$capabilityType.'s' );
								$role->add_cap( 'delete_'.$capabilityType );
								$role->add_cap( 'delete_'.$capabilityType.'s' );
								$role->add_cap( 'delete_private_'.$capabilityType.'s' );
								$role->add_cap( 'delete_published_'.$capabilityType.'s' );
								$role->add_cap( 'delete_others_'.$capabilityType.'s' );
								break;

							case "author":
								$role->add_cap( 'edit_'.$capabilityType );
								$role->add_cap( 'edit_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_private_'.$capabilityType.'s' );
								$role->add_cap( 'edit_published_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_other_'.$capabilityType.'s' );
								$role->add_cap( 'publish_'.$capabilityType );
								$role->add_cap( 'publish_'.$capabilityType.'s' );
								$role->remove_cap( 'read_private_'.$capabilityType.'s' );
								$role->add_cap( 'delete_'.$capabilityType );
								$role->add_cap( 'delete_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_private_'.$capabilityType.'s' );
								$role->add_cap( 'delete_published_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_others_'.$capabilityType.'s' );
								break;

							case "contributor":
								$role->add_cap( 'edit_'.$capabilityType );
								$role->add_cap( 'edit_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_private_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_published_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_other_'.$capabilityType.'s' );
								$role->remove_cap( 'publish_'.$capabilityType );
								$role->remove_cap( 'publish_'.$capabilityType.'s' );
								$role->remove_cap( 'read_private_'.$capabilityType.'s' );
								$role->add_cap( 'delete_'.$capabilityType );
								$role->add_cap( 'delete_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_private_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_published_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_others_'.$capabilityType.'s' );
								break;

							default:
							case "subscriber":
								$role->remove_cap( 'edit_'.$capabilityType );
								$role->remove_cap( 'edit_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_private_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_published_'.$capabilityType.'s' );
								$role->remove_cap( 'edit_other_'.$capabilityType.'s' );
								$role->remove_cap( 'publish_'.$capabilityType );
								$role->remove_cap( 'publish_'.$capabilityType.'s' );
								$role->remove_cap( 'read_private_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_'.$capabilityType );
								$role->remove_cap( 'delete_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_private_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_published_'.$capabilityType.'s' );
								$role->remove_cap( 'delete_others_'.$capabilityType.'s' );
								break;
						}
					}
				}

				Roles::refresh();
			}
		}
	}

	/**
	 * @param $allTheRoles
	 *
	 * @throws \Exception
	 */
	private function setOptionPagesPermissions($allTheRoles)
	{
        if(!ACPT_ENABLE_PAGES){
            return;
        }

		$optionPages = OptionPageRepository::get([]);

		foreach($optionPages as $optionPage){
			$this->setOptionPagePermissions($optionPage, $allTheRoles);

			foreach ($optionPage->getChildren() as $childOptionPage){
				$this->setOptionPagePermissions($childOptionPage, $allTheRoles);
			}
		}
	}

	/**
	 * @param OptionPageModel $optionPage
	 * @param $allTheRoles
	 */
	private function setOptionPagePermissions(OptionPageModel $optionPage, $allTheRoles)
	{
		if($optionPage->hasPermissions()){
			$optionPageRoleNames = [];
			$capabilityType = $optionPage->capabilityType();

			// Set specific user roles
			foreach ($optionPage->getPermissions() as $permission){
				$optionPageRoleNames[] = $permission->getUserRole();
				$role = get_role($permission->getUserRole());

				if($role !== null){
					$permissions = $permission->getPermissions();
					$edit = $permissions['edit'];
					$read = $permissions['read'];

					// edit
					if($edit === true){
						$role->add_cap( 'edit_'.$capabilityType );
					} else {
						$role->remove_cap( 'edit_'.$capabilityType );
					}

					// read
					if($read === true){
						$role->add_cap( 'read_'.$capabilityType );
					} else {
						$role->remove_cap( 'read_'.$capabilityType );
					}
				}
			}

			// Reset the permissions for the other roles
			foreach ($allTheRoles as $role){
				if(!in_array($role->name, $optionPageRoleNames)){
					switch($role->name){
						case "super_admin":
						case "administrator":
						case "editor":
						case "author":
							$role->add_cap( 'edit_'.$capabilityType );
							$role->add_cap( 'read_'.$capabilityType );
							break;

						case "contributor":
							$role->remove_cap( 'edit_'.$capabilityType );
							$role->add_cap( 'read_'.$capabilityType );
							break;

						default:
						case "subscriber":
							$role->remove_cap( 'edit_'.$capabilityType );
							$role->remove_cap( 'read_'.$capabilityType );
							break;
					}
				}
			}

			Roles::refresh();
		}
	}

	/**
	 * @param $allTheRoles
	 *
	 * @throws \Exception
	 */
	private function setTaxonomyPermissions($allTheRoles)
	{
        if(!ACPT_ENABLE_TAX){
            return;
        }

		$taxonomies = TaxonomyRepository::get([]);

		foreach ($taxonomies as $taxonomy) {
			if(!$taxonomy->isNative() and $taxonomy->hasPermissions()) {
				$capabilityType = $taxonomy->capabilityType();
				$taxonomyRoleNames = [];

				// Set specific user roles
				foreach ($taxonomy->getPermissions() as $permission){
					$taxonomyRoleNames[] = $permission->getUserRole();
					$role = get_role($permission->getUserRole());

					if($role !== null) {
						$permissions = $permission->getPermissions();
						$edit        = $permissions['edit'];
						$assign      = $permissions['assign'];
						$manage      = $permissions['manage'];
						$delete      = $permissions['delete'];

						// edit
						if($edit === true){
							$role->add_cap( 'edit_'.$capabilityType );
						} else {
							$role->remove_cap( 'edit_'.$capabilityType );
						}

						// assign
						if($assign === true){
							$role->add_cap( 'assign_'.$capabilityType );
						} else {
							$role->remove_cap( 'assign_'.$capabilityType );
						}

						// manage
						if($manage === true){
							$role->add_cap( 'manage_'.$capabilityType );
						} else {
							$role->remove_cap( 'manage_'.$capabilityType );
						}

						// delete
						if($delete === true){
							$role->add_cap( 'delete_'.$capabilityType );
						} else {
							$role->remove_cap( 'delete_'.$capabilityType );
						}
					}
				}

				// Reset the permissions for the other roles
				foreach ($allTheRoles as $role){
					if(!in_array($role->name, $taxonomyRoleNames)){

						switch($role->name){
							case "super_admin":
							case "administrator":
							case "editor":
							case "author":
								$role->add_cap( 'edit_'.$capabilityType );
								$role->add_cap( 'assign_'.$capabilityType );
								$role->add_cap( 'manage_'.$capabilityType );
								$role->add_cap( 'delete_'.$capabilityType );
								break;

							case "contributor":
								$role->add_cap( 'edit_'.$capabilityType );
								$role->add_cap( 'assign_'.$capabilityType );
								$role->add_cap( 'manage_'.$capabilityType );
								$role->remove_cap( 'delete_'.$capabilityType );
								break;

							default:
							case "subscriber":
								$role->remove_cap( 'edit_'.$capabilityType );
								$role->remove_cap( 'assign_'.$capabilityType );
								$role->remove_cap( 'manage_'.$capabilityType );
								$role->remove_cap( 'delete_'.$capabilityType );
								break;
						}
					}
				}

				Roles::refresh();
			}
		}
	}

	/**
	 * @param $allTheRoles
	 *
	 * @throws \Exception
	 */
	private function setMetaFieldsPermissions($allTheRoles)
	{
        if(!ACPT_ENABLE_META){
            return;
        }

		$metaFields = MetaRepository::getMetaFields([]);

		foreach ($metaFields as $metaField) {
			if($metaField->hasPermissions()) {
				$capabilityType = $metaField->capabilityType();
				$fieldRoleNames = [];

				// Set specific user roles
				foreach ($metaField->getPermissions() as $permission){
					$fieldRoleNames[] = $permission->getUserRole();
					$role = get_role($permission->getUserRole());

					if($role !== null){
						$permissions = $permission->getPermissions();
						$edit = $permissions['edit'];
						$read = $permissions['read'];

						// edit
						if($edit === true){
							$role->add_cap( 'edit_'.$capabilityType );
						} else {
							$role->remove_cap( 'edit_'.$capabilityType );
						}

						// read
						if($read === true){
							$role->add_cap( 'read_'.$capabilityType );
						} else {
							$role->remove_cap( 'read_'.$capabilityType );
						}
					}
				}

				// Reset the permissions for the other roles
				foreach ($allTheRoles as $role){
					if(!in_array($role->name, $fieldRoleNames)){
						switch($role->name){
							case "super_admin":
							case "administrator":
							case "editor":
							case "author":
								$role->add_cap( 'edit_'.$capabilityType );
								$role->add_cap( 'read_'.$capabilityType );
								break;

							case "contributor":
								$role->remove_cap( 'edit_'.$capabilityType );
								$role->add_cap( 'read_'.$capabilityType );
								break;

							default:
							case "subscriber":
								$role->remove_cap( 'edit_'.$capabilityType );
								$role->remove_cap( 'read_'.$capabilityType );
								break;
						}
					}
				}

				Roles::refresh();
			}
		}
	}
}