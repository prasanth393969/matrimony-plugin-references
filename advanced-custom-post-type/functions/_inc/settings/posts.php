<?php

use ACPT\Core\CQRS\Command\DeleteCustomPostTypeCommand;
use ACPT\Core\CQRS\Command\SaveCustomPostTypeCommand;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Repository\CustomPostTypeRepository;

if( !function_exists('register_acpt_post_type') ){

    /**
     * Register a new custom post type.
     *
     * @param array $args
     *
     * @return \WP_Post_Type|null
     */
    function register_acpt_post_type($args = [])
    {
        try {
	        $id = (CustomPostTypeRepository::exists($args["post_name"])) ? CustomPostTypeRepository::getId($args["post_name"]) : Uuid::v4();
	        $data = [
		        'id' => $id,
		        'name' => @$args["post_name"],
		        'singular_label' => $args["singular_label"] ?? $args["singularLabel"],
		        'plural_label' => $args["plural_label"] ?? $args["pluralLabel"],
		        'icon' => @$args["icon"],
		        'supports' => @$args['supports'],
		        'labels' => @$args['labels'],
		        'settings' => @$args['settings'],
		        'permissions' => $args['permissions'] ?? []
	        ];

	        $command = new SaveCustomPostTypeCommand($data);
	        $command->execute();

            return get_post_type_object($args["post_name"]);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return null;
        }
    }
}

if( !function_exists('delete_acpt_post_type') ){

    /**
     * Delete a custom post type.
     *
     * @param string $post_type
     * @param bool   $delete_posts
     *
     * @return bool
     */
    function delete_acpt_post_type($post_type, $delete_posts = false)
    {
        if(!CustomPostTypeRepository::exists($post_type)){
            return false;
        }

        try {
            $command = new DeleteCustomPostTypeCommand($post_type);
	        $command->execute();

            return true;

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return false;
        }
    }
}