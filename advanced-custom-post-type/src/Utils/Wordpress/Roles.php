<?php

namespace ACPT\Utils\Wordpress;

class Roles
{
	/**
	 * $return can be: array|objects|names
	 *
	 * @param string $return
	 *
	 * @return array
	 */
	public static function get($return = 'array')
	{
	    $wp_roles = wp_roles();
		$roles = [];

		if($wp_roles === null){
			return $roles;
		}

		$roleNames = $wp_roles->get_names();
		asort($roleNames);

		switch ($return){
			case "array":
				foreach ($roleNames as $value => $label){
					$roles[] = [
						'label' => $label,
						'value' => $value,
					];
				}

				break;

			case "names":
				foreach ($roleNames as $value => $label){
					$roles[] = $value;
				}
				break;

			case "objects":
				foreach ($roleNames as $value => $label){
					$roles[$value] = get_role($value);
				}
				break;
		}

		return $roles;
	}

	/**
	 * Refresh rules
	 */
	public static function refresh()
	{
		// refresh roles
		$wpRoles = new \WP_Roles();
		$wpRoles->for_site();
	}
}