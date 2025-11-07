<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\OptionPage\OptionPageModel;
use ACPT\Core\Models\Permission\PermissionModel;
use ACPT\Core\Repository\OptionPageRepository;

class SaveOptionPageCommand implements CommandInterface, LogFormatInterface
{
	/**
	 * @var array
	 */
	private array $page;

	/**
	 * SaveOptionPagesCommand constructor.
	 *
	 * @param array $page
	 */
	public function __construct(array $page = [])
	{
		$this->page = $page;
	}

	/**
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function execute()
	{
		$pageSlugs = OptionPageRepository::getAllSlugs();
		$page = $this->page;

		foreach ($pageSlugs as $index => $pageSlug){
			if($page['menuSlug'] === $pageSlug){
				unset($pageSlugs[$index]);
			}
		}

		$pageModel = OptionPageModel::hydrateFromArray([
			'id' => @$page['id'],
			'parentId' => (isset($page['parentId']) ? $page['parentId'] : null),
			'pageTitle' => @$page['pageTitle'],
			'menuTitle' => @$page['menuTitle'],
			'menuSlug' => @$page['menuSlug'],
			'capability' => @$page['capability'],
			'icon' => (isset($page['icon']) ? $page['icon'] : null),
			'position' => @$page['position'],
			'description' => @$page['description'],
			'sort' => (isset($page['sort']) ? $page['sort'] : OptionPageRepository::count()+1),
		]);

		$pageModel->setMenuSlug(Strings::getTheFirstAvailableName($pageModel->getMenuSlug(), $pageSlugs));
		$pageSlugs = [];

		if(isset($page['children']) and is_array($page['children'])){

			$childPosition = @$page['position'];

			foreach ($page['children'] as $childIndex => $child){

				$childPosition = $childPosition + 1;

				$childPageModel = OptionPageModel::hydrateFromArray([
					'id' => @$child['id'],
					'parentId' => $pageModel->getId(),
					'pageTitle' => @$child['pageTitle'],
					'menuTitle' => @$child['menuTitle'],
					'menuSlug' => @$child['menuSlug'],
					'capability' => @$child['capability'],
					'position' => $childPosition,
					'description' => @$child['description'],
					'sort' => (isset($child['sort']) ? $child['sort'] : $childIndex+1),
				]);

				$childPageModel->setMenuSlug(Strings::getTheFirstAvailableName($childPageModel->getMenuSlug(), $pageSlugs));
				$ids[] = $childPageModel->getId();
				$pageSlugs[] = $childPageModel->getMenuSlug();

				$pageModel->addChild($childPageModel);
			}
		}

		$permissions = $page['permissions'] ?? [];

		if(is_array($permissions) and !empty($permissions)){
			foreach ($permissions as $permissionIndex => $permission){
				$permissionModel = PermissionModel::hydrateFromArray([
					'id' => (isset($permission["id"]) ? $permission["id"] : Uuid::v4()),
					'entityId' => $pageModel->getId(),
					'userRole' => $permission['userRole'] ?? $permission['user_role'],
					'permissions' => $permission['permissions'] ?? [],
					'sort' => ($permissionIndex+1),
				]);

				$pageModel->addPermission($permissionModel);
			}
		}

		OptionPageRepository::save($pageModel);

		// save permissions
		if($pageModel->hasPermissions()){
			$command = new SavePermissionCommand([
				'entityId' => $pageModel->getId(),
				'items' => $pageModel->gerPermissionsAsArray()
			]);

			$command->execute();
		}

		do_action("acpt/option_page/save", $this, $pageModel);

		return $pageModel->getId();
	}

    /**
     * @inheritDoc
     */
    public function logFormat(): array
    {
        return [
            "class"  => SaveOptionPageCommand::class,
            "data" => $this->page
        ];
    }
}