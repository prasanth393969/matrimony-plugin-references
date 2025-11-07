<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Core\Generators\CustomPostType\CustomPostTypeGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\CustomPostType\CustomPostTypeModel;
use ACPT\Core\Models\Permission\PermissionModel;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Utils\PHP\Image;
use ACPT\Utils\Wordpress\WPAttachment;

class SaveCustomPostTypeCommand implements CommandInterface, LogFormatInterface
{
	/**
	 * @var array
	 */
	private array $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function execute()
	{
		$data = $this->data;

		// Custom icon image resize
		if(isset($data['icon']) and Strings::isUrl($data['icon'])){
			$attachment = WPAttachment::fromUrl($data['icon']);

			if($attachment->isImage()){
				$data['icon'] = Image::resize($attachment, 20, 20);
			}
		}

		$postTypeModel = CustomPostTypeModel::hydrateFromArray([
			'id' => ($data['id'] ? $data['id'] : Uuid::v4()),
			'name' => @$data['name'],
			'singular' => @$data["singular_label"],
			'plural' => @$data["plural_label"],
			'icon' => @$data["icon"],
			'native' => false,
			'supports' => @$data['supports'],
			'labels' =>  @$data['labels'],
			'settings' =>  @$data['settings'],
		]);

		CustomPostTypeRepository::save($postTypeModel);

		$permissions = $data['permissions'] ?? [];

		if(is_array($permissions) and !empty($permissions)){
			foreach ($permissions as $permissionIndex => $permission){
				$permissionModel = PermissionModel::hydrateFromArray([
					'id' => (isset($permission["id"]) ? $permission["id"] : Uuid::v4()),
					'entityId' => $postTypeModel->getId(),
					'userRole' => $permission['userRole'] ?? $permission['user_role'],
					'permissions' => $permission['permissions'] ?? [],
					'sort' => ($permissionIndex+1),
				]);

				$postTypeModel->addPermission($permissionModel);
			}
		}

		// generate CPT in WP tables
		$customPostTypeGenerator = new CustomPostTypeGenerator($postTypeModel);
		$customPostTypeGenerator->registerPostType();

		// save permissions
		if($postTypeModel->hasPermissions()){
			$command = new SavePermissionCommand([
				'entityId' => $postTypeModel->getId(),
				'items' => $postTypeModel->gerPermissionsAsArray()
			]);

			$command->execute();
		}

        $this->flushPermalinkRules();

        do_action("acpt/custom_post_type/save", $this, $postTypeModel);

		return $postTypeModel->getId();
	}

	/**
	 * Reset the permalink structure
	 */
	private function flushPermalinkRules()
	{
        flush_rewrite_rules();
	}

    /**
     * @inheritDoc
     */
    public function logFormat(): array
    {
        return [
            "class"  => SaveCustomPostTypeCommand::class,
            'data' => $this->data
        ];
    }
}