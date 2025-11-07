<?php

namespace ACPT\Integrations\WPML\Helper;

use ACPT\Constants\MetaTypes;
use ACPT\Utils\Data\Formatter\Driver\XMLFormatter;
use ACPT\Utils\Data\Meta;

/**
 * Correct settings
 * @see https://wpml.org/documentation/related-projects/translate-sites-built-with-acf/recommended-custom-fields-translation-preferences-for-acf-and-wpml/
 */
class WPMLConfig
{
	const ACPT_WPML_CONFIG_KEY = 'acpt_wpml_config';

	/**
	 * @return string
	 */
	private static function filePath()
	{
		return ACPT_PLUGIN_DIR_PATH .'wpml-config.xml';
	}

	/**
	 * Delete the wpml-config.xml file
	 */
	public static function destroy()
	{
		Meta::delete(self::ACPT_WPML_CONFIG_KEY, MetaTypes::OPTION_PAGE, self::ACPT_WPML_CONFIG_KEY);

		if(self::fileExists()){
			unlink(self::filePath());
		}
	}

	/**
	 * @return bool
	 */
	public static function fileExists()
	{
		return file_exists(self::filePath());
	}

    /**
     * @param $xml
     *
     * @return bool
     */
	public static function createFile($xml)
    {
        $fileExists = self::fileExists();

        if(!$fileExists){
            $fileExists = touch(self::filePath());
        }

        if($fileExists === true){
            if(file_put_contents(self::filePath(), $xml)){
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Generate wpml-config.xml file from wp_options cache
     */
    public static function generateFromCache()
    {
        if(self::fileExists()){
            return;
        }

        $xmlInCache = Meta::fetch(self::ACPT_WPML_CONFIG_KEY, MetaTypes::OPTION_PAGE, self::ACPT_WPML_CONFIG_KEY);

        // if file not exist but xml is saved in cache
        if($xmlInCache !== null){
            self::createFile($xmlInCache);
        }
    }

	/**
	 * Generate wpml-config.xml file
	 * (every 2 hours)
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function generate(array $data = [])
	{
		try {
			$xml = self::xml($data);

			// cache check
            $xmlInCache = Meta::fetch(self::ACPT_WPML_CONFIG_KEY, MetaTypes::OPTION_PAGE, self::ACPT_WPML_CONFIG_KEY);

            if($xmlInCache === $xml){
                return false;
            }

			// save on options
			Meta::save(self::ACPT_WPML_CONFIG_KEY, MetaTypes::OPTION_PAGE, self::ACPT_WPML_CONFIG_KEY, $xml);

			// save on disk
			return self::createFile($xml);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return false;
		}
	}

	/**
	 * Generate the xml
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function  xml(array $data = [])
	{
		$_xml = new \SimpleXMLElement( '<wpml-config/>');

		$allowedAttributes = [
			'action',
			'style',
			'label'
		];

		// <custom-types>
        if(isset($data['custom-types'])){
            $postTypesNode = $_xml->addChild('custom-types');

            foreach ($data['custom-types'] as $name => $postType){

                $attrs = [
                    "translate" => (is_array($postType) and isset($postType['translate'])) ? $postType['translate'] : 1,
                    "display_as_translated" => (is_array($postType) and isset($postType['display_as_translated'])) ? $postType['display_as_translated'] : 1,
                    "automatic" => (is_array($postType) and isset($postType['automatic'])) ? $postType['automatic'] : 1,
                ];

                $postTypeNode = $postTypesNode->addChild("custom-type", $name);
                $postTypeNode->addAttribute("translate", $attrs['translate']);
                $postTypeNode->addAttribute("display-as-translated", $attrs['display_as_translated']);
                $postTypeNode->addAttribute("automatic", $attrs['automatic']);

                Meta::save(self::cacheFieldKey($postType['id']), MetaTypes::OPTION_PAGE, self::cacheFieldKey($postType['id']), $attrs);
            }
        }

        // <taxonomies>
        if(isset($data['taxonomies'])){
            $taxonomiesNode = $_xml->addChild('taxonomies');

            foreach ($data['taxonomies'] as $name => $taxonomy){

                $attrs = [
                    "translate" => (is_array($taxonomy) and isset($taxonomy['translate'])) ? $taxonomy['translate'] : 1,
                    "display_as_translated" => (is_array($taxonomy) and isset($taxonomy['display_as_translated'])) ? $taxonomy['display_as_translated'] : 1,
                ];

                $taxonomyNode = $taxonomiesNode->addChild("taxonomy", $name);
                $taxonomyNode->addAttribute("translate", $attrs['translate']);
                $taxonomyNode->addAttribute("display-as-translated", $attrs['display_as_translated']);

                Meta::save(self::cacheFieldKey($taxonomy['id']), MetaTypes::OPTION_PAGE, self::cacheFieldKey($taxonomy['id']), $attrs);
            }
        }

		// <custom-fields>
		if(isset($data['custom-fields'])){
			$fieldsNode = $_xml->addChild('custom-fields');

			foreach ($data['custom-fields'] as $fieldName => $attrs){

				// save settings for this field
				Meta::save(self::cacheFieldKey($attrs['id']), MetaTypes::OPTION_PAGE, self::cacheFieldKey($attrs['id']), $attrs);
				$fieldNode = $fieldsNode->addChild("custom-field", $fieldName);

				foreach ($attrs as $label => $value){
					if(in_array($label, $allowedAttributes)){
						$fieldNode->addAttribute($label, $value);
					}
				}
			}
		}

		// <custom-term-fields>
		if(isset($data['custom-term-fields']) and !empty($data['custom-term-fields'])){
			$fieldsNode = $_xml->addChild('custom-term-fields');

			foreach ($data['custom-term-fields'] as $fieldName => $attrs){

				// save settings for this field
				Meta::save(self::cacheFieldKey($attrs['id']), MetaTypes::OPTION_PAGE, self::cacheFieldKey($attrs['id']), $attrs);
				$fieldNode = $fieldsNode->addChild("custom-term-field", $fieldName);

				foreach ($attrs as $label => $value){
					if(in_array($label, $allowedAttributes)){
						$fieldNode->addAttribute($label, $value);
					}
				}
			}
		}

		// <admin-texts>
		if(isset($data['admin-texts']) and !empty($data['admin-texts'])){
			$fieldsNode = $_xml->addChild('admin-texts');

			foreach ($data['admin-texts'] as $fieldName => $attrs){

				// save settings for this field
				Meta::save(self::cacheFieldKey($attrs['id']), MetaTypes::OPTION_PAGE, self::cacheFieldKey($attrs['id']), $attrs);
				$fieldNode = $fieldsNode->addChild("key");
				$fieldNode->addAttribute('name', $fieldName);

				foreach ($attrs as $label => $value){
					if(in_array($label, $allowedAttributes)){
						$fieldNode->addAttribute($label, $value);
					}
				}
			}
		}

		$xml = $_xml->asXML();

		return XMLFormatter::beautify($xml);
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public static function cacheFieldKey($id)
	{
		return self::ACPT_WPML_CONFIG_KEY . "_field_".$id."_settings";
	}
}
