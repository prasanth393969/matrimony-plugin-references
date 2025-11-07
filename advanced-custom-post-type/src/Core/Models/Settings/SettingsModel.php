<?php

namespace ACPT\Core\Models\Settings;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Models\Abstracts\AbstractModel;

/**
 * SettingsModel
 *
 * @since      1.0.0
 * @package    advanced-custom-post-type
 * @subpackage advanced-custom-post-type/core
 * @author     Mauro Cassani <maurocassani1978@gmail.com>
 */
class SettingsModel extends AbstractModel implements \JsonSerializable
{
	// option keys
	const LANGUAGE_KEY = 'language';
	const RECORDS_PER_PAGE_KEY = 'records_per_page';
	const GOOGLE_MAPS_API_KEY = 'google_maps_api_key';
	const GOOGLE_RECAPTCHA_SITE_KEY = 'google_recaptcha_site_key';
	const GOOGLE_RECAPTCHA_SECRET_KEY = 'google_recaptcha_secret_key';
	const CLOUDFLARE_TURNSTILE_SITE_KEY = 'cloudflare_turnstile_site_key';
	const CLOUDFLARE_TURNSTILE_SECRET_KEY = 'cloudflare_turnstile_secret_key';
	const SKIN = 'skin';
	const ENABLE_BETA = 'enable_beta';
	const ENABLE_CACHE = 'enable_cache';
	const ENABLE_META_CACHE = 'enable_meta_cache';
	const CACHE_DRIVER = 'cache_driver';
	const CACHE_CONFIG = 'cache_config';
	const ENABLE_FORMS = 'enable_forms';
	const ENABLE_CPT = 'enable_cpt';
	const ENABLE_TAX = 'enable_tax';
	const ENABLE_OP = 'enable_op';
	const ENABLE_META = 'enable_meta';
	const ENABLE_BLOCKS = 'enable_blocks';
	const DELETE_TABLES_WHEN_DEACTIVATE_KEY = 'delete_tables_when_deactivate';
	const DELETE_POSTS_KEY = 'delete_posts';
	const DELETE_POSTMETA_KEY = 'delete_metadata';
	const DELETE_UNUSED_TABLES = 'delete_unused_tables';
	const ENABLE_LOG = 'enable_log';

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * SettingsModel constructor.
     *
     * @param $id
     * @param $key
     * @param $value
     */
    public function __construct(
        $id,
        $key,
        $value
    ) {
        parent::__construct($id);
        $this->key   = $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getDecodedValue()
    {
        $value = $this->getValue();

        if(Strings::isJson($value)){
            $value = json_decode($value);
        }

        return $value;
    }

	#[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'value' => $this->getDecodedValue(),
        ];
    }

	/**
	 * @inheritDoc
	 */
	public static function validationRules(): array
	{
		return [
			'id' => [
				'required' => false,
				'type' => 'string',
			],
			'key' => [
				'required' => true,
				'type' => 'string',
			],
			'value' => [
				'required' => true,
				'type' => 'string|integer',
			],
		];
	}
}