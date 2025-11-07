<?php

namespace ACPT\Utils\Vite;

class Assets
{
	/**
	 * @param $src
	 * @param $key
	 *
	 * @return array
	 */
	public static function load($src, $key)
	{
		$resources = [
			'js' => [],
			'css' => [],
		];

		$manifest = ACPT_PLUGIN_DIR_PATH."/assets/build/.vite/manifest.json";

		if(file_exists($manifest)){
			$json = json_decode(file_get_contents($manifest), true);

			foreach ($json as $entry){
				if( isset($entry['src']) and $entry['src'] === $src){
					$asset = $entry['file'];
					$styles = $entry['css'];

					$resources['js'][$key] = plugins_url( 'advanced-custom-post-type/assets/build/').$asset;

					foreach ($styles as $style){
						$resources['css'][$key] = plugins_url( 'advanced-custom-post-type/assets/build/').$style;
					}
				}
			}
		}

		return $resources;
	}
}