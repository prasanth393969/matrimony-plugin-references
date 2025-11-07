<?php

use ACPT\Core\CQRS\Command\SaveOptionPageCommand;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Repository\OptionPageRepository;

if( !function_exists('register_acpt_option_page') ){
	function register_acpt_option_page($args = [])
	{
		try {
			$parentId = null;
			$menuSlug = $args["menu_slug"] ?? $args["menuSlug"];
			$pageTitle = $args["page_title"] ?? $args["pageTitle"];
			$menuTitle = $args["menu_title"] ?? $args["menuTitle"];

			$id = (OptionPageRepository::exists($menuSlug)) ? OptionPageRepository::getByMenuSlug($menuSlug)->getId() : Uuid::v4();

			if(isset($args['parent'])){
				$parentId = (OptionPageRepository::exists($args["parent"])) ? OptionPageRepository::getByMenuSlug($args["parent"])->getId() : null;

				if(!$parentId){
					return false;
				}
			}

			$command = new SaveOptionPageCommand([
				'id' => $id,
				'parentId' => $parentId,
				'pageTitle' => @$pageTitle,
				'menuTitle' => @$menuTitle,
				'capability' => $args["capability"],
				'menuSlug' => @$menuSlug,
				'position' => $args["position"],
				'icon' => (isset($args["icon"]) ? $args["icon"] : null),
				'description' => $args["description"],
				'children' => [],
				'permissions' => $args["permissions"] ?? [],
				'sort' => (count(OptionPageRepository::getAllIds()) + 1)
			]);
			$command->execute();

			return true;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}

if( !function_exists('delete_acpt_option_page') ){
	function delete_acpt_option_page($page_slug, $delete_options = false)
	{
		if(!OptionPageRepository::exists($page_slug)){
			return false;
		}

		try {
			$option_page = OptionPageRepository::getByMenuSlug($page_slug);
			OptionPageRepository::delete($option_page, $delete_options);

			return true;

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}
}