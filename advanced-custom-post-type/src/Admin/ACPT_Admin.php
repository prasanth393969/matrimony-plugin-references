<?php

namespace ACPT\Admin;

use ACPT\Constants\Cookies;
use ACPT\Constants\MetaTypes;
use ACPT\Core\Generators\Comment\CommentAdminColumnsGenerator;
use ACPT\Core\Generators\Comment\CommentMetaGroupsGenerator;
use ACPT\Core\Generators\CustomPostType\CustomPostTypeAdminColumnsGenerator;
use ACPT\Core\Generators\CustomPostType\CustomPostTypeGenerator;
use ACPT\Core\Generators\CustomPostType\CustomPostTypeMetaBoxGenerator;
use ACPT\Core\Generators\CustomPostType\CustomPostTypeMetaGroupsGenerator;
use ACPT\Core\Generators\Meta\Fields\AbstractField;
use ACPT\Core\Generators\OptionPage\OptionPageGenerator;
use ACPT\Core\Generators\Taxonomy\TaxonomyAdminColumnsGenerator;
use ACPT\Core\Generators\Taxonomy\TaxonomyMetaGroupsGenerator;
use ACPT\Core\Generators\User\UserAdminColumnsGenerator;
use ACPT\Core\Generators\User\UserMetaGroupsGenerator;
use ACPT\Core\Helper\Strings;
use ACPT\Core\Repository\CustomPostTypeRepository;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Core\Repository\OptionPageRepository;
use ACPT\Core\Repository\TaxonomyRepository;
use ACPT\Core\Shortcodes\ACPT\AttachmentMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\CommentMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\OptionPageMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\PostMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\TaxonomyMetaShortcode;
use ACPT\Core\Shortcodes\ACPT\UserMetaShortcode;
use ACPT\Core\Shortcodes\Form\FormShortcode;
use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Loader;
use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Breakdance\ACPT_Breakdance;
use ACPT\Integrations\Bricks\ACPT_Bricks;
use ACPT\Integrations\Divi\ACPT_Divi;
use ACPT\Integrations\Elementor\ACPT_Elementor;
use ACPT\Integrations\ElementorPro\ACPT_Elementor_Pro;
use ACPT\Integrations\Etch\ACPT_Etch;
use ACPT\Integrations\GenerateBlocks\ACPT_GenerateBlocks;
use ACPT\Integrations\Gutenberg\ACPT_Gutenberg;
use ACPT\Integrations\Oxygen\ACPT_Oxygen;
use ACPT\Integrations\Polylang\ACPT_Polylang;
use ACPT\Integrations\Polylang\Helper\PolylangChecker;
use ACPT\Integrations\RankMath\ACPT_RankMath;
use ACPT\Integrations\SeoPress\ACPT_SeoPress;
use ACPT\Integrations\SlimSeo\ACPT_SlimSeo;
use ACPT\Integrations\WooCommerce\ACPT_WooCommerce;
use ACPT\Integrations\WPAllExport\ACPT_WPAllExport;
use ACPT\Integrations\WPAllImport\ACPT_WPAllImport;
use ACPT\Integrations\WPGraphQL\ACPT_WPGraphQL;
use ACPT\Integrations\WPGridBuilder\ACPT_WPGridBuilder;
use ACPT\Integrations\WPML\ACPT_WPML;
use ACPT\Integrations\WPML\Helper\WPMLChecker;
use ACPT\Integrations\Yoast\ACPT_Yoast;
use ACPT\Integrations\Zion\ACPT_Zion;
use ACPT\Utils\PHP\Cookie;
use ACPT\Utils\PHP\Maps;
use ACPT\Utils\PHP\Profiler;
use ACPT\Utils\PHP\Server;
use ACPT\Utils\PHP\Session;
use ACPT\Utils\PHP\Url;
use ACPT\Utils\Vite\Assets;
use ACPT\Utils\Wordpress\Posts;
use ACPT\Utils\Wordpress\Translator;
use ACPT\Utils\Wordpress\WPUtils;

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    advanced-custom-post-type
 * @subpackage advanced-custom-post-type/admin
 * @author     Mauro Cassani <maurocassani1978@gmail.com>
 */
class ACPT_Admin
{
    /**
     * @var ACPT_Loader
     */
    private $loader;

    /**
     * @var ACPT_Ajax
     */
    private $ajax;

    /**
     * @var array
     */
    private $pages = [];

    /**
     * @var array
     */
    private $ajaxActions = [];

    /**
     * @var array
     */
    private $staticCssAssets = [];

    /**
     * @var array
     */
    private $staticJsAssets = [];

	/**
	 * @var string
	 */
    private $googleMapsApiKey;

    /**
     * @var string
     */
    private string $pagenow;

    /**
     * @var \WP_Screen|null
     */
    private $screen;

    /**
	 * ACPT_Admin constructor.
	 *
	 * @param ACPT_Loader $loader
	 *
	 * @throws \Exception
	 */
    public function __construct(ACPT_Loader $loader)
    {
        $this->ajax = new ACPT_Ajax();
        $this->loader = $loader;
        $this->pagenow = Url::pagenow();
        $this->googleMapsApiKey = Maps::googleMapsKey();
        $this->setAjaxActions();

        add_action('init', function(){
            $this->setPages();
        });
    }

    /**
     * Define ajax actions
     */
    private function setAjaxActions()
    {
        $this->ajaxActions = [

            // Unauthenticated routes
            'wp_ajax_nopriv_generateGroupedFieldsAction' => 'generateGroupedFieldsAction',
            'wp_ajax_nopriv_checkIsVisibleAction' => 'checkIsVisibleAction',
            'wp_ajax_nopriv_generateIdAction' => 'generateIdAction',

            // Authenticated routes
            'wp_ajax_assocPostTypeToTaxonomyAction' => 'assocPostTypeToTaxonomyAction',
            'wp_ajax_assocTaxonomyToPostTypeAction' => 'assocTaxonomyToPostTypeAction',
            'wp_ajax_bulkActionsAction' => 'bulkActionsAction',
            'wp_ajax_cacheConnectionTestAction' => 'cacheConnectionTestAction',
            'wp_ajax_cacheStatsAction' => 'cacheStatsAction',
            'wp_ajax_calculateShortCodeAction' => 'calculateShortCodeAction',
            'wp_ajax_checkIsVisibleAction' => 'checkIsVisibleAction',
            'wp_ajax_checkMetaBoxNameAction' => 'checkMetaBoxNameAction',
            'wp_ajax_checkMetaBoxFieldNameAction' => 'checkMetaBoxFieldNameAction',
            'wp_ajax_checkBlockNameAction' => 'checkBlockNameAction',
            'wp_ajax_checkPostTypeNameAction' => 'checkPostTypeNameAction',
            'wp_ajax_checkTaxonomySlugAction' => 'checkTaxonomySlugAction',
            'wp_ajax_checkTwigCodeAction' => 'checkTwigCodeAction',
            'wp_ajax_copyMetaBoxAction' => 'copyMetaBoxAction',
            'wp_ajax_copyMetaBoxesAction' => 'copyMetaBoxesAction',
            'wp_ajax_copyMetaFieldAction' => 'copyMetaFieldAction',
            'wp_ajax_copyMetaFieldsAction' => 'copyMetaFieldsAction',
            'wp_ajax_copyMetaBlockAction' => 'copyMetaBlockAction',
            'wp_ajax_copyOptionPageAction' => 'copyOptionPageAction',
            'wp_ajax_copyOptionPagesAction' => 'copyOptionPagesAction',
            'wp_ajax_copyMetaBlocksAction' => 'copyMetaBlocksAction',
            'wp_ajax_createPostAndLinkItAction' => 'createPostAndLinkItAction',
            'wp_ajax_deactivateLicenseAction' => 'deactivateLicenseAction',
            'wp_ajax_deleteApiKeyAction' => 'deleteApiKeyAction',
            'wp_ajax_deleteBlockAction' => 'deleteBlockAction',
            'wp_ajax_deleteCacheItemAction' => 'deleteCacheItemAction',
            'wp_ajax_deleteCustomPostTypeAction' => 'deleteCustomPostTypeAction',
            'wp_ajax_deleteDatasetAction' => 'deleteDatasetAction',
            'wp_ajax_deleteFormAction' => 'deleteFormAction',
            'wp_ajax_deleteMetaAction' => 'deleteMetaAction',
            'wp_ajax_deletePageAction' => 'deletePageAction',
            'wp_ajax_deleteOptionPagesAction' => 'deleteOptionPagesAction',
            'wp_ajax_deleteTableTemplateAction' => 'deleteTableTemplateAction',
            'wp_ajax_deleteTaxonomyAction' => 'deleteTaxonomyAction',
            'wp_ajax_deleteUserMetaAction' => 'deleteUserMetaAction',
            'wp_ajax_duplicateAction' => 'duplicateAction',
            'wp_ajax_doShortcodeAction' => 'doShortcodeAction',
            'wp_ajax_exportCodeAction' => 'exportCodeAction',
            'wp_ajax_exportFileAction' => 'exportFileAction',
            'wp_ajax_fetchApiKeysAction' => 'fetchApiKeysAction',
            'wp_ajax_fetchApiKeysCountAction' => 'fetchApiKeysCountAction',
            'wp_ajax_fetchAllFindBelongsAction' => 'fetchAllFindBelongsAction',
            'wp_ajax_fetchAllMetaAction' => 'fetchAllMetaAction',
            'wp_ajax_fetchBlocksAction' => 'fetchBlocksAction',
            'wp_ajax_fetchBoxesAction' => 'fetchBoxesAction',
            'wp_ajax_fetchChangelogAction' => 'fetchChangelogAction',
            'wp_ajax_fetchMetaValueAction' => 'fetchMetaValueAction',
            'wp_ajax_fetchCustomPostTypesAction' => 'fetchCustomPostTypesAction',
            'wp_ajax_fetchDatasetsAction' => 'fetchDatasetsAction',
            'wp_ajax_fetchElementsAction' => 'fetchElementsAction',
            'wp_ajax_fetchFindAction' => 'fetchFindAction',
            'wp_ajax_fetchFindFromBelongsToAction' => 'fetchFindFromBelongsToAction',
            'wp_ajax_fetchFormFieldsAction' => 'fetchFormFieldsAction',
            'wp_ajax_fetchFormsAction' => 'fetchFormsAction',
            'wp_ajax_fetchFieldsAction' => 'fetchFieldsAction',
            'wp_ajax_fetchFormPreviewElementAction' => 'fetchFormPreviewElementAction',
            'wp_ajax_fetchFormSubmissionsAction' => 'fetchFormSubmissionsAction',
            'wp_ajax_fetchHeadersAndFootersAction' => 'fetchHeadersAndFootersAction',
            'wp_ajax_fetchLicenseAction' => 'fetchLicenseAction',
            'wp_ajax_fetchLogsAction' => 'fetchLogsAction',
            'wp_ajax_fetchMetaAction' => 'fetchMetaAction',
            'wp_ajax_fetchMetaFieldAction' => 'fetchMetaFieldAction',
            'wp_ajax_fetchMetaFieldRelationshipAction' => 'fetchMetaFieldRelationshipAction',
            'wp_ajax_fetchMetaFieldsFlatMapAction' => 'fetchMetaFieldsFlatMapAction',
            'wp_ajax_fetchMetaFieldsFromBelongsToAction' => 'fetchMetaFieldsFromBelongsToAction',
            'wp_ajax_fetchOptionPageAction' => 'fetchOptionPageAction',
            'wp_ajax_fetchOptionPagesAction' => 'fetchOptionPagesAction',
            'wp_ajax_fetchOptionPagesMetaAction' => 'fetchOptionPagesMetaAction',
            'wp_ajax_fetchPostTypesAction' => 'fetchPostTypesAction',
            'wp_ajax_fetchPostTypeTaxonomiesAction' => 'fetchPostTypeTaxonomiesAction',
            'wp_ajax_fetchPostTypePostsAction' => 'fetchPostTypePostsAction',
            'wp_ajax_fetchPermissionAction' => 'fetchPermissionAction',
            'wp_ajax_fetchPreviewLinkAction' => 'fetchPreviewLinkAction',
            'wp_ajax_fetchSettingsAction' => 'fetchSettingsAction',
            'wp_ajax_fetchTaxonomiesAction' => 'fetchTaxonomiesAction',
            'wp_ajax_fetchTableTemplatesAction' => 'fetchTableTemplatesAction',
            'wp_ajax_fetchTermsAction' => 'fetchTermsAction',
            'wp_ajax_fetchUserMetaAction' => 'fetchUserMetaAction',
            'wp_ajax_flushCacheAction' => 'flushCacheAction',
            'wp_ajax_generateApiKeyAction' => 'generateApiKeyAction',
            'wp_ajax_generateEmbedAction' => 'generateEmbedAction',
            'wp_ajax_generateGroupedFieldsAction' => 'generateGroupedFieldsAction',
            'wp_ajax_generateFlexibleBlockAction' => 'generateFlexibleBlockAction',
            'wp_ajax_generateFlexibleGroupedFieldsAction' => 'generateFlexibleGroupedFieldsAction',
            'wp_ajax_generateGutenbergTemplateAction' => 'generateGutenbergTemplateAction',
            'wp_ajax_generateIdAction' => 'generateIdAction',
            'wp_ajax_generateRelationalFieldOptionsAction' => 'generateRelationalFieldOptionsAction',
            'wp_ajax_globalSettingsAction' => 'globalSettingsAction',
            'wp_ajax_healthCheckAction' => 'healthCheckAction',
            'wp_ajax_importDatasetAction' => 'importDatasetAction',
            'wp_ajax_importFileAction' => 'importFileAction',
            'wp_ajax_languagesAction' => 'languagesAction',
            'wp_ajax_regeneratePostLabelsAction' => 'regeneratePostLabelsAction',
            'wp_ajax_regenerateTaxonomyLabelsAction' => 'regenerateTaxonomyLabelsAction',
            'wp_ajax_resetCustomPostTypesAction' => 'resetCustomPostTypesAction',
            'wp_ajax_resetTaxonomiesAction' => 'resetTaxonomiesAction',
            'wp_ajax_runRepairAction' => 'runRepairAction',
            'wp_ajax_saveBlockAction' => 'saveBlockAction',
            'wp_ajax_saveCustomPostTypeAction' => 'saveCustomPostTypeAction',
            'wp_ajax_savePermissionAction' => 'savePermissionAction',
            'wp_ajax_saveFormAction' => 'saveFormAction',
            'wp_ajax_saveFormFieldsAction' => 'saveFormFieldsAction',
            'wp_ajax_saveFormSubmissionAction' => 'saveFormSubmissionAction',
            'wp_ajax_saveDatasetAction' => 'saveDatasetAction',
            'wp_ajax_saveMetaAction' => 'saveMetaAction',
            'wp_ajax_saveOptionPagesAction' => 'saveOptionPagesAction',
            'wp_ajax_saveSettingsAction' => 'saveSettingsAction',
            'wp_ajax_saveTaxonomyAction' => 'saveTaxonomyAction',
            'wp_ajax_saveUserMetaAction' => 'saveUserMetaAction',
            'wp_ajax_syncPostsAction' => 'syncPostsAction',
            'wp_ajax_sluggifyAction' => 'sluggifyAction',
            'wp_ajax_isWPGraphQLActiveAction' => 'isWPGraphQLActiveAction',
            'wp_ajax_isOxygenBuilderActiveAction' => 'isOxygenBuilderActiveAction',
            'wp_ajax_isBBThemeBuilderActiveAction' => 'isBBThemeBuilderActiveAction',
            'wp_ajax_wpmlConfigAction' => 'wpmlConfigAction',
            'wp_ajax_saveTableTemplate' => 'saveTableTemplate',
            'wp_ajax_saveWPMLConfigAction' => 'saveWPMLConfigAction',
            'wp_ajax_deleteWPMLConfigAction' => 'deleteWPMLConfigAction',
        ];
    }

