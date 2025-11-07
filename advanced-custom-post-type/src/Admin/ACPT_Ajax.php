<?php

namespace ACPT\Admin;

use ACPT\Constants\FormAction;
use ACPT\Constants\MetaTypes;
use ACPT\Constants\Visibility;
use ACPT\Core\CQRS\Command\AssocTaxonomyToCustomPostTypeCommand;
use ACPT\Core\CQRS\Command\CopyMetaBlockCommand;
use ACPT\Core\CQRS\Command\CopyMetaBoxCommand;
use ACPT\Core\CQRS\Command\CopyMetaFieldCommand;
use ACPT\Core\CQRS\Command\CopyOptionPageCommand;
use ACPT\Core\CQRS\Command\CreatePostAndLinkToARelationalFieldCommand;
use ACPT\Core\CQRS\Command\DeleteBlockCommand;
use ACPT\Core\CQRS\Command\DeleteCustomPostTypeCommand;
use ACPT\Core\CQRS\Command\DeleteDatasetCommand;
use ACPT\Core\CQRS\Command\DeleteFormCommand;
use ACPT\Core\CQRS\Command\DeleteMetaGroupCommand;
use ACPT\Core\CQRS\Command\DeleteOptionPageCommand;
use ACPT\Core\CQRS\Command\DeleteTableTemplateCommand;
use ACPT\Core\CQRS\Command\DeleteTaxonomyCommand;
use ACPT\Core\CQRS\Command\DeleteWooCommerceProductDataCommand;
use ACPT\Core\CQRS\Command\DuplicateBlockCommand;
use ACPT\Core\CQRS\Command\DuplicateCustomPostTypeCommand;
use ACPT\Core\CQRS\Command\DuplicateDatasetCommand;
use ACPT\Core\CQRS\Command\DuplicateFormCommand;
use ACPT\Core\CQRS\Command\DuplicateMetaGroupCommand;
use ACPT\Core\CQRS\Command\DuplicateOptionPageCommand;
use ACPT\Core\CQRS\Command\DuplicateTaxonomyCommand;
use ACPT\Core\CQRS\Command\DuplicateWooCommerceProductDataCommand;
use ACPT\Core\CQRS\Command\ExportDataCommand;
use ACPT\Core\CQRS\Command\GenerateApiKeyCommand;
use ACPT\Core\CQRS\Command\ImportDatasetCommand;
use ACPT\Core\CQRS\Command\ImportFileCommand;
use ACPT\Core\CQRS\Command\PingACPTServerCommand;
use ACPT\Core\CQRS\Command\RegeneratePostLabelsCommand;
use ACPT\Core\CQRS\Command\RegenerateTaxonomyLabelsCommand;
use ACPT\Core\CQRS\Command\SaveBlockCommand;
use ACPT\Core\CQRS\Command\SaveCustomPostTypeCommand;
use ACPT\Core\CQRS\Command\SaveDatasetCommand;
use ACPT\Core\CQRS\Command\SaveFormCommand;
use ACPT\Core\CQRS\Command\SaveFormFieldsCommand;
use ACPT\Core\CQRS\Command\SaveMetaGroupCommand;
use ACPT\Core\CQRS\Command\SaveOptionPagesCommand;
use ACPT\Core\CQRS\Command\SavePermissionCommand;
use ACPT\Core\CQRS\Command\SaveSettingsCommand;
use ACPT\Core\CQRS\Command\SaveTableTemplateCommand;
use ACPT\Core\CQRS\Command\SaveTaxonomyCommand;
use ACPT\Core\CQRS\Command\SaveWPMLConfigCommand;
use ACPT\Core\CQRS\Query\CacheStatsQuery;
use ACPT\Core\CQRS\Query\CalculateShortCodeQuery;
use ACPT\Core\CQRS\Query\FetchAllFindBelongsQuery;
use ACPT\Core\CQRS\Query\FetchChangelogQuery;
use ACPT\Core\CQRS\Query\FetchElementsQuery;
use ACPT\Core\CQRS\Query\FetchFindQuery;
use ACPT\Core\CQRS\Query\FetchFormFieldsQuery;
use ACPT\Core\CQRS\Query\FetchFormPreviewElementQuery;
use ACPT\Core\CQRS\Query\FetchLanguagesQuery;
use ACPT\Core\CQRS\Query\FetchLicenseQuery;
use ACPT\Core\CQRS\Query\FetchMetaFieldsFromBelongsQuery;
use ACPT\Core\CQRS\Query\FetchPostTypeTaxonomiesQuery;
use ACPT\Core\CQRS\Query\FetchPreviewLinkQuery;
use ACPT\Core\CQRS\Query\FetchTableTemplatesQuery;
use ACPT\Core\CQRS\Query\GenerateGutenbergTemplateQuery;
use ACPT\Core\CQRS\Query\GenerateRelationalFieldOptionsQuery;
use ACPT\Core\Generators\Meta\FieldBlockGenerator;
use ACPT\Core\Generators\Meta\RepeaterFieldGenerator;
use ACPT\Core\Helper\Currencies;
use ACPT\Core\Helper\Id;
use ACPT\Core\Helper\Lengths;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Helper\Weights;
use ACPT\Core\Models\Form\FormSubmissionModel;
use ACPT\Core\Repository\ApiRepository;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\DatasetRepository;
use ACPT\Core\Repository\DynamicBlockRepository;
use ACPT\Core\Repository\FormRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\PermissionRepository;
use ACPT\Core\Repository\SettingsRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Core\ValueObjects\FormSubmissionDatumObject;
use ACPT\Includes\ACPT_Cache;
use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_DB_Tools;
use ACPT\Includes\ACPT_Plugin;
use ACPT\Integrations\Polylang\Helper\PolylangChecker;
use ACPT\Integrations\WPML\Helper\WPMLChecker;
use ACPT\Integrations\WPML\Helper\WPMLConfig;
use ACPT\Integrations\WPML\Provider\MetaFieldsProvider;
use ACPT\Utils\Cache\FlexibleFieldCache;
use ACPT\Utils\Cache\RepeaterFieldCache;
use ACPT\Utils\Checker\FieldsVisibilityLiveChecker;
use ACPT\Utils\Data\Meta;
use ACPT\Utils\Data\Sanitizer;
use ACPT\Utils\ExportCode\ExportCodeStrings;
use ACPT\Utils\Http\ACPTApiClient;
use ACPT\Utils\PHP\Browser;
use ACPT\Utils\PHP\IP;
use ACPT\Utils\PHP\Sluggify;
use ACPT\Utils\PHP\Twig;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Settings\Settings;
use ACPT\Utils\Wordpress\Roles;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPUtils;
use Psr\Cache\InvalidArgumentException;

/**
 * The admin ajax handler
 *
 * @since      1.0.0
 * @package    advanced-custom-post-type
 * @subpackage advanced-custom-post-type/admin
 * @author     Mauro Cassani <maurocassani1978@gmail.com>
 */