	/**
	 * Define admin pages
	 *
	 * @throws \Exception
	 */
    private function setPages()
    {
        $pages = [
            [
                'pageTitle' => 'Advanced Custom Post Types',
                'menuTitle' => 'ACPT',
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME,
                'template' => 'app',
                'iconUrl' => plugins_url( 'advanced-custom-post-type/assets/static/img/advanced-custom-post-type-icon.svg'),
                'position' => 50,
            ]
        ];

        if(ACPT_ENABLE_CPT){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Custom Post Types', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Custom Post Types', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME,
                'template' => 'app',
                'position' => 51,
            ];
        }

        if(ACPT_ENABLE_TAX){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Taxonomies', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Taxonomies', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/taxonomies',
                'template' => 'app',
                'position' => 52,
            ];
        }

        if(ACPT_ENABLE_PAGES and ACPT_IS_LICENSE_VALID){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Option pages', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Option pages', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/option-pages',
                'template' => 'app',
                'position' => 53,
            ];
        }

        if(ACPT_ENABLE_META){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Field groups', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Field groups', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/meta',
                'template' => 'app',
                'position' => 54,
            ];
        }

        if(ACPT_ENABLE_FORMS and ACPT_IS_LICENSE_VALID){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Forms', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Forms', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/forms',
                'template' => 'app',
                'position' => 55,
            ];
        }

        if(ACPT_ENABLE_BLOCKS and ACPT_IS_LICENSE_VALID){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Dynamic blocks', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Dynamic blocks', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/blocks',
                'template' => 'app',
                'position' => 56,
            ];
        }


        if(WPMLChecker::isActive()){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('WPML', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('WPML', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/wpml',
                'template' => 'app',
                'position' => 57,
            ];
        }

        if(PolylangChecker::isActive()){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Polylang', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Polylang', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/polylang',
                'template' => 'app',
                'position' => 58,
            ];
        }

        if(ACPT_ENABLE_LOG){
            $pages[] = [
                'parentSlug' => ACPT_PLUGIN_NAME,
                'pageTitle' => translate('Logs', ACPT_PLUGIN_NAME),
                'menuTitle' => translate('Logs', ACPT_PLUGIN_NAME),
                'capability' => 'manage_options',
                'menuSlug' => ACPT_PLUGIN_NAME . '#/logs',
                'template' => 'app',
                'position' => 59,
            ];
        }

        $pages[] = [
            'parentSlug' => ACPT_PLUGIN_NAME,
            'pageTitle' => translate('Tools', ACPT_PLUGIN_NAME),
            'menuTitle' => translate('Tools', ACPT_PLUGIN_NAME),
            'capability' => 'manage_options',
            'menuSlug' => ACPT_PLUGIN_NAME . '#/tools',
            'template' => 'app',
            'position' => 60,
        ];

        $pages[] = [
            'parentSlug' => ACPT_PLUGIN_NAME,
            'pageTitle' => translate('Settings', ACPT_PLUGIN_NAME),
            'menuTitle' => translate('Settings', ACPT_PLUGIN_NAME),
            'capability' => 'manage_options',
            'menuSlug' => ACPT_PLUGIN_NAME . '#/settings',
            'template' => 'app',
            'position' => 61,
        ];

        $pages[] = [
            'parentSlug' => ACPT_PLUGIN_NAME,
            'pageTitle' => translate('License', ACPT_PLUGIN_NAME),
            'menuTitle' => translate('License', ACPT_PLUGIN_NAME),
            'capability' => 'manage_options',
            'menuSlug' => ACPT_PLUGIN_NAME . '#/license',
            'template' => 'app',
            'position' => 62,
        ];

        $this->pages = $pages;
    }

    /**
     * Add pages to to admin panel
     */
    public function addPages()
    {
        foreach ($this->pages as $page){
            $this->addPage(
                $page['pageTitle'],
                $page['menuTitle'],
                $page['capability'],
                $page['menuSlug'],
                $page['template'],
                (isset($page['iconUrl'])) ? $page['iconUrl'] : null,
                (isset($page['position'])) ? $page['position'] : null,
                (isset($page['parentSlug'])) ? $page['parentSlug'] : null
            );
        }
    }

    /**
     * Add a single page to admin panel
     *
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     * @param string $template
     * @param string $iconUrl
     * @param null   $position
     * @param null   $parentSlug
     */
    private function addPage($pageTitle, $menuTitle, $capability, $menuSlug, $template, $iconUrl = '', $position = null, $parentSlug = null)
    {
        if(isset($parentSlug)){
            add_submenu_page(
                $parentSlug,
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                function () use($template) {
                    require_once plugin_dir_path( dirname( __FILE__ ) ) . '../admin/templates/'.$template.'.php';
                },
                $position
            );
        } else {
            add_menu_page(
                $pageTitle,
                $menuTitle,
                $capability,
                $menuSlug,
                function () use($template) {
                    require_once plugin_dir_path( dirname( __FILE__ ) ) . '../admin/templates/'.$template.'.php';
                },
                $iconUrl,
                $position
            );
        }
    }

    /**
     * Enqueue assets
     */
    public function enqueueAssets()
    {
        // Register assets
        $this->setApplicationAssets();
        $this->setGutenbergBlockAssets();
        $this->setAdminAssets();

        // Register media
        if ( ! function_exists( 'wp_enqueue_media' ) ) {
            require ABSPATH . 'wp-admin/includes/media.php';
        }

        wp_enqueue_media();

        // Enqueue registered CSS assets
        foreach ($this->staticCssAssets as $key => $asset){
            wp_enqueue_style( ACPT_PLUGIN_NAME.'__'.$key, $asset, [], ACPT_PLUGIN_VERSION, 'all');
        }

        // Enqueue registered JS assets
        foreach ($this->staticJsAssets as $key => $asset){
            if($asset['module'] === true){

                // modules
                if(isset($asset['modules']) and is_array($asset['modules'])){
                    foreach ($asset['modules'] as $module){
                        wp_register_script_module( $module['id'], $module['path'] );
                    }
                }

                wp_enqueue_script_module( ACPT_PLUGIN_NAME.'__'.$key, $asset['path'], isset($asset['dep']) ? $asset['dep'] : [], ACPT_PLUGIN_VERSION);
            } else {
                wp_enqueue_script( ACPT_PLUGIN_NAME.'__'.$key, $asset['path'], isset($asset['dep']) ? $asset['dep'] : [], ACPT_PLUGIN_VERSION, true);
            }
        }

        // Validator
        if($this->pagenow === 'post-new.php' or $this->pagenow === 'post.php'){
            $customPostTypeMetaBoxGenerator = new CustomPostTypeMetaBoxGenerator();
            $customPostTypeMetaBoxGenerator->enqueueScripts('save-cpt');
        }
    }

    /**
     * Set Application assets
     */
    private function setApplicationAssets()
    {
        if($this->isACPTAppPage()){
            $viteAssets = Assets::load('assets/src/App/index.jsx', 'acpt_app');

            foreach ($viteAssets['css'] as $viteCssAssetKey => $viteCssAsset){
                $this->staticCssAssets[$viteCssAssetKey] = $viteCssAsset;
            }

            foreach ($viteAssets['js'] as $viteJsAssetKey => $viteJsAsset){
                wp_localize_script(ACPT_PLUGIN_NAME.'__'.$viteJsAssetKey, 'acpt', ['pluginsUrl' => plugins_url()]);
                $this->staticJsAssets[$viteJsAssetKey] = [
                        'path' => $viteJsAsset,
                        'module' => true,
                        'dep'  => ['wp-element'],
                ];
            }
        }
    }

    /**
     * Set Gutenberg Block assets
     */
    private function setGutenbergBlockAssets()
    {
        $screen = get_current_screen();

        if($screen !== null and $screen->is_block_editor === true){
            $viteAssets = Assets::load('assets/src/Gutenberg/index.jsx', 'block_js');

            foreach ($viteAssets['css'] as $viteCssAssetKey => $viteCssAsset){
                $this->staticCssAssets[$viteCssAssetKey] = $viteCssAsset;
            }

            foreach ($viteAssets['js'] as $viteJsAssetKey => $viteJsAsset){
                $this->staticJsAssets[$viteJsAssetKey] = [
                        'path' => $viteJsAsset,
                        'module' => true,
                        'dep'  => ['wp-blocks', 'wp-element'],
                ];
            }
        }
    }

    /**
     * Set Admin assets
     */
    private function setAdminAssets()
    {
        // check option pages
        if($this->pagenow === 'admin.php' and isset($_GET['page']) and !in_array($_GET['page'], OptionPageRepository::getAllSlugs())){
            return;
        }

        // allowed pages
        $allowedPages = [
            'site-editor.php',
            'post-new.php',
            'edit.php',
            'post.php',
            'profile.php',
            'user-edit.php',
            'edit-tags.php',
            'term.php',
            'upload.php',
            'admin.php',
            'comment.php',
            'edit-comments.php',
        ];

        if(
            in_array($this->pagenow, $allowedPages) and
            !$this->isACPTAppPage()
        ){
            // CSS
            $this->staticCssAssets = [
                'admin_selectize_css' => plugins_url(  'advanced-custom-post-type/assets/vendor/selectize/selectize.default.min.css'),
                'admin_css' => $this->assetPath("admin.css"),
            ];

            // JS
            $this->staticJsAssets['admin_selectize_js'] = [
                'path' => plugins_url( 'advanced-custom-post-type/assets/vendor/selectize/selectize.min.js'),
                'module' => false,
                'dep'  => ['jquery'],
            ];

            $this->staticJsAssets['admin_js'] = [
                'path' => $this->assetPath("admin.js"),
                'module' => true,
                'modules' => [
                    [
                        'id' => 'admin_colorpicker_js',
                        'path' => $this->assetPath("_admin_colorpicker.js"),
                    ],
                    [
                        'id' => 'admin_commons_js',
                        'path' => $this->assetPath("_admin_commons.js"),
                    ],
                    [
                        'id' => 'admin_datepicker_js',
                        'path' => $this->assetPath("_admin_datepicker.js"),
                    ],
                    [
                        'id' => 'admin_editor_js',
                        'path' => $this->assetPath("_admin_editor.js"),
                    ],
                    [
                        'id' => 'admin_file_js',
                        'path' => $this->assetPath("_admin_file.js"),
                    ],
                    [
                        'id' => 'admin_flexible_js',
                        'path' => $this->assetPath("_admin_flexible.js"),
                    ],
                    [
                        'id' => 'admin_helpers_js',
                        'path' => $this->assetPath("_admin_helpers.js"),
                    ],
                    [
                        'id' => 'admin_iconpicker_js',
                        'path' => $this->assetPath("_admin_iconpicker.js"),
                    ],
                    [
                        'id' => 'admin_list_js',
                        'path' => $this->assetPath("_admin_list.js"),
                    ],
                    [
                        'id' => 'admin_misc_js',
                        'path' => $this->assetPath("_admin_misc.js"),
                    ],
                    [
                        'id' => 'admin_relational_js',
                        'path' => $this->assetPath("_admin_relational.js"),
                    ],
                    [
                        'id' => 'admin_repeater_js',
                        'path' => $this->assetPath("_admin_repeater.js"),
                    ],
                    [
                        'id' => 'admin_sortable_js',
                        'path' => $this->assetPath("_admin_sortable.js"),
                    ],
                    [
                        'id' => 'admin_woocommerce_js',
                        'path' => $this->assetPath("_admin_woocommerce.js"),
                    ],
                ],
                'dep'  => [
                    [
                        'id' => 'jquery',
                        'import' => 'static',
                    ],
                    [
                        'id' => 'admin_colorpicker_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_commons_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_datepicker_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_editor_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_file_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_flexible_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_helpers_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_iconpicker_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_list_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_misc_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_relational_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_repeater_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_sortable_js',
                        'import' => 'dynamic',
                    ],
                    [
                        'id' => 'admin_woocommerce_js',
                        'import' => 'dynamic',
                    ],
                ],
            ];

            if($this->pagenow === "edit-comments.php"){
                $this->staticJsAssets['comment_quick_edit_js'] = [
                    'path' => $this->assetPath("comment-quick-edit.js"),
                    'module' => false,
                    'dep'  => ['jquery'],
                ];
            }

            if($this->pagenow === 'edit.php'){
                $this->staticJsAssets['quick_edit_js'] = [
                    'path' => $this->assetPath( 'quick_edit.js'),
                    'module' => false,
                    'dep'  => ['jquery'],
                ];
            }

            //
            // =================================
            // WP DEFAULT UTILITIES
            // =================================
            //

            // color picker
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );

            // codemirror
            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/html'));
            wp_localize_script('jquery', 'cm_settings', $cm_settings);
            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');

            //
            // =================================
            // ICONIFY
            // =================================
            //
            wp_register_script('iconify',  plugins_url( 'advanced-custom-post-type/assets/vendor/iconify/iconify.min.js') );
            wp_enqueue_script('iconify');
        }
    }

    /**
     * @param $asset
     *
     * @return string
     */
    private function assetPath($asset)
    {
        $pathinfo = pathinfo($asset);
        $extension = $pathinfo['extension'];
        $filename = $pathinfo['filename'];
        $baseDir = 'advanced-custom-post-type/assets/static/';

        return plugins_url(ACPT_DEV_MODE ? $baseDir.$extension.'/'.$filename.'.'.$extension : $baseDir.$extension.'/'.$filename.'.min.'.$extension);
    }

	/**
	 * Invalid CustomPostTypeMetaGroupsGenerator cache when creating a new post
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
    public function invalidPostIdsCache($new_status, $old_status, $post)
    {
        $allowedStatus = [
            'draft',
            'publish',
	        'private',
            'auto-draft',
	        'inherit'
        ];

	    if (
            in_array($new_status, $allowedStatus) and
            in_array($old_status, $allowedStatus)
        ) {
		    ACPT_DB::invalidateCacheTag(CustomPostTypeMetaGroupsGenerator::class);
	    }
    }

    /**
     * Add filters here
     */
    public function addFilters()
    {
	    add_filter('plugin_action_links', [ $this, 'addPluginLinks' ], PHP_INT_MAX, 2 );
        add_filter('script_loader_tag', [$this, 'addAsyncDeferAttribute'], 10, 2);
        add_filter('block_categories_all', [$this, 'addGutenbergBlocks'], 10, 2 );
        add_filter('safe_style_css', [$this, 'allowCSSAttributes'], 10, 1);
        add_filter('kses_allowed_protocols', [$this, 'addDataProtocol'], 10, 1);
    }

    /**
     * Allow CSS attributes
     *
     * @see https://wordpress.stackexchange.com/questions/173526/why-is-wp-kses-not-keeping-style-attributes-as-expected/195433#195433
     * @param $styles
     *
     * @return array
     */
    public function allowCSSAttributes($styles)
    {
        $styles[] = 'fill';
        $styles[] = 'transform';

        return $styles;
    }

    /**
     * Add data protocol to kses
     *
     * @param $protocols
     * @return array
     */
    public function addDataProtocol($protocols)
    {
        $protocols[] = 'data';

        return $protocols;
    }

	/**
     * Add plugin links
     *
	 * @param $actions
	 * @param $plugin_file
	 *
	 * @return array
	 */
    public function addPluginLinks($actions, $plugin_file)
    {
	    $actionLinks = [];

	    if ( 'advanced-custom-post-type/advanced-custom-post-type.php' === $plugin_file ) {
	        $actionLinks['settings'] = '<a href="'.admin_url( 'admin.php?page='.ACPT_PLUGIN_NAME.'#/settings' ).'">'.Translator::translate('Settings').'</a>';
		    $actionLinks['documentation'] = '<a target="_blank" href="https://docs.acpt.io/">'.Translator::translate('Documentation').'</a>';
	    }

	    return array_merge($actionLinks, $actions);
    }

    /**
     * Register custom Gutember
     *
     * @param $block_categories
     * @param $block_editor_context
     *
     * @return array
     */
    public function addGutenbergBlocks($block_categories, $block_editor_context)
    {
        $category_slugs = wp_list_pluck( $block_categories, 'slug' );

        return in_array( 'advanced-custom-post-type-blocks', $category_slugs, true ) ? $block_categories : array_merge(
                $block_categories,
                [
                    [
                        'slug'  => 'advanced-custom-post-type-blocks',
                        'title' => __( 'ACPT Blocks', 'advanced-custom-post-type-blocks' ),
                        'icon'  => null,
                    ]
                ]
        );
    }

    /**
     * Async script load
     *
     * @param $tag
     * @param $handle
     *
     * @return string|string[]
     */
    public function addAsyncDeferAttribute($tag, $handle)
    {
        if ( 'google-maps' !== $handle ){
            return $tag;
        }

        return str_replace( ' src', ' async defer src', $tag );
    }

    /**
     * Add shortcodes
     */
    private function addShortcodes()
    {
        add_shortcode('acpt', [new PostMetaShortcode(), 'render']);
        add_shortcode('acpt_user', [new UserMetaShortcode(), 'render']);
        add_shortcode('acpt_tax', [new TaxonomyMetaShortcode(), 'render']);
        add_shortcode('acpt_media', [new AttachmentMetaShortcode(), 'render']);
        add_shortcode('acpt_comm', [new CommentMetaShortcode(), 'render']);

        if(ACPT_IS_LICENSE_VALID){
            add_shortcode('acpt_form', [new FormShortcode(), 'render']);
            add_shortcode('acpt_option', [new OptionPageMetaShortcode(), 'render']);
        }
    }

    /**
     * This function adds acpt class to body
     *
     * @param bool $isACPTAppPage
     */
    private function addACTPClassToBody($isACPTAppPage = false)
    {
        if($isACPTAppPage){
            add_filter( 'admin_body_class', function ($classes){

                $classes .= " acpt";

                if(ACPT_SKIN === 'dark'){
                    $classes .= "-dark";
                }

                return $classes;
            } );
        }
    }

	/**
	 * @param bool $lazy
	 *
	 * @throws \Exception
	 */
    private function registerCustomPostTypesAndTaxonomies($lazy = false)
    {
        // run this code after all plugins are loaded
        try {
            $postTypeMetaGroups = [];

            // prevents any orphan meta box/field
            MetaRepository::removeOrphanBoxesAndFields();

            // generate meta groups
            if($lazy === false){

                // enable savePost function only on post and quick edit pages
                $allowedPages = [
                    'admin.php',
                    'admin-ajax.php',
                    'edit.php',
                    'post.php',
                    'post-new.php'
                ];

                // enable rendering of meta fields only on post (or new post) page
                $metaRendering = in_array($this->pagenow, [
                    'post.php',
                    'post-new.php'
                ]);

                $postTypeAssociatedWithGroup = MetaRepository::getAllAssociatedPostTypesAndTaxonomies()['postTypeNames'];
                $registeredACPTPostTypeNames = CustomPostTypeRepository::getNames();
                $postTypeNames = array_unique(array_merge($postTypeAssociatedWithGroup, $registeredACPTPostTypeNames));

                foreach ($postTypeNames as $postTypeName){
                    if(is_string($postTypeName)){
                        $metaGroupModels = MetaRepository::get([
                            'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
                            'find' => $postTypeName,
                            'clonedFields' => true
                        ]);

                        if(!empty($metaGroupModels) and ACPT_ENABLE_META){
                            $postTypeMetaGroupsGenerator = new CustomPostTypeMetaGroupsGenerator($postTypeName, $metaGroupModels);
                            $postTypeMetaGroups[$postTypeName] = $postTypeMetaGroupsGenerator->generate($metaRendering);
                        }

                        // register the "save_post" hook for all custom post types
                        if(in_array($this->pagenow, $allowedPages)){

                            // Handle save post functions
                            $metaGroups = $postTypeMetaGroups[$postTypeName] ?? [];

                            // can't use a filter here, because integration are not yet running
                            if(ACPT_WooCommerce::active() and $postTypeName === 'shop_order'){
                                $hookName = 'woocommerce_process_shop_order_meta';
                            } elseif($postTypeName === "attachment"){
                                $hookName = "edit_attachment";
                            } else {
                                $hookName = 'save_post_'.$postTypeName;
                            }

                            add_action($hookName, function ($postId) use ($metaGroups) {
                                WPUtils::handleSavePost($postId, $metaGroups);
                            });

                            // Flush Posts cache on post status change
                            $actions = [
                                'trash',
                                'publish',
                                'pending',
                                'draft',
                                'delete',
                                'future',
                                'private'
                            ];

                            foreach ($actions as $action){
                                add_action( 'admin_init', function () use($action, $postTypeName){
                                    add_action( $action.'_'.$postTypeName, function ($post_id, $post) use($action, $postTypeName){
                                        Posts::invalidPostsByTypeQueryCache($postTypeName);
                                        Posts::invalidCache();
                                    }, 10, 2 );
                                });
                            }
                        }
                    }
                }
            }

            // register here ACTP CPTs and Taxonomies
            foreach (CustomPostTypeRepository::get() as $postTypeModel){
                $postTypeName = $postTypeModel->getName();
                $ACPTPostTypeNames[] = $postTypeName;
                $customPostType = new CustomPostTypeGenerator($postTypeModel, $postTypeMetaGroups[$postTypeName] ?? []);
            }

        } catch (\Exception $exception){
            // do nothing
            do_action("acpt/error", $exception);
        }
    }

    private function registerCommentMeta()
    {
	    (new CommentMetaGroupsGenerator())->generate();
    }

    private function addCommentColumnsToAdminPanel()
    {
	    CommentAdminColumnsGenerator::addColumns();
    }

    /**
     * @throws \Exception
     */
    private function registerTaxonomyMeta()
    {
        (new TaxonomyMetaGroupsGenerator())->generate();
    }

    /**
     * Add CPT columns to the admin panel
     * (including quick edit and filter capabilities)
     *
     * @throws \Exception
     */
    private function addCustomPostTypeColumnsToAdminPanel()
    {
        $acptPostTypes = CustomPostTypeRepository::get();
        $postTypesAssociatedWithGroup = MetaRepository::getAllAssociatedPostTypesAndTaxonomies()['postTypeNames'];
        $notACPTPostTypes = $postTypesAssociatedWithGroup;

        foreach ($acptPostTypes as $postTypeModel){
	        CustomPostTypeAdminColumnsGenerator::addColumns($postTypeModel);

            if (($key = array_search($postTypeModel->getName(), $notACPTPostTypes)) !== false) {
                unset($notACPTPostTypes[$key]);
            }
        }

        foreach ($notACPTPostTypes as $notACPTPostType){
            if(is_string($notACPTPostType)){
                CustomPostTypeAdminColumnsGenerator::addColumns($notACPTPostType);
            }
        }
    }

    /**
     * Add Taxonomy columns to the admin panel
     *
     * @throws \Exception
     */
    private function addTaxonomyColumnsToAdminPanel()
    {
        $acptTaxonomies = TaxonomyRepository::get();
        $taxonomiesAssociatedWithGroup = MetaRepository::getAllAssociatedPostTypesAndTaxonomies()['taxonomyNames'];
        $notACPTTaxonomies = $taxonomiesAssociatedWithGroup;

        foreach ($acptTaxonomies as $taxonomyModel){
            TaxonomyAdminColumnsGenerator::addColumns($taxonomyModel);

            if (($key = array_search($taxonomyModel->getSlug(), $notACPTTaxonomies)) !== false) {
                unset($notACPTTaxonomies[$key]);
            }
        }

        foreach ($notACPTTaxonomies as $notACPTTaxonomy){
            if(is_string($notACPTTaxonomy)){
                TaxonomyAdminColumnsGenerator::addColumns($notACPTTaxonomy);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function registerUserMeta()
    {
        (new UserMetaGroupsGenerator())->generate();
    }

    /**
     * Add User meta columns to show in the admin panel
     */
    private function addUserMetaColumnsToShow()
    {
	    UserAdminColumnsGenerator::addColumns();
    }

	/**
	 * Register option pages
	 *
	 * @param bool $lazy
	 *
	 * @throws \Exception
	 */
    private function registerOptionPages($lazy = false)
    {
	    $optionPages = OptionPageRepository::get([]);

	    foreach ($optionPages as $optionPage){
		    $optionPageGenerator = new OptionPageGenerator($this->loader, $optionPage, $lazy);
		    $optionPageGenerator->registerPage();
	    }
    }

    /**
     * Register API fields
     */
    private function registerRestFields()
    {
        $this->loader->addAction( 'rest_api_init', new ACPT_Api_Rest_Fields(), 'registerRestFields' );
    }

    /**
     * Register API endpoints
     */
    private function registerRestEndpoint()
    {
        $this->loader->addAction( 'rest_api_init', new ACPT_Api_V1(), 'registerRestRoutes' );
    }

	/**
	 * Register roles and set permissions
	 */
    private function registerRolesAndSetPermissions()
    {
	    $this->loader->addAction( 'admin_init', new ACPT_Permissions(), 'setPermissions', 999);
    }

    /**
     * Include PHP functions
     */
    private function includeFunctions()
    {
	    require_once ACPT_PLUGIN_DIR_PATH.'/functions/acpt_functions.php';
    }

    /**
     * Run integrations
     */
    private function runIntegrations()
    {
        $integrations = [
	        ACPT_Breakdance::class,
	        ACPT_Bricks::class,
	        ACPT_Divi::class,
	        ACPT_Etch::class,
	        ACPT_Elementor::class,
	        ACPT_Elementor_Pro::class,
	        ACPT_GenerateBlocks::class,
	        ACPT_Gutenberg::class,
	        ACPT_Oxygen::class,
	        ACPT_Polylang::class,
	        ACPT_RankMath::class,
	        ACPT_SeoPress::class,
	        ACPT_SlimSeo::class,
	        ACPT_WooCommerce::class,
	        ACPT_WPAllExport::class,
	        ACPT_WPAllImport::class,
	        ACPT_WPGraphQL::class,
	        ACPT_WPGridBuilder::class,
	        ACPT_WPML::class,
            ACPT_Yoast::class,
	        ACPT_Zion::class,
        ];

        foreach ($integrations as $integration){
            /** @var AbstractIntegration $instance */
            $instance = new $integration;
            $instance->run();
            Profiler::lap('runIntegrations');
        }
    }

	/**
	 * Close session before curl request
	 *
	 * @param $curlhandle
	 */
	public function curlBeforeRequest($curlhandle)
	{
		Session::close();
	}

	/**
	 * @param $timeout
	 *
	 * @return int
	 */
	public function extendHttpRequestTimeout($timeout)
    {
		return 30; // seconds
	}

	/**
	 * Fetch the admin menu and add it to document.globals
	 */
	public function fetchAdminMenu()
	{
		$home = get_option('home');
		$pluginsUrl = plugins_url();

		if(Server::isSecure()){
			$home = Url::secureUrl($home);
			$pluginsUrl = Url::secureUrl($pluginsUrl);
		}

		wp_register_script( 'globals-menu-run', '', [], '', true );
		wp_enqueue_script('globals-menu-run');
		wp_add_inline_script( 'globals-menu-run', '
            document.globals = {site_url: "'.$home.'", plugins_url: "'.$pluginsUrl.'", menu: '. json_encode($GLOBALS['menu']).', google_maps_key: "'.($this->googleMapsApiKey ? $this->googleMapsApiKey : "").'"};
		');
	}

	/**
	 * @return bool
	 */
	private function isACPTAppPage()
    {
	    return (
		    $this->pagenow === 'admin.php' and
		    isset($_GET['page']) and
		    $_GET['page'] === ACPT_PLUGIN_NAME
	    );
    }

    public function displayNotice()
    {
        $messages = Cookie::get(Cookies::ACPT_ERRORS);

        if(!empty($messages)){
            foreach ($messages as $message){
                add_action('admin_notices', function () use ($message){
                    ?>
                    <div id="acpt-message-<?php echo Strings::generateRandomId(); ?>" class="notice notice-<?php echo $message['level']; ?> is-dismissible">
                        <p><?php echo $message['message']; ?></p>
                    </div>
                    <?php
                });
            }

            Cookie::delete(Cookies::ACPT_ERRORS);
        }
    }

	/**
	 * Run admin scripts
	 *
	 * @throws \Exception
	 */
    public function run()
    {
        $isACPTAppPage  = $this->isACPTAppPage();

        $this->displayNotice();

        // filters
        Profiler::start('addFilters');
	    $this->addFilters();
	    Profiler::stop('addFilters');

	    // pages and assets
	    Profiler::start('addPages');
	    $this->loader->addAction('admin_menu', $this, 'addPages');
	    Profiler::stop('addPages');

	    Profiler::start('enqueueAssets');
	    $this->loader->addAction('admin_enqueue_scripts', $this, 'enqueueAssets');
	    Profiler::stop('enqueueAssets');

	    // admin menu
	    Profiler::start('fetchAdminMenu');
	    $this->loader->addAction('admin_menu', $this, 'fetchAdminMenu');
	    Profiler::stop('fetchAdminMenu');

	    // register roles and set permissions
	    Profiler::start('registerRolesAndSetPermissions');
	    $this->registerRolesAndSetPermissions();
	    Profiler::stop('registerRolesAndSetPermissions');

		// run the application
        Profiler::start('transitionPostStatus');
        $this->loader->addAction('transition_post_status', $this, 'invalidPostIdsCache', 10, 3);
        Profiler::stop('transitionPostStatus');

        Profiler::start('siteHealth');
        $this->loader->addAction('requests-curl.before_request', $this, 'curlBeforeRequest', 9999);
        $this->loader->addAction('http_request_timeout', $this, 'extendHttpRequestTimeout');
        Profiler::stop('siteHealth');

        // ajax calls
        Profiler::start('ajaxCalls');

        foreach ($this->ajaxActions as $action => $callback){
            $this->loader->addAction($action, $this->ajax, $callback);
            Profiler::lap('ajaxCalls');
        }
        Profiler::stop('ajaxCalls');

        // register custom post types and taxonomies. Lazy load custom post type metas
        Profiler::start('registerCustomPostTypesAndTaxonomies');
        $this->registerCustomPostTypesAndTaxonomies($isACPTAppPage);
        Profiler::stop('registerCustomPostTypesAndTaxonomies');

        if(ACPT_ENABLE_PAGES and ACPT_IS_LICENSE_VALID){
            // add option pages
            Profiler::start('registerOptionPages');
            $this->registerOptionPages($this->isACPTAppPage());
            Profiler::stop('registerOptionPages');
        }

        // API REST
        Profiler::start('RestFieldsAndEndpoints');
        $this->registerRestFields();
        $this->registerRestEndpoint();
        Profiler::stop('RestFieldsAndEndpoints');

        // add ACPT class body
        if(is_admin()){
            Profiler::start('addACTPClassToBody');
            $this->addACTPClassToBody($isACPTAppPage);
            Profiler::stop('addACTPClassToBody');
        }

        // lazy load, these functions are not needed in App page
        if(!$isACPTAppPage){

            // shortcodes
            Profiler::start('addShortcodes');
            $this->addShortcodes();
            Profiler::stop('addShortcodes');

            if(ACPT_ENABLE_META){

                // register comments meta
                Profiler::start('registerCommentMeta');
                $this->registerCommentMeta();
                Profiler::stop('registerCommentMeta');

                // add columns to show in the list panel
                if(is_admin()){
                    Profiler::start('addCommentColumnsToAdminPanel');
                    $this->addCommentColumnsToAdminPanel();
                    Profiler::stop('addCommentColumnsToAdminPanel');
                }

                // register taxonomy meta
                Profiler::start('registerTaxonomyMeta');
                $this->registerTaxonomyMeta();
                Profiler::stop('registerTaxonomyMeta');

                // add columns to show in the CPT list panel
                if(is_admin()){
                    Profiler::start('addCustomPostTypeColumnsToAdminPanel');
                    $this->addCustomPostTypeColumnsToAdminPanel();
                    Profiler::stop('addCustomPostTypeColumnsToAdminPanel');

                    // add columns to show in the taxonomy list panel
                    Profiler::start('addTaxonomyColumnsToAdminPanel');
                    $this->addTaxonomyColumnsToAdminPanel();
                    Profiler::stop('addTaxonomyColumnsToAdminPanel');
                }

                // register user meta
                Profiler::start('registerUserMeta');
                $this->registerUserMeta();
                Profiler::stop('registerUserMeta');

                // add user meta columns to show in the admin panel
                if(is_admin()){
                    Profiler::start('addUserMetaColumnsToShow');
                    $this->addUserMetaColumnsToShow();
                    Profiler::stop('addUserMetaColumnsToShow');
                }
            }

            // functions and hooks
            Profiler::start('includeFunctions');
            $this->includeFunctions();
            Profiler::stop('includeFunctions');

            // run integrations
            Profiler::start('runIntegrations');
            $this->runIntegrations();
            Profiler::stop('runIntegrations');
        }
    }
}