class ACPT_Ajax
{
	/**
	 * @throws \Exception
	 */
	public function globalSettingsAction()
	{
		try {
			$language = Settings::get('language', 'en_US');
			$font = Settings::get('font', 'Inter');
		} catch (\Exception $exception){
            do_action("acpt/error", $exception);

			$language = 'en_US';
			$font = 'Inter';
		}

		$languageQuery = new FetchLanguagesQuery();
		$languageEntries = $languageQuery->execute();

		$findQuery = new FetchAllFindBelongsQuery();
		$findEntries = $findQuery->execute();

		$globals = [
			"plugin_version" => ACPT_PLUGIN_VERSION,
            "site_url" =>  site_url(),
            "admin_url" => admin_url(),
            "ajax_url" => admin_url( 'admin-ajax.php' ),
            "rest_route_url" => "/?rest_route=/acpt/v1",
            "http_referer" => (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '',
            "language" => $language,
            "locale" => get_locale(),
			"font" => $font,
            "is_rtl" => is_rtl(),
			'find' => $findEntries,
			'roles' => Roles::get(),
			"browser" => Browser::getBrowser(),
            "license_is_valid" => ACPT_IS_LICENSE_VALID,
            "available_languages" => $languageEntries['languages'],
            "translations" =>  $languageEntries['translations'],
		    "uom" => [
		    	"currency" => Currencies::getSymbolList(),
                "length" => Lengths::getSymbolList(),
                "weight" => Weights::getSymbolList(),
		    ],
		];

		$settings = SettingsRepository::get();

		return wp_send_json([
			'globals' => $globals,
			'settings' => $settings,
		]);
	}

	public function healthCheckAction()
	{
		// version

		// | =====================================================|
		// | LEGACY FORMAT | get_option('acpt_version')           |
		// | =====================================================|
		// | NEW FORMAT    | get_option('acpt_current_version')   |
		// | =====================================================|

		$savedVersion = get_option('acpt_current_version') ?? oldACPTPluginVersion(get_option('acpt_version', 0));
		$versionCheck = ($savedVersion === ACPT_PLUGIN_VERSION) ? 'ok' : 'Saved version is not aligned';

		// cache
		$cacheCheck = 'ok';
		try {
			$isCacheEnabled = Settings::get('enable_cache', 1);

			if($isCacheEnabled == 1){
				$cacheDir = plugin_dir_path( __FILE__ ) . "../../cache";

				if(!is_dir($cacheDir)){
					$cacheCheck = 'The cache directory does not exists';
				} elseif(!is_writable($cacheDir)){
					$cacheCheck = 'The cache directory is not writable';
				}
			}
		} catch (\Exception $exception){
            do_action("acpt/error", $exception);
			$cacheCheck = $exception->getMessage();
		}

		// db
        try {
            $dbHealthCheck = ACPT_DB_Tools::healthCheck();
            $dbCheck = (empty($dbHealthCheck)) ? 'ok' : 'Missing tables or columns';
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
            $dbHealthCheck = $exception->getMessage();
            $dbCheck = $exception->getMessage();
        }

		// curl
        try {
            $curlCheckCommand = new PingACPTServerCommand();
            $curlCheck = $curlCheckCommand->execute();
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
            $curlCheck = $exception->getMessage();
        }

		return wp_send_json([
			'db' => $dbCheck,
            'db_health_check' => $dbHealthCheck,
			'curl' => $curlCheck,
			'version' => $versionCheck,
			'cache' => $cacheCheck,
		]);
	}

	public function runRepairAction()
	{
		try {
			// version
			$current_version = filemtime(plugin_dir_path( __FILE__ ) . "../../advanced-custom-post-type.php");
			update_option('acpt_version', $current_version, false);

			// cache
			$cacheDir = plugin_dir_path( __FILE__ ) . "../../cache";

			if(!is_dir($cacheDir)){
				mkdir($cacheDir, 0777, true);
			}

			if(!is_writable($cacheDir)){
				chmod($cacheDir, 0777);
			}

			// db
			if(ACPT_DB_Tools::repair(ACPT_DB_Tools::healthCheck()) === false){
				throw new \Exception("DB repair failed");
			}

			return wp_send_json([
				'success' => 'ok',
			]);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return wp_send_json([
				'error' => $exception->getMessage(),
			]);
		}
	}

	/**
	 * Assoc CPT to Taxonomy
	 */
	public function assocPostTypeToTaxonomyAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			try {
				$taxonomyId = TaxonomyRepository::getId($data['taxonomy']);

				foreach ($data['postTypes'] as $customPostType){
					$command = new AssocTaxonomyToCustomPostTypeCommand($customPostType['id'], $taxonomyId, $customPostType['checked']);
					$command->execute();
				}

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	/**
	 * Assoc Taxonomy to CPT
	 */
    public function assocTaxonomyToPostTypeAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            try {
                $postId = CustomPostTypeRepository::getId($data['postType']);

                foreach ($data['taxonomies'] as $taxonomy){
	                $command = new AssocTaxonomyToCustomPostTypeCommand($postId, $taxonomy['id'], $taxonomy['checked']);
	                $command->execute();
                }

                $return = [
                    'success' => true,
                ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                        'success' => false,
                        'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }
    }

    public function bulkActionsAction()
    {
	    if(isset($_POST['data'])) {
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if (!isset($data['action'])) {
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing action'
			    ]);
		    }

		    if (!isset($data['belongsTo'])) {
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing belongsTo'
			    ]);
		    }

		    if (!isset($data['elements'])) {
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing elements'
			    ]);
		    }

		    $action = $data['action'];
		    $elements = $data['elements'];
		    $belongsTo = $data['belongsTo'];

		    try {
		    	switch ($action){

		    	    // delete
				    case "delete":
				    	foreach ($elements as $element => $toDelete){
				    		if($toDelete){
							    switch ($belongsTo){

								    default:
								    case MetaTypes::CUSTOM_POST_TYPE:
									    $command = new DeleteCustomPostTypeCommand($element);
									    break;

								    case MetaTypes::TAXONOMY:
									    $command = new DeleteTaxonomyCommand($element);
									    break;

								    case MetaTypes::META:
									    $command = new DeleteMetaGroupCommand($element);
									    break;

								    case MetaTypes::OPTION_PAGE:
									    $command = new DeleteOptionPageCommand($element);
									    break;

                                    case "block":
                                        $command = new DeleteBlockCommand($element);
                                        break;

								    case "form":
									    $command = new DeleteFormCommand($element);
									    break;

								    case "dataset":
									    $command = new DeleteDatasetCommand($element);
									    break;

								    case "woo_product_data":
									    $command = new DeleteWooCommerceProductDataCommand($element);
								    	break;
							    }

							    if($command !== null){
                                    $command->execute();
                                }
						    }
					    }

                        break;

                    // duplicate
                    case "duplicate":
                        foreach ($elements as $element => $toDuplicate){
                            if($toDuplicate){
                                switch ($belongsTo){

                                    default:
                                    case MetaTypes::CUSTOM_POST_TYPE:
                                        $command = new DuplicateCustomPostTypeCommand($element);
                                        break;

                                    case MetaTypes::TAXONOMY:
                                        $command = new DuplicateTaxonomyCommand($element);
                                        break;

                                    case MetaTypes::META:
                                        $command = new DuplicateMetaGroupCommand($element);
                                        break;

                                    case MetaTypes::OPTION_PAGE:
                                        $command = new DuplicateOptionPageCommand($element);
                                        break;

                                    case "form":
                                        $command = new DuplicateFormCommand($element);
                                        break;

                                    case "dataset":
                                        $command = new DuplicateDatasetCommand($element);
                                        break;

                                    case "woo_product_data":
                                        $command = new DuplicateWooCommerceProductDataCommand($element);
                                        break;
                                }

                                if($command !== null){
                                    $command->execute();
                                }
                            }
                        }

                        break;
			    }

			    return wp_send_json([
				    'success' => true,
			    ]);

		    } catch ( \Exception $exception ) {

                do_action("acpt/error", $exception);

			    return wp_send_json([
				    'success' => false,
				    'error' => $e->getMessage()
			    ]);
		    }
	    }
    }

    public function cacheConnectionTestAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            try {
                $driver = $data['driver'];
                $config = $data['config'];
                $cache = new ACPT_Cache($driver, $config);
                $cache->testConnection();

                return wp_send_json([
                    "success" => true,
                ]);

            } catch ( \Exception $exception ) {

                do_action("acpt/error", $exception);

                return wp_send_json([
                    "success" => false,
                    "error" => $e->getMessage()
                ], 500);
            }
        }

        return wp_send_json([
            "success" => false,
            "error" => "Missing data"
        ], 500);
    }

    public function cacheStatsAction()
    {
        $query = new CacheStatsQuery();

        return wp_send_json($query->execute());
    }

    public function calculateShortCodeAction()
    {
	    if(isset($_POST['data'])) {
		    $data = $this->sanitizeJsonData($_POST['data']);

		    try {
			    $query = new CalculateShortCodeQuery( $data );

			    return wp_send_json($query->execute());
		    } catch ( \Exception $exception ) {

                do_action("acpt/error", $exception);

			    return wp_send_json([
				    "metaKey" => null,
				    "shortcodes" => []
			    ], 500);
		    }
	    }
    }

    public function checkIsVisibleAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    try {
		    return wp_send_json(
			    FieldsVisibilityLiveChecker::check(
				    Visibility::IS_BACKEND,
				    $data['elementId'],
				    $data['belongsTo'],
				    $data['values']
			    )
		    );
	    } catch (\Exception $exception){

            do_action("acpt/error", $exception);

		    return wp_send_json([]);
	    }
    }

	/**
	 * @throws \Exception
	 */
    public function checkMetaBoxNameAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    if (!isset($data['boxName'])) {
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing boxName'
		    ]);
	    }

	    $boxName = $data['boxName'];

	    return wp_send_json([
		    'exists' => MetaRepository::existsMetaBox([
		    	'boxName' => $boxName
		    ])
	    ]);
    }

	/**
	 * @throws \Exception
	 */
    public function checkMetaBoxFieldNameAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    if (!isset($data['boxName'])) {
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing boxName'
		    ]);
	    }

	    if (!isset($data['fieldName'])) {
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing fieldName'
		    ]);
	    }

	    $boxName = $data['boxName'];
	    $fieldName = $data['fieldName'];

	    return wp_send_json([
		    'exists' => MetaRepository::existsMetaBoxField([
			    'boxName' => $boxName,
			    'fieldName' => $fieldName,
		    ])
	    ]);
    }

    public function checkBlockNameAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if (!isset($data['name'])) {
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing block name'
                ]);
            }

            $name = $data['name'];

            return wp_send_json([
                'exists' => DynamicBlockRepository::exists($name)
            ]);
        }
    }

    /**
     * Check if a Custom post type exists
     *
     * @return mixed
     */
    public function checkPostTypeNameAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if (!isset($data['postType'])) {
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing postType'
                ]);
            }

            $postType = $data['postType'];

            return wp_send_json([
                    'exists' => CustomPostTypeRepository::exists($postType)
            ]);
        }
    }

    /**
     * Check if a Taxonomy exists
     *
     * @return mixed
     */
    public function checkTaxonomySlugAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if (!isset($data['slug'])) {
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing slug'
                ]);
            }

            $slug = $data['slug'];

            return wp_send_json([
                    'exists' => TaxonomyRepository::exists($slug)
            ]);
        }
    }

    public function checkTwigCodeAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if (!isset($data['string'])) {
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing string'
                ]);
            }

            $string = $data['string'];
            $attributes = $data['attributes'] ?? [];

            $checkErrors = Twig::checkErrors($string, $attributes);

            return wp_send_json([
                'success' => empty($checkErrors),
                'error' => $checkErrors,
            ]);
        }
    }

	public function copyMetaBoxAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			try {
				$command = new CopyMetaBoxCommand($data);
				$command->execute();

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function copyMetaBoxesAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['boxIds'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing boxIds'
				]);
			}

			try {
				foreach ($data['boxIds'] as $boxId){
					$command = new CopyMetaBoxCommand([
						'boxId' => $boxId,
						'targetGroupId' => $data['targetGroupId'],
						'delete' => $data['delete'],
					]);
					$command->execute();
				}


				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function copyMetaFieldAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			try {
				$command = new CopyMetaFieldCommand($data);
				$command->execute();

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function copyOptionPageAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			try {
				$command = new CopyOptionPageCommand($data);
				$command->execute();

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function copyOptionPagesAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['pageIds'])){
				return wp_send_json([
					'success' => false,
					'error' => '`pageIds` is missing'
				]);
			}

			if(!is_array($data['pageIds'])){
				return wp_send_json([
					'success' => false,
					'error' => '`pageIds` is not an array'
				]);
			}

			try {
				foreach ($data['pageIds'] as $pageId){
					$command = new CopyOptionPageCommand([
						'targetPageId' => $data['targetPageId'],
						'pageId' => $pageId,
						'deletePage' => $data['deletePage']
					]);
					$command->execute();
				}

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function copyMetaFieldsAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['fieldIds'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing fieldIds'
				]);
			}

			try {
				foreach ($data['fieldIds'] as $fieldId){
					$command = new CopyMetaFieldCommand([
						'fieldId' => $fieldId,
						'targetEntityId' => $data['targetEntityId'],
						'targetEntityType' => $data['targetEntityType'],
						'delete' => $data['delete'],
					]);
					$command->execute();

					$return = [
						'success' => true,
					];
				}
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function copyMetaBlockAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			try {
				$command = new CopyMetaBlockCommand($data);
				$command->execute();

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function copyMetaBlocksAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['blockIds'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing blockIds'
				]);
			}

			try {
				foreach ($data['blockIds'] as $blockId){
					$command = new CopyMetaBlockCommand([
						'blockId' => $blockId,
						'targetFieldId' => $data['targetFieldId'],
						'delete' => $data['delete'],
					]);
					$command->execute();

					$return = [
						'success' => true,
					];
				}

			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}
	}

	public function createPostAndLinkItAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['value'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing value'
                ]);
            }

            if(!isset($data['fieldId'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing fieldId'
                ]);
            }

            if(!isset($data['entityType'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing entityType'
                ]);
            }

            if(!isset($data['entityValue'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing entityValue'
                ]);
            }

            if(!isset($data['entityId'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing entityId'
                ]);
            }

            try {
                $value = $data['value'];
                $fieldId = $data['fieldId'];
                $entityType = $data['entityType'];
                $entityValue = $data['entityValue'];
                $entityId = $data['entityId'];
                $savedValues = $data['savedValues'];
                $metaField = MetaRepository::getMetaFieldById($fieldId);

                if(empty($metaField)){
                    return wp_send_json([
                        'success' => false,
                        'error' => 'Wrong field ID'
                    ]);
                }

                $command = new CreatePostAndLinkToARelationalFieldCommand($metaField, $value, $entityType, $entityValue, $entityId, $savedValues);
                $return = [
                    'success' => $command->execute(),
                ];

            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                    'success' => false,
                    'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }
    }

    /**
     * Delete API key
     *
     * @return mixed
     */
    public function deleteApiKeyAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['id'])){
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing id'
                ]);
            }

            $id = $data['id'];

            try {
                ApiRepository::delete($id);

                $return = [
                        'success' => true,
                ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                        'success' => false,
                        'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }

        return wp_send_json([
                'success' => false,
                'error' => 'no data was sent'
        ]);
    }

    /**
     * Delete API key
     *
     * @return mixed
     */
    public function deleteBlockAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['id'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing id'
                ]);
            }

            $id = $data['id'];

            try {
                DynamicBlockRepository::delete($id);

                $return = [
                    'success' => true,
                ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                    'success' => false,
                    'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }

        return wp_send_json([
            'success' => false,
            'error' => 'no data was sent'
        ]);
    }

    public function deleteCacheItemAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['key'])){
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing key'
                ]);
            }

            $key = $data['key'];

            try {
                $cache = ACPT_Plugin::getCache();
                $cache->deleteItem($key);

                $return = [
                    'success' => true,
                ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                    'success' => false,
                    'error' => $exception->getMessage()
                ];
            } catch ( InvalidArgumentException $exception ) {

                do_action("acpt/error", $exception);

                $return = [
                    'success' => false,
                    'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }

        return wp_send_json([
                'success' => false,
                'error' => 'no postType was sent'
        ]);
    }

    /**
     * Delete custom post type
     *
     * @return mixed
     */
    public function deleteCustomPostTypeAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['postType'])){
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing postType'
                ]);
            }

            $postType = $data['postType'];

            try {
				$command = new DeleteCustomPostTypeCommand($postType);
	            $command->execute();

                $return = [
                        'success' => true,
                ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                        'success' => false,
                        'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }

        return wp_send_json([
                'success' => false,
                'error' => 'no postType was sent'
        ]);
    }

	/**
	 * Delete custom post type
	 *
	 * @return mixed
	 */
	public function deleteDatasetAction()
	{
		if(isset($_POST['data'])){
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['id'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing id'
				]);
			}

			$id = $data['id'];

			try {
				$command = new DeleteDatasetCommand($id);
				$command->execute();

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}

		return wp_send_json([
			'success' => false,
			'error' => 'no postType was sent'
		]);
	}

	/**
	 * Delete custom post type
	 *
	 * @return mixed
	 */
	public function deleteFormAction()
	{
		if(isset($_POST['data'])){
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['id'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing id'
				]);
			}

			$id = $data['id'];

			try {
				$command = new DeleteFormCommand($id);
				$command->execute();

				$return = [
					'success' => true,
				];
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				$return = [
					'success' => false,
					'error' => $exception->getMessage()
				];
			}

			return wp_send_json($return);
		}

		return wp_send_json([
			'success' => false,
			'error' => 'no postType was sent'
		]);
	}

    /**
     * Delete a meta group
     *
     * @return mixed
     */
    public function deleteMetaAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);

	        if(!isset($data['id'])){
		        return wp_send_json([
			        'success' => false,
			        'error' => 'Missing group `id`'
		        ]);
	        }

	        $id = $data['id'];

            try {
            	$command = new DeleteMetaGroupCommand($id);
	            $command->execute();

                $return = [
                        'success' => true,
                ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                        'success' => false,
                        'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }

        return wp_send_json([
                'success' => false,
                'error' => 'no postType was sent'
        ]);
    }

    public function deletePageAction()
    {
	    if(isset($_POST['data'])){
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if(!isset($data['id'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing id'
			    ]);
		    }

		    try {
			    $command = new DeleteOptionPageCommand($data['id']);
			    $command->execute();

			    $return = [
				    'success' => true,
			    ];
		    } catch (\Exception $exception){

                do_action("acpt/error", $exception);

			    $return = [
				    'success' => false,
				    'error' => $exception->getMessage()
			    ];
		    }

		    return wp_send_json($return);
	    }

	    return wp_send_json([
		    'success' => false,
		    'error' => 'no data was sent'
	    ]);
    }

    public function deleteOptionPagesAction()
    {
	    try {
		    OptionPageRepository::deleteAll();

		    $return = [
			    'success' => true,
		    ];
	    } catch (\Exception $exception){

            do_action("acpt/error", $exception);

		    $return = [
			    'success' => false,
			    'error' => $exception->getMessage()
		    ];
	    }

	    return wp_send_json($return);
    }

    public function deleteTableTemplateAction()
    {
	    if(isset($_POST['data'])){
		    try {
			    $data = $this->sanitizeJsonData($_POST['data']);
			    $id = $data['id'];
			    $command = new DeleteTableTemplateCommand($id);
			    $command->execute();

			    $return = [
				    'success' => true,
			    ];
		    } catch (\Exception $exception){

                do_action("acpt/error", $exception);

			    $return = [
				    'success' => false,
				    'error' => $exception->getMessage()
			    ];
		    }

		    return wp_send_json($return);
	    }

	    return wp_send_json([
		    'success' => false,
		    'error' => 'no data was sent'
	    ]);
    }

    /**
     * Delete a taxonomy
     *
     * @return mixed
     */
    public function deleteTaxonomyAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['taxonomy'])){
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing taxonomy'
                ]);
            }

            $taxonomy = $data['taxonomy'];

            try {
                $command = new DeleteTaxonomyCommand($taxonomy);
                $command->execute();

                $return = [
                        'success' => true,
                ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $return = [
                        'success' => false,
                        'error' => $exception->getMessage()
                ];
            }

            return wp_send_json($return);
        }

        return wp_send_json([
                'success' => false,
                'error' => 'no taxonomy was sent'
        ]);
    }

    public function duplicateAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        if(!isset($data['find'])){
            return wp_send_json([
                'success' => false,
                'error' => 'Missing `find`'
            ]);
        }

        if(!isset($data['belongsTo'])){
            return wp_send_json([
                'success' => false,
                'error' => 'Missing `belongsTo`'
            ]);
        }

        try {
            $belongsTo = $data['belongsTo'];
            $element = $data['find'];

            switch ($belongsTo){

                default:
                case MetaTypes::CUSTOM_POST_TYPE:
                    $command = new DuplicateCustomPostTypeCommand($element);
                    break;

                case MetaTypes::TAXONOMY:
                    $command = new DuplicateTaxonomyCommand($element);
                    break;

                case MetaTypes::META:
                    $command = new DuplicateMetaGroupCommand($element);
                    break;

                case MetaTypes::OPTION_PAGE:
                    $command = new DuplicateOptionPageCommand($element);
                    break;

                case "block":
                    $command = new DuplicateBlockCommand($element);
                    break;

                case "form":
                    $command = new DuplicateFormCommand($element);
                    break;

                case "dataset":
                    $command = new DuplicateDatasetCommand($element);
                    break;

                case "woo_product_data":
                    $command = new DuplicateWooCommerceProductDataCommand($element);
                    break;
            }

            if($command !== null){
                $command->execute();
            }

            $return = [
                'success' => true,
            ];

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $return = [
                'success' => false,
                'error' => $exception->getMessage()
            ];
        }

        return wp_send_json($return);
    }

    /**
     * @return mixed
     */
    public function doShortcodeAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        if(!isset($data['shortcode'])){
            return wp_send_json([
                    'success' => false,
                    'error' => 'Missing taxonomy'
            ]);
        }

        $shortcode = $data['shortcode'];

        return wp_send_json([
                'success' => true,
                'data' => WPUtils::renderShortCode($shortcode)
        ]);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function exportFileAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

	    if(!isset($data['format'])){
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing format'
		    ]);
	    }

	    if(!isset($data['data'])){
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing data to export'
		    ]);
	    }

	    $format = $data['format'];
	    $data = $data['data'];

        $command = new ExportDataCommand($format, $data);

        return wp_send_json([
            'success' => true,
            'data' => $command->execute()
        ]);
    }

	/**
	 * @throws \Exception
	 */
    public function fetchMetaFieldsFromBelongsToAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    if(!isset($data['belongsTo']) and !isset($data['find'])){
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing params (`belongsTo`)'
		    ]);
	    }

	    $belongsTo = $data['belongsTo'];
	    $find = $data['find'];

	    $query = new FetchMetaFieldsFromBelongsQuery($belongsTo, $find);

	    return wp_send_json($query->execute());
    }

	/**
	 * @throws \Exception
	 */
    public function fetchAllFindBelongsAction()
    {
	    $query = new FetchAllFindBelongsQuery();

	    return wp_send_json($query->execute());
    }

	/**
	 * @throws \Exception
	 */
    public function fetchFindAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    if(!isset($data['belongsTo']) and !isset($data['id'])){
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing params (`id, belongsTo`)'
		    ]);
	    }

	    $id = $data['id'];
	    $belongsTo = $data['belongsTo'];

	    $command = new FetchFindQuery($id, $belongsTo);

        return wp_send_json($command->execute());
    }

	/**
	 * @throws \Exception
	 */
    public function fetchFindFromBelongsToAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    if(!isset($data['belongsTo'])){
		    return wp_send_json([
			    'success' => false,
			    'error' => 'Missing params (`belongsTo`)'
		    ]);
	    }

	    $belongsTo = $data['belongsTo'];
	    $format = $data['format'] ? $data['format'] : 'default';

	    $command = new FetchMetaFieldsFromBelongsQuery($belongsTo, $format);

	    return wp_send_json($command->execute());
    }

	/**
	 * Fetch form fields
	 */
    public function fetchFormFieldsAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    try {
		    if(!isset($data['id'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing params (`id`)'
			    ]);
		    }

		    $id = $data['id'];
		    $command = new FetchFormFieldsQuery($id);

		    return wp_send_json($command->execute());

	    } catch (\Exception $exception){

            do_action("acpt/error", $exception);

		    return wp_send_json([
			    'success' => false,
			    'error' => $exception->getMessage(),
		    ]);
	    }
    }

	/**
	 * Fetch preview link
	 */
    public function fetchPreviewLinkAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        try {
	        $query = new FetchPreviewLinkQuery($data);

	        return wp_send_json([
	        	'success' => true,
		        'link' => $query->execute(),
	        ]);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

	        return wp_send_json([
		        'success' => false,
		        'error' => $exception->getMessage(),
	        ]);
        }
    }

	public function fetchMetaFieldsFlatMapAction()
	{
		$data = $this->sanitizeJsonData($_POST['data']);

		if(!isset($data['excludeField'])){
			return wp_send_json([
				'success' => false,
				'error' => 'Missing `excludeField`'
			]);
		}

		return wp_send_json(MetaRepository::metaFieldsFlatArray($data['excludeField']));
	}

    /**
     * Fetch custom post type meta
     *
     * @return mixed
     * @throws \Exception
     */
    public function fetchMetaAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        if(empty($data)){
        	$data = [];
        }

        // Single field fetch
        if(isset($data['id'])){

        	$record = MetaRepository::get([
		        'id' => $data['id'],
		        'formBuilder' => $data['formBuilder'] ?? false,
		        'excludeField' => ((isset($data['excludeField'])) ? $data['excludeField'] : null),
	        ]);

        	if(!empty($record)){
        		return wp_send_json($record[0]);
	        }

        	return wp_send_json([]);
        }

        // user fields
	    if(isset($data['belongsTo']) and $data['belongsTo'] === MetaTypes::USER){
	    	unset($data['find']);
	    }

	    // media fields
        if(isset($data['find']) and $data['find'] === 'attachment'){
            $data['belongsTo'] = MetaTypes::MEDIA;
            unset($data['find']);
        }

	    return wp_send_json([
		    'count' => MetaRepository::count(),
		    'records' => MetaRepository::get($data),
	    ]);
    }

    /**
     * Fetch meta field by id
     *
     * @return mixed
     * @throws \Exception
     */
    public function fetchMetaFieldAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        if(!isset($data['id'])){
            return wp_send_json([
                    'success' => false,
                    'error' => 'Missing id'
            ]);
        }

        $id = $data['id'];
        $lazy = isset($data ['lazy']) ? $data ['lazy'] : false;

        return wp_send_json(MetaRepository::getMetaFieldById($id, $lazy));
    }

    public function fetchMetaValueAction()
    {
	    if(isset($_POST['data'])){
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if(!isset($data['fieldName'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => "Missing `fieldName` field"
			    ]);
		    }

		    if(!isset($data['objectType'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => "Missing `objectType` field"
			    ]);
		    }

		    if(!isset($data['objectId'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => "Missing `objectId` field"
			    ]);
		    }

		    $fieldName = $data['fieldName'];
		    $objectId = $data['objectId'];
		    $objectType = $data['objectType'];

		    return wp_send_json([
			    'value' => Meta::fetch($objectId, $objectType, $fieldName)
		    ]);
	    }
    }

    /**
     * Fetch dynamic blocks
     *
     * @throws \Exception
     */
    public function fetchBlocksAction()
    {
        $id = null;

        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);
            $id = isset($data['id']) ? $data['id'] : null;
            $page = isset($data['page']) ? $data['page'] : null;
            $perPage = isset($data['perPage']) ? $data['perPage'] : null;
        }

        if($id){
            return wp_send_json(DynamicBlockRepository::get([
                'id' => $id
            ]));
        }

        return wp_send_json([
            'count' => DynamicBlockRepository::count(),
            'records' => DynamicBlockRepository::get([
                'page' => isset($page) ? $page : 1,
                'perPage' => isset($perPage) ? $perPage : 20,
            ]),
        ]);
    }

    /**
     * Fetch custom post types
     *
     * @return mixed
     * @throws \Exception
     */
    public function fetchCustomPostTypesAction()
    {
        $postType = null;

        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);
            $postType = isset($data['postType']) ? $data['postType'] : null;
            $page = isset($data['page']) ? $data['page'] : null;
            $perPage = isset($data['perPage']) ? $data['perPage'] : null;
        }

        if($postType){
            return wp_send_json(CustomPostTypeRepository::get([
                    'postType' => $postType
            ]));
        }

        return wp_send_json([
        	'count' => CustomPostTypeRepository::count(),
	        'records' => CustomPostTypeRepository::get([
		        'page' => isset($page) ? $page : 1,
		        'perPage' => isset($perPage) ? $perPage : 20,
	        ]),
        ]);
    }

    public function fetchHeadersAndFootersAction()
    {
        $directory = get_template_directory();

        $headers = array_merge(glob($directory."/header.php"), glob($directory."/header-*.php"));
        $footers = array_merge(glob($directory."/footer.php"), glob($directory."/footer-*.php"));

        foreach ($headers as $index => $header){
            $headers[$index] = $this->cleanHeadersAndFootersName($header);
        }

        foreach ($footers as $index => $footer){
            $footers[$index] = $this->cleanHeadersAndFootersName($footer);
        }

        return wp_send_json([
                'headers' => $headers,
                'footers' => $footers,
        ]);
    }

	/**
	 * Flush cache
	 */
	public function flushCacheAction()
	{
		try {
			$flushCache = ACPT_DB::flushCache();

			if($flushCache){
				return wp_send_json([
					'success' => true,
				]);
			}

			return wp_send_json([
				'success' => false,
				'error' => "Unknown error",
			]);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return wp_send_json([
				'success' => false,
				'error' => $exception->getMessage(),
			]);
		}
	}

    /**
     * @return mixed
     * @throws \Exception
     */
    public function fetchLicenseAction()
    {
        if(!ACPT_IS_LICENSE_VALID){

            $referer = json_encode([
                    'site' => site_url(),
                    'siteName' => get_bloginfo('name'),
                    'ip' => IP::getClientIP(),
            ]);

            $link = "https://acpt.io/activation?referer=".base64_encode($referer);

            return wp_send_json([
                "code" => null,
                "expiring_date" => null,
                "is_valid" => false,
                "max_activations" => null,
                "activations_count" => null,
                "is_dev" => false,
                "is_multiuser" => false,
                "currentVersion" => ACPT_PLUGIN_VERSION,
                "licenseActivation" => null,
                'activationLink' => $link,
            ]);
        }

        try {
	        $query = new FetchLicenseQuery();

	        return wp_send_json($query->execute());
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

	        return wp_send_json([
		        'error' => $exception->getMessage()
	        ], 500);
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function fetchLogsAction()
    {
        if(!ACPT_ENABLE_LOG){
            return wp_send_json([]);
        }

        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);
            $date = $data['date'] ?? (new \DateTime())->format("Y-m-d");

            $logsFile = WP_CONTENT_DIR . "/acpt-logs/log-{$date}.json";

            if(!file_exists($logsFile)){
                return wp_send_json([]);
            }

            return wp_send_json(json_decode(file_get_contents($logsFile)));
        }

        return wp_send_json([]);
    }

    public function fetchChangelogAction(   )
    {
        try {
            $query = new FetchChangelogQuery();

            return wp_send_json($query->execute());
        } catch (\Exception $exception){
            return wp_send_json([]);
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function deactivateLicenseAction()
    {
        try {
            if(!ACPT_IS_LICENSE_VALID){
                throw new \Exception( 'License is not valid');
            }

            $licenseActivation = ACPT_License_Manager::getLicense();
            $deactivation = ACPTApiClient::call('/license/deactivate', [
                'id' => $licenseActivation['activation_id'],
            ]);

            if(!isset($deactivation['id'])){
                $message = 'An unexpected error has occurred while connecting to the ACPT servers. You must deactivate this license on your ACPT dashboard.';
            } else {
                $message = 'License successfully deactivated.';
            }

            ACPT_License_Manager::destroy();

            return wp_send_json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return wp_send_json([
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * @param $string
     *
     * @return string|string[]
     */
    private function cleanHeadersAndFootersName( $string)
    {
        $directory = get_template_directory();

        return str_replace([$directory, '/', '.php'],'', $string);
    }

	/**
	 * @throws \Exception
	 */
    public function exportCodeAction()
    {
	    if(isset($_POST['data'])){
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if(!isset($data['find']) and !isset($data['belongsTo'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing params (`find`, `belongsTo`)'
			    ], 500);
		    }

		    return wp_send_json(ExportCodeStrings::export($data['belongsTo'], $data['find']));
	    }

	    return wp_send_json([
		    'success' => false,
		    'error' => 'no data was sent'
	    ], 500);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function fetchSettingsAction()
    {
        return wp_send_json(SettingsRepository::get());
    }



    /**
     * Fetch Api keys
     *
     * @return mixed
     * @throws \Exception
     */
    public function fetchApiKeysAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);
            $page = isset($data['page']) ? $data['page'] : null;
            $perPage = isset($data['perPage']) ? $data['perPage'] : null;
        }

        $count = ApiRepository::count([
	        'uid' => get_current_user_id(),
        ]);

	    $keys = ApiRepository::getPaginated([
		    'uid' => get_current_user_id(),
		    'page' => isset($page) ? $page : 1,
		    'perPage' => isset($perPage) ? $perPage : 20,
	    ]);

	    return wp_send_json([
		    'count' => $count,
		    'records' => $keys,
	    ]);
    }

	/**
	 * Fetch option page
	 *
	 * @throws \Exception
	 */
    public function fetchOptionPageAction()
    {
	    if(isset($_POST['data'])){
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if(!isset($data['slug'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => 'no slug were sent'
			    ], 500);
		    }

		    return wp_send_json(OptionPageRepository::getByMenuSlug($data['slug']));
	    }

	    return wp_send_json([
		    'success' => false,
		    'error' => 'no params were sent'
	    ], 500);
    }

	/**
	 * Fetch paginated option pages
	 *
	 * @throws \Exception
	 */
	public function fetchOptionPagesAction()
	{
		if(isset($_POST['data'])){
			$data = $this->sanitizeJsonData($_POST['data']);
			$page = isset($data['page']) ? $data['page'] : null;
			$perPage = isset($data['perPage']) ? $data['perPage'] : null;
		}

		return wp_send_json([
			'count' => OptionPageRepository::count(),
			'records' => OptionPageRepository::get([
				'page' => isset($page) ? $page : 1,
				'perPage' => isset($perPage) ? $perPage : 20,
			]),
		]);
	}

	/**
	 * Fetch OP meta fields
	 * Used by FSE
	 */
	public function fetchAllMetaAction()
	{
		try {
			$groups = [
				MetaTypes::CUSTOM_POST_TYPE => [],
				MetaTypes::TAXONOMY => [],
				MetaTypes::OPTION_PAGE => [],
			];

			$customPostTypes = CustomPostTypeRepository::get([]);
			foreach ($customPostTypes as $customPostTypeModel){
				$groups[MetaTypes::CUSTOM_POST_TYPE][$customPostTypeModel->getName()] = MetaRepository::get([
					'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
					'find' => $customPostTypeModel->getName(),
                    'clonedFields' => true,
				]);
			}

			$taxonomies = TaxonomyRepository::get([]);
			foreach ($taxonomies as $taxonomy){
				$groups[MetaTypes::TAXONOMY][$taxonomy->getSlug()] = MetaRepository::get([
					'belongsTo' => MetaTypes::TAXONOMY,
					'find' => $taxonomy->getSlug(),
                    'clonedFields' => true,
				]);
			}

			$optionPageSlugs = OptionPageRepository::getAllSlugs();
			foreach ($optionPageSlugs as $optionPageSlug){
				$groups[MetaTypes::OPTION_PAGE][$optionPageSlug] = MetaRepository::get([
					'belongsTo' => MetaTypes::OPTION_PAGE,
					'find' => $optionPageSlug,
                    'clonedFields' => true,
				]);
			}

			return wp_send_json($groups);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return wp_send_json([
				'success' => false,
				'error' => $exception->getMessage()
			], 500);
		}
	}

	/**
	 * Fetch OP meta fields
	 */
	public function fetchOptionPagesMetaAction()
	{
		try {
			$groups = [];
			$optionPageSlugs = OptionPageRepository::getAllSlugs();

			foreach ($optionPageSlugs as $optionPageSlug){
				$groups[$optionPageSlug] = MetaRepository::get([
					'belongsTo' => MetaTypes::OPTION_PAGE,
					'find' => $optionPageSlug
				]);
			}

			return wp_send_json($groups);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return wp_send_json([
				'success' => false,
				'error' => $exception->getMessage()
			], 500);
		}
	}

	public function fetchDatasetsAction()
	{
		if(isset($_POST['data'])){
			$data = $this->sanitizeJsonData($_POST['data']);
			$page = isset($data['page']) ? $data['page'] : null;
			$perPage = isset($data['perPage']) ? $data['perPage'] : null;
		}

		if(isset($data['id'])){
			return wp_send_json(
				DatasetRepository::getById($data['id'])
			);
		}

		return wp_send_json([
			'count' => DatasetRepository::count(),
			'records' => DatasetRepository::get([
				'page' => isset($page) ? $page : 1,
				'perPage' => isset($perPage) ? $perPage : 20,
			]),
		]);
	}

	/**
	 * Fetch paginated forms
	 *
	 * @throws \Exception
	 */
	public function fetchFormsAction()
	{
		if(isset($_POST['data'])){
			$data = $this->sanitizeJsonData($_POST['data']);
			$page = isset($data['page']) ? $data['page'] : null;
			$perPage = isset($data['perPage']) ? $data['perPage'] : null;
		}

		return wp_send_json([
			'count' => FormRepository::count(),
			'records' => FormRepository::get([
				'page' => isset($page) ? $page : 1,
				'perPage' => isset($perPage) ? $perPage : 20,
			]),
		]);
	}

	public function fetchFormPreviewElementAction()
	{
		if(isset($_POST['data'])){
			try {
				$data = $this->sanitizeJsonData($_POST['data']);

				if(!isset($data['field'])){
					return wp_send_json([
						'value' => null,
					]);
				}

				$field = $data['field'];
				$previewElement = $data['previewElement'] ?? null;
				$query = new FetchFormPreviewElementQuery($field, $previewElement);

				return wp_send_json([
					'value' => $query->execute()
				]);
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				return wp_send_json([
					'value' => null,
				]);
			}
		}

		return wp_send_json([
			'value' => null,
		]);
	}

	public function fetchFormSubmissionsAction()
	{
		try {
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['id'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing form id'
				], 500);
			}

			$formId = $data['id'];
			$page = isset($data['page']) ? $data['page'] : 1;
			$perPage = isset($data['perPage']) ? $data['perPage'] : 20;

			return wp_send_json([
				'count' => FormRepository::getSubmissionsCount($formId),
				'records' => FormRepository::getSubmissions($formId, $page, $perPage),
			]);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			return wp_send_json([
				'success' => false,
				'error' => $exception->getMessage()
			], 500);
		}
	}

    /**
     * Fetch API key count (by uid)
     *
     * @return mixed
     */
    public function fetchApiKeysCountAction()
    {
        return wp_send_json(ApiRepository::count([
                'uid' => get_current_user_id(),
        ]));
    }

	/**
	 * fetch CPTs or Taxonomies
	 * (used by copy meta box/field UI)
	 *
	 * @return mixed
	 * @throws \Exception
	 */
    public function fetchElementsAction()
    {
	    if(isset($_POST['data'])){
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if(!isset($data['belongsTo'])){
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing belongsTo'
			    ], 500);
		    }

		    $belongsTo = $data['belongsTo'];
		    $exclude = (isset($data['exclude'])) ? $data['exclude'] : null;

		    $query = new FetchElementsQuery($belongsTo, $exclude);

		    return wp_send_json($query->execute());
	    }

	    return wp_send_json([
		    'success' => false,
		    'error' => 'no params were sent'
	    ], 500);
    }

    /**
     * Fetch taxonomies
     *
     * @return mixed
     * @throws \Exception
     */
    public function fetchTaxonomiesAction()
    {
        $taxonomy = null;
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);
            $taxonomy = isset($data['taxonomy']) ? $data['taxonomy'] : null;
            $page = isset($data['page']) ? $data['page'] : null;
            $perPage = isset($data['perPage']) ? $data['perPage'] : null;
        }

        if($taxonomy){
            return wp_send_json(TaxonomyRepository::get([
                    'taxonomy' => $taxonomy
            ]));
        }

        return wp_send_json([
        	'count' => TaxonomyRepository::count(),
        	'records' => TaxonomyRepository::get([
		        'page' => isset($page) ? $page : 1,
		        'perPage' => isset($perPage) ? $perPage : 20,
	        ]),
        ]);
    }

    public function fetchTableTemplatesAction()
    {
    	$query = new FetchTableTemplatesQuery();

    	return wp_send_json($query->execute());
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function fetchTermsAction()
    {
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['taxonomy'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing taxonomy'
                ], 500);
            }

            $terms = get_terms([
                'taxonomy' => $data['taxonomy'],
                'hide_empty' => false,
            ]);

            if(isset($data['format']) and $data['format'] === 'short'){

	            $data = [];

	            foreach ($terms as $term){
                    $data[] = ["label" => $term->name, "value" => (int)$term->term_id];
                }

	            return wp_send_json($data);
            }

            return wp_send_json($terms);
        }

        return wp_send_json([
            'success' => false,
            'error' => 'no data was sent'
        ], 500);
    }

    /**
     * Generate an API key
     *
     * @return mixed
     * @throws \Exception
     */
    public function generateApiKeyAction()
    {
        try {
            $command = new GenerateApiKeyCommand(get_current_user_id());
            $apiKeyModel = $command->execute();

            return wp_send_json([
                'success' => true,
                'data' => [
                    'key' => $apiKeyModel->getKey(),
                    'secret' => $apiKeyModel->getSecret(),
                ]
            ]);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return wp_send_json([
                    'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function generateIdAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['strategy'])){
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing `strategy` params'
                ], 500);
            }

            $strategy = $data['strategy'];
            $index    = $data['index'] ?? null;

            try {
                return wp_send_json([
                    'id' => Id::generate($strategy, $index)
                ]);
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                return wp_send_json([
                    'error' => $exception->getMessage()
                ], 500);
            }
        }
    }

    public function generateEmbedAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['value'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing `value` params'
                ], 500);
            }

            $value = $data['value'];

            if(!Url::isValid($value)){
                return wp_send_json([
                    'success' => false,
                    'error' => '`value` is not a valid URL'
                ], 500);
            }

            $embed = (new \WP_Embed())->shortcode([
                'width' => 180,
                'height' => 135,
            ], Url::sanitize($value));

            return wp_send_json([
                'success' => true,
                'embed' => $embed
            ]);
        }
    }

	/**
	 * @return mixed
	 */
    public function generateFlexibleBlockAction()
    {
	    if(isset($_POST['data'])) {
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if(!isset($data['blockId']) and
		       !isset($data['parentName']) and
		       !isset($data['mediaType']) and
		       !isset($data['index']) and
		       !isset($data['blockListId'])
		    ){
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing `blockListId` or `blockId` or `parentName` or `index` or `mediaType` params'
			    ], 500);
		    }

		    $blockListId = $data['blockListId'];
		    $blockId = $data['blockId'];
		    $mediaType = $data['mediaType'];
		    $parentName = $data['parentName'];
		    $index = $data['index'];
		    $layout = $data['layout'];
		    $minBlocks = isset($data['minBlocks']) ? $data['minBlocks'] : null;
		    $maxBlocks = isset($data['maxBlocks']) ? $data['maxBlocks'] : null;

		    try {
			    $metaBlock = MetaRepository::getMetaBlockById($blockId);

			    if(null === $metaBlock){
				    return wp_send_json([
					    'success' => false,
					    'error' => 'No meta block found'
				    ], 500);
			    }

			    $repeaterFieldGenerator = new FieldBlockGenerator(
			    	$metaBlock,
				    $index,
				    $parentName,
				    $mediaType,
				    $layout,
				    $minBlocks,
				    $maxBlocks
			    );

			    return wp_send_json([
				    'block' => $repeaterFieldGenerator->generate($blockListId, $index)
			    ]);

		    } catch (\Exception $exception){

                do_action("acpt/error", $exception);

			    return wp_send_json([
				    'success' => false,
				    'error' => $exception->getMessage()
			    ], 500);
		    }
	    }

	    return wp_send_json([
		    'success' => false,
		    'error' => 'no data was sent'
	    ], 500);
    }

    /**
     * User by relational fields
     */
    public function generateRelationalFieldOptionsAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);
            $args = [];

            $id = $data['id'] ?? null;
            $fieldType = $data['fieldType'] ?? null;
            $defaultValues = $data['defaultValues'] ?? [];
            $layout = $data['layout'] ?? null;
            $format = $data['format'] ?? 'html';

            if(isset($data['toType'])){
                $args['toType'] = $data['toType'];
            }

            if(isset($data['toValue'])){
                $args['toValue'] = $data['toValue'];
            }

            if(isset($data['postType'])){
                $args['post_type'] = $data['postType'];
            }

            if(isset($data['postStatus'])){
                $args['post_status'] = $data['postStatus'];
            }

            if(isset($data['postTaxonomy'])){
                $args['post_taxonomy'] = $data['postTaxonomy'];
            }

            if(isset($data['termTaxonomy'])){
                $args['term_taxonomy'] = $data['termTaxonomy'];
            }

            if(isset($data['userRole'])){
                $args['user_role'] = $data['userRole'];
            }

            if(empty($fieldType)){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing `fieldType` param'
                ], 500);
            }

            $query = new GenerateRelationalFieldOptionsQuery($fieldType, $args, $format, $defaultValues, $layout, $id);

            return wp_send_json([
                'fields' => $query->execute(),
            ]);
        }

        return wp_send_json([
                'success' => false,
                'error' => 'no data was sent'
        ], 500);
    }

    /**
     * @throws \Exception
     */
	public function generateFlexibleGroupedFieldsAction()
	{
		if(isset($_POST['data'])) {
			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['blockId']) and !isset($data['mediaType']) and !isset($data['elementIndex']) and !isset($data['blockIndex'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing `fieldId` or `blockId` or `index` or `mediaType` params'
				], 500);
			}

			$blockId = $data['blockId'];
			$mediaType = $data['mediaType'];
			$parentName = $data['parentName'];
			$elementIndex = $data['elementIndex'];
			$blockIndex = $data['blockIndex'];
			$realBlockId = 'block-elements-'.$blockId.'-'.$blockIndex;
			$layout = $data['layout'];
			$minBlocks = isset($data['minBlocks']) ? $data['minBlocks'] : null;
			$maxBlocks = isset($data['maxBlocks']) ? $data['maxBlocks'] : null;

            $cache = new FlexibleFieldCache();

            // Check for a cached version
            $cachedTemplate = $cache->get(
                $blockId,
                $mediaType,
                $parentName,
                $elementIndex,
                $blockIndex,
                $realBlockId
            );

            if($cachedTemplate !== null){
                return wp_send_json([
                    'fields' => $cachedTemplate
                ]);
            }

			try {
				$metaBlock = MetaRepository::getMetaBlockById($blockId);

				if(null === $metaBlock){
					return wp_send_json([
						'success' => false,
						'error' => 'No meta block found'
					], 500);
				}

				$repeaterFieldGenerator = new FieldBlockGenerator(
					$metaBlock,
					$blockIndex,
					$parentName,
					$mediaType,
					$layout,
					$minBlocks,
					$maxBlocks
				);

                $fields = $repeaterFieldGenerator->generateElement($elementIndex, $realBlockId, $blockIndex);
                $cache->save(
                    $fields,
                    $blockId,
                    $mediaType,
                    $parentName,
                    $elementIndex,
                    $blockIndex,
                    $realBlockId
                );

				return wp_send_json([
					'fields' => $repeaterFieldGenerator->generateElement($elementIndex, $realBlockId, $blockIndex)
				]);

			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				return wp_send_json([
					'success' => false,
					'error' => $exception->getMessage()
				], 500);
			}
		}

		return wp_send_json([
			'success' => false,
			'error' => 'no data was sent'
		], 500);
	}

	/**
	 * Generate a template for Gutenberg inner blocks
	 */
	public function generateGutenbergTemplateAction()
	{
		if(isset($_POST['data'])) {

			$data = $this->sanitizeJsonData($_POST['data']);

			if(!isset($data['field'])){
				return wp_send_json([
					'success' => false,
					'error' => 'Missing `field`'
				], 500);
			}

			$attributes = $data['attributes'] ?? [];

			try {
				$query = new GenerateGutenbergTemplateQuery($data['field'], $data['contextId'], $attributes);
				$template = $query->execute();

				return wp_send_json($template);

			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				return wp_send_json([
					'success' => false,
					'error' => $exception->getMessage()
				], 500);
			}
		}

		return wp_send_json([
			'success' => false,
			'error' => 'no data was sent'
		], 500);
	}

    /**
     * @return mixed|void|null
     * @throws \Exception
     */
    public function generateGroupedFieldsAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

	        if(!isset($data['id']) and !isset($data['mediaType'])){
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing `id` or `mediaType` params'
                ]);
            }

            $id = $data['id'];
	        $mediaType = $data['mediaType'];
            $parentIndex = $data['parentIndex'];
            $parentName = $data['parentName'];
            $index = $data['index'];
            $formId = $data['formId'] ?? null;

            $cache = new RepeaterFieldCache();

            // Check for a cached version
            $cachedTemplate = $cache->get(
                $id,
                $mediaType,
                $parentIndex,
                $parentName,
                $index,
                $formId
            );

            if($cachedTemplate !== null){
                return wp_send_json([
                    'fields' => $cachedTemplate
                ]);
            }

            try {
                $metaField = MetaRepository::getMetaFieldById($id);

                if(null === $metaField){
                    return wp_send_json([
                            'success' => false,
                            'error' => 'No meta field found'
                    ], 500);
                }

                $layout = $data['layout'] ?? $metaField->getAdvancedOption('layout') ?? 'row';
                $leadingFieldId = $metaField->getAdvancedOption('leading_field');

                $repeaterFieldGenerator = new RepeaterFieldGenerator(
                	$metaField,
	                $parentName,
	                $parentIndex,
	                $mediaType,
	                $layout,
	                $leadingFieldId,
                    null,
                    $formId
                );

                $fields = $repeaterFieldGenerator->generate($index);
                $cache->save(
                    $fields,
                    $id,
                    $mediaType,
                    $parentIndex,
                    $parentName,
                    $index,
                    $formId
                );

                return wp_send_json([
                    'fields' => $fields
                ]);

            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                return wp_send_json([
                        'success' => false,
                        'error' => $exception->getMessage()
                ], 500);
            }
        }

        return wp_send_json([
            'success' => false,
            'error' => 'no data was sent'
        ], 500);
    }

    public function importDatasetAction()
    {
	    if(empty($_FILES)){
		    return wp_send_json([
			    'error' => Translator::translate('No files uploaded')
		    ], 500);
	    }

	    try {
		    $file = $_FILES['file'];

		    if(
			    !isset($_POST['id'])
		    ){
			    return wp_send_json([
				    'success' => false,
				    'error' => 'No data sent'
			    ], 500);
		    }

		    $command = new ImportDatasetCommand(sanitize_text_field($_POST['id']), $file);

		    return wp_send_json([
			    'success' => true,
			    'data' => $command->execute()
		    ]);

	    } catch (\Exception $exception){

            do_action("acpt/error", $exception);

		    return wp_send_json([
			    'error' => (!empty($exception->getMessage())) ? $exception->getMessage() : 'Error during import occurred'
		    ], 500);
	    }
    }

    /**
     * @return mixed
     */
    public function importFileAction()
    {
        if(empty($_FILES)){
            return wp_send_json([
                'error' => Translator::translate('No files uploaded')
            ], 500);
        }

        try {
        	$file = $_FILES['file'];
        	$command = new ImportFileCommand($file);

            return wp_send_json([
                'success' => true,
                'data' => $command->execute()
            ]);

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return wp_send_json([
                'error' => (!empty($exception->getMessage())) ? $exception->getMessage() : 'Error during import occurred'
            ], 500);
        }
    }

	/**
	 * Return the string translations
	 * @throws \Exception
	 */
    public function languagesAction()
    {
	    $query = new FetchLanguagesQuery();

	    return wp_send_json($query->execute());
    }

    /**
     * Regenerate post type labels
     */
    public function regeneratePostLabelsAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['postType'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing `postType`'
                ], 500);
            }

            try {
                $postType = $data['postType'];
                $command = new RegeneratePostLabelsCommand($postType);
                $command->execute();

                return wp_send_json([
                    'success' => true,
                ]);
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                return wp_send_json([
                    'success' => false,
                    'error' => $exception->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Regenerate taxonomy labels
     */
    public function regenerateTaxonomyLabelsAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if(!isset($data['taxonomy'])){
                return wp_send_json([
                    'success' => false,
                    'error' => 'Missing `taxonomy`'
                ], 500);
            }

            try {
                $taxonomy = $data['taxonomy'];
                $command = new RegenerateTaxonomyLabelsCommand($taxonomy);
                $command->execute();

                return wp_send_json([
                    'success' => true,
                ]);
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                return wp_send_json([
                    'success' => false,
                    'error' => $exception->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Reset all custom post type meta
     *
     * @return mixed
     */
    public function resetCustomPostTypesAction()
    {
        return wp_send_json([]);
    }

    /**
     * Reset all taxonomies
     *
     * @return mixed
     */
    public function resetTaxonomiesAction()
    {
        return wp_send_json([]);
    }

    public function saveBlockAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        // persist $model on DB
        try {

            $d = [
                'id' => $data[1]['id'] ?? Uuid::v4(),
                'title' => $data[1]['title'],
                'name' => $data[1]['name'],
                'category' => $data[1]['category'],
                'icon' => (is_array($data[1]['icon']) ? [
                    'src' => $data[1]['icon']['src'],
                    'background' => $data[1]['icon']['background'],
                    'foreground' => $data[1]['icon']['foreground'],
                ] : $data[1]['icon']) ,
                'css' => $data[5]['css'] ?? null,
                'callback' => $data[4]['callback'] ?? null,
                'keywords' => is_array($data[1]['keywords']) ? $data[1]['keywords'] : [],
                'postTypes' => is_array($data[1]['postTypes']) ? $data[1]['postTypes'] : [],
                'supports' => is_array($data[2]['supports']) ? $data[2]['supports'] : [],
                'controls' => is_array($data[3]['controls']) ? $data[3]['controls'] : [],
            ];

            $command = new SaveBlockCommand($d);

            $httpStatus = 200;
            $return = [
                'id' => $command->execute(),
                'success' => true,
            ];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
                'success' => false,
                'error' => $exception->getMessage()
            ];
        }

        return wp_send_json($return, $httpStatus);
    }

    /**
     * Creates a custom post type
     */
    public function saveCustomPostTypeAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        $supports = [];

        if($data[1]["support_0"] !== false){ $supports[] = $data[1]["support_0"]; }
        if($data[1]["support_1"] !== false){ $supports[] = $data[1]["support_1"]; }
        if($data[1]["support_2"] !== false){ $supports[] = $data[1]["support_2"]; }
        if($data[1]["support_3"] !== false){ $supports[] = $data[1]["support_3"]; }
        if($data[1]["support_4"] !== false){ $supports[] = $data[1]["support_4"]; }
        if($data[1]["support_5"] !== false){ $supports[] = $data[1]["support_5"]; }
        if($data[1]["support_6"] !== false){ $supports[] = $data[1]["support_6"]; }
        if($data[1]["support_7"] !== false){ $supports[] = $data[1]["support_7"]; }
        if($data[1]["support_8"] !== false){ $supports[] = $data[1]["support_8"]; }
        if($data[1]["support_9"] !== false){ $supports[] = $data[1]["support_9"]; }
        if($data[1]["support_10"] !== false){ $supports[] = $data[1]["support_10"]; }

        // persist $model on DB
        try {
            $id = (CustomPostTypeRepository::exists($data[1]["post_name"])) ? CustomPostTypeRepository::getId($data[1]["post_name"]) : Uuid::v4();

	        $data = [
		        'id' => $id,
		        'name' => $data[1]["post_name"],
		        'singular_label' => $data[1]["singular_label"],
		        'plural_label' => $data[1]["plural_label"],
		        'icon' => (isset($data[1]["icon"]['value'])) ? $data[1]["icon"]['value']: $data[1]["icon"],
		        'supports' => $supports,
		        'labels' => $data[2],
		        'settings' => $data[3]
	        ];

            $command = new SaveCustomPostTypeCommand($data);

            $httpStatus = 200;
            $return = [
            	'id' => $command->execute(),
                'success' => true,
            ];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
                'success' => false,
                'error' => $exception->getMessage()
            ];
        }

        return wp_send_json($return, $httpStatus);
    }

	/**
	 * Saves form
	 */
    public function saveFormAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    // covert data from UI
	    $convertedMeta = [];
	    $convertedFields = [];

	    if(isset($data['meta'])){
		    $metadata = $data['meta'];

		    foreach ($metadata as $metadatum){
			    foreach ($metadatum as $key => $value){

				    if(is_array($value)){
					    $value = serialize($value);
				    }

				    $convertedMeta[] = [
					    'key' => $key,
					    'value' => $value
				    ];
			    }
		    }
	    }

	    $data['meta'] = $convertedMeta;

	    if(isset($data['fields'])){
	    	foreach ($data['fields'] as $field){
			    $convertedFields[] = $this->convertFormFieldFromUI($field);
		    }
	    }

	    $data['fields'] = $convertedFields;

	    try {
	        $httpStatus = 200;
            $command = new SaveFormCommand($data);
            $return = [
			    'id' => $command->execute(),
			    'success' => true
		    ];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
			    'success' => false,
			    'error' => $exception->getMessage()
		    ];
        }

	    return wp_send_json($return, $httpStatus);
    }

	/**
	 * Saves form
	 */
	public function saveFormFieldsAction()
	{
		$data = $this->sanitizeJsonData($_POST['data']);
		$convertedFields = [];

		if(
			!isset($data['id']) or
			!isset($data['data'])
		){
			return wp_send_json([
				'success' => false,
				'error' => 'No data sent'
			], 500);
		}

		try {
			$id = $data['id'];
			$data = $data['data'];
            $httpStatus = 200;

			foreach ($data as $datum){
				$convertedFields[] = $this->convertFormFieldFromUI($datum);
			}

			$command = new SaveFormFieldsCommand($id, $convertedFields);

			$return = [
				'id' => $command->execute(),
				'success' => true
			];
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
			$return = [
				'success' => false,
				'error' => $exception->getMessage()
			];
		}

		return wp_send_json($return, $httpStatus);
	}

	/**
	 * Used to save AJAX or CUSTOM form submissions
	 */
	public function saveFormSubmissionAction()
	{
		try {
			$currentUser = wp_get_current_user();
			$data = $this->sanitizeJsonData($_POST['data']);
			$formModel = FormRepository::getById($data['acpt_form_id']);

			if($formModel === null){
				throw new \Exception("Not found form");
			}

			$callbackKey = ($formModel->getAction() === FormAction::AJAX) ? "ajax_action" : "custom_action"; //custom_method

			$formSubmission = FormSubmissionModel::hydrateFromArray([
				'formId' => $data['acpt_form_id'],
				'action' => $formModel->getAction(),
				'callback' => $formModel->getMetaDatum($callbackKey)->getValue(),
				'uid' => ($currentUser->ID > 0 ? $currentUser->ID : null),
                'createdAt' => new \DateTime(),
			]);

			foreach ($formModel->getFields() as $field){
				$datumObject = new FormSubmissionDatumObject($field->getName(), $field->getType(), $data[$field->getName()]);
				$formSubmission->addDatum($datumObject);
			}

			FormRepository::saveSubmission($formSubmission);

            $httpStatus = 200;
			$return = [
				'success' => true
			];

		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
			$return = [
				'success' => false,
				'error' => $exception->getMessage()
			];
		}

		return wp_send_json($return, $httpStatus);
	}

	/**
	 * @param $field
	 *
	 * @return array
	 */
	private function convertFormFieldFromUI($field)
	{
		return [
			'id' => @$field['field']['id'],
			'metaFieldId' => @$field['field']['metaFieldId'],
			'group' => @$field['field']['group'],
			'key' => @$field['id'],
			'name' => @$field['field']['name'],
			'label' => @$field['field']['label'],
			'type' => @$field['field']['type'],
			'description' => @$field['field']['description'],
			'required' => (bool)@$field['field']['isRequired'],
			'extra' => @$field['field']['extra'],
			'settings' => @$field['settings'],
			'rules' => @$field['validationRules'],
		];
	}

	public function saveDatasetAction()
	{
		$data = $this->sanitizeJsonData($_POST['data']);

		if(
			!isset($data['name'])
		){
			return wp_send_json([
				'success' => false,
				'error' => 'No data sent'
			], 500);
		}

		$emptyItems = (!empty($data['emptyItems'])) ? $data['emptyItems'] : false;
		unset($data['emptyItems']);

		try {
            $httpStatus = 200;
            $command = new SaveDatasetCommand($data, $emptyItems);
            $return = [
				'id' => $command->execute(),
				'success' => true
			];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
				'success' => false,
				'error' => $exception->getMessage()
			];
        }

		return wp_send_json($return, $httpStatus);
	}

	public function savePermissionAction()
	{
		$data = $this->sanitizeJsonData($_POST['data']);

		if(!isset($data)){
			return wp_send_json([
				'success' => false,
				'error' => 'No data sent'
			], 500);
		}

		try {
            $httpStatus = 200;
            $command = new SavePermissionCommand($data);
            $return = [
				'id' => $command->execute(),
				'success' => true
			];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
				'success' => false,
				'error' => $exception->getMessage()
			];
		}

		return wp_send_json($return, $httpStatus);
	}

    /**
     * Saves meta
     */
    public function saveMetaAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

	    if(
		    !isset($data['id']) or
		    !isset($data['name'])
	    ){
		    return wp_send_json([
			    'success' => false,
			    'error' => 'No data sent'
		    ], 500);
	    }

        try {
            $httpStatus = 200;
            $command = new SaveMetaGroupCommand($data);
            $return = [
		        'id' => $command->execute(),
		        'success' => true
	        ];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
		        'success' => false,
		        'error' => $exception->getMessage()
	        ];
        }

        return wp_send_json($return, $httpStatus);
    }

	/**
	 * Save option page
	 */
    public function saveOptionPagesAction()
    {
	    $data = $this->sanitizeJsonData($_POST['data']);

	    try {
	    	$pages = (isset($data['pages']) and is_array($data['pages'])) ? $data['pages'] : [];
            $pages = array_filter($pages, function ($page){
                return (
                    isset($page['menuTitle']) and
                    isset($page['pageTitle']) and
                    isset($page['menuTitle'])
                );
            });

	    	$command = new SaveOptionPagesCommand($pages);
            $httpStatus = 200;

            $return = [
			    'ids' => $command->execute(),
			    'success' => true
		    ];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
			    'success' => false,
			    'error' => $exception->getMessage()
		    ];
	    }

	    return wp_send_json($return, $httpStatus);
    }

    /**
     * @return mixed
     */
    public function saveSettingsAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        // persist $model on DB
        try {
            $command = new SaveSettingsCommand($data);
	        $command->execute();
            $httpStatus = 200;

            $return = [
                    'success' => true
            ];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
                    'success' => false,
                    'error' => $exception->getMessage()
            ];
        }

        return wp_send_json($return, $httpStatus);
    }

    /**
     * Creates a taxonomy
     */
    public function saveTaxonomyAction()
    {
        $data = $this->sanitizeJsonData($_POST['data']);

        // persist $model on DB
        try {
            $settings = $data[3];

            if($settings["rewrite"] === true){
                $settings["rewrite"] = [];
                $settings["rewrite"]["slug"] = (isset($settings["custom_rewrite"]) and null !== $settings["custom_rewrite"]) ? strtolower($settings["custom_rewrite"]) : strtolower($data[1]["slug"]) ;
            }

            $settings['capabilities'] = [];

            if($settings['capabilities_0'] === 'manage_terms') { $settings['capabilities'][] = 'manage_terms'; }
            if($settings['capabilities_1'] === 'edit_terms') { $settings['capabilities'][] = 'edit_terms'; }
            if($settings['capabilities_2'] === 'delete_terms') { $settings['capabilities'][] = 'delete_terms'; }
            if($settings['capabilities_3'] === 'assign_terms') { $settings['capabilities'][] = 'assign_terms'; }

            unset($settings['capabilities_0']);
            unset($settings['capabilities_1']);
            unset($settings['capabilities_2']);
            unset($settings['capabilities_3']);

            $id = (TaxonomyRepository::exists($data[1]["slug"])) ? TaxonomyRepository::getId($data[1]["slug"]) : Uuid::v4();
            $data = [
	            'id' => $id,
	            'slug' => $data[1]["slug"],
	            'singular_label' => $data[1]["singular_label"],
	            'plural_label' => $data[1]["plural_label"],
	            'labels' => $data[2],
	            'settings' => $settings
            ];

            $command = new SaveTaxonomyCommand($data);
	        $command->execute();
            $httpStatus = 200;

            $return = [
            	'id' => $id,
                'success' => true,
            ];
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            $httpStatus = 500;
            $return = [
                    'success' => false,
                    'error' => $exception->getMessage()
            ];
        }

        return wp_send_json($return, $httpStatus);
    }

    /**
     * @return mixed
     */
    public function syncPostsAction()
    {
	    if(isset($_POST['data'])){
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if (!isset($data['postTypes'])) {
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing postType'
			    ], 500);
		    }

		    try {
				$postTypes = $data['postTypes'];
                $httpStatus = 200;

                foreach ($postTypes as $postType){
                    CustomPostTypeRepository::createCustomPostType($postType);
                }

                $return = [
				    'success' => true
			    ];
            } catch (\Exception $exception){

                do_action("acpt/error", $exception);

                $httpStatus = 500;
                $return = [
				    'success' => false,
				    'error' => $exception->getMessage()
			    ];
		    }

		    return wp_send_json($return, $httpStatus);
	    }
    }

    /**
     * Sluggify a string
     *
     * @return mixed
     * @throws \Exception
     */
    public function sluggifyAction()
    {
        $string = null;
        $maxLength = 20;
        if(isset($_POST['data'])){
            $data = $this->sanitizeJsonData($_POST['data']);
            $string = isset($data['string']) ? $data['string'] : null;
            $maxLength = isset($data['maxLength']) ? $data['maxLength'] : 20;
        }

        if($string){
            return wp_send_json([
                    'string' => Sluggify::slug($string, $maxLength)
            ]);
        }

        return wp_send_json([
                'success' => false,
                'error' => 'no string was sent'
        ], 500);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    protected function sanitizeJsonData($data)
    {
        $jsonDecoded = json_decode(wp_unslash($data), true);

        return Sanitizer::recursiveSanitizeRawData($jsonDecoded);
    }

    /**
     * @return mixed
     */
    public function isWPGraphQLActiveAction()
    {
        return wp_send_json([
                'status' => is_plugin_active( 'wp-graphql/wp-graphql.php' ),
        ]);
    }

    /**
     * @return mixed
     */
    public function isOxygenBuilderActiveAction()
    {
        return wp_send_json([
                'status' => is_plugin_active( 'oxygen/functions.php' )
        ]);
    }

    /**
     * @return mixed
     */
    public function isBBThemeBuilderActiveAction()
    {
        return wp_send_json([
                'status' => is_plugin_active( 'bb-theme-builder/bb-theme-builder.php' )
        ]);
    }

	/**
	 * Fetch CPT not registered in ACPT
	 */
    public function fetchPostTypesAction()
    {
	    $postTypes = get_post_types(['_builtin' => false], 'objects');

	    $postTypesArray = [];
	    foreach ($postTypes as $postType){
		    if(
		    	CustomPostTypeRepository::exists($postType->name) === false and
			    $postType->show_ui === true and
		        $postType->show_in_nav_menus === true and
		        is_string($postType->menu_icon)
		    ){
			    $postTypesArray[] = $postType;
		    }
	    }

	    return wp_send_json($postTypesArray);
    }

    /**
     * @return mixed
     */
    public function fetchPostTypePostsAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData($_POST['data']);

            if (!isset($data['postType'])) {
                return wp_send_json([
                        'success' => false,
                        'error' => 'Missing postType'
                ], 500);
            }

            $postType = $data['postType'];

            global $wpdb;
            $rawData = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type=%s AND post_status=%s ORDER BY post_title", [$postType, 'publish'] );

            $data = [];

            foreach ($wpdb->get_results($rawData) as $result){
                $data[] = ["label" => $result->post_title, "value" => (int)$result->ID];
            }

            return wp_send_json($data);
        }
    }

    public function fetchPermissionAction()
    {
	    if(isset($_POST['data'])) {
		    $data = $this->sanitizeJsonData($_POST['data']);

		    if (!isset($data['id'])) {
			    return wp_send_json([
				    'success' => false,
				    'error' => 'Missing id'
			    ], 500);
		    }

		    $id = $data['id'];

		    return wp_send_json(PermissionRepository::getByEntityId($id));
	    }
    }

    /**
     * @return mixed
     */
    public function fetchPostTypeTaxonomiesAction()
    {
        if(isset($_POST['data'])) {
            $data = $this->sanitizeJsonData( $_POST[ 'data' ] );

            if ( !isset( $data[ 'postType' ] ) ) {
                return wp_send_json( [
                        'success' => false,
                        'error'   => 'Missing postType'
                ] );
            }

            $postType = $data[ 'postType' ];
			$query = new FetchPostTypeTaxonomiesQuery($postType);

            return wp_send_json($query->execute());
        }
    }

    public function deleteWPMLConfigAction()
    {
        WPMLConfig::destroy();

        return wp_send_json([
            'success' => true,
        ]);
    }

	public function wpmlConfigAction()
	{
		if(WPMLChecker::isActive() or PolylangChecker::isActive()){

			$fields = MetaFieldsProvider::getInstance()->getFields();

			return wp_send_json([
				'file' => WPMLConfig::fileExists(),
				'fields' => $fields,
				'xml' => Meta::fetch(WPMLConfig::ACPT_WPML_CONFIG_KEY, MetaTypes::OPTION_PAGE, WPMLConfig::ACPT_WPML_CONFIG_KEY)
			]);
		}

		return wp_send_json([
			'file' => false,
			'fields' => [],
			'xml' => null
		]);
	}

	public function saveTableTemplate()
	{
		if(isset($_POST['data'])) {
			try {
				$data = $this->sanitizeJsonData( $_POST[ 'data' ] );

				if(!isset($data['name'])){
					return wp_send_json([
						'success' => false,
						'error' => 'Missing `name` param'
					], 500);
				}

				if(!isset($data['json'])){
					return wp_send_json([
						'success' => false,
						'error' => 'Missing `json` param'
					], 500);
				}

				$query = new SaveTableTemplateCommand($data['name'], $data['json']);
				$query->execute();

				return wp_send_json([
					'success' => true,
				]);
			} catch (\Exception $exception){

                do_action("acpt/error", $exception);

				return wp_send_json([
					'success' => false,
					'error' => $exception->getMessage()
				], 500);
			}
		}
	}

	/**
	 * Save WPML Config file with custom settings
	 */
	public function saveWPMLConfigAction()
	{
		if(WPMLChecker::isActive() or PolylangChecker::isActive()){

			if(isset($_POST['data'])) {
				try {
					$data = $this->sanitizeJsonData( $_POST[ 'data' ] );
					$query = new SaveWPMLConfigCommand($data);
					$query->execute();

					return wp_send_json([
						'success' => true,
						'file' => WPMLConfig::fileExists(),
						'fields' => MetaFieldsProvider::getInstance()->getFields(),
						'xml' => Meta::fetch(WPMLConfig::ACPT_WPML_CONFIG_KEY, MetaTypes::OPTION_PAGE, WPMLConfig::ACPT_WPML_CONFIG_KEY)
					]);

				} catch (\Exception $exception){

                    do_action("acpt/error", $exception);

					return wp_send_json([
						'success' => false,
						'error' => $exception->getMessage()
					], 500);
				}
			}
		}

		return wp_send_json([
			'success' => false,
			'error' => 'Polylang or WPML are not active'
		], 500);
	}
}