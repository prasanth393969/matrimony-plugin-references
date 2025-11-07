<?php

namespace ACPT\Integrations\Divi;

use ACPT\Integrations\AbstractIntegration;
use ET\Builder\Packages\Module\Layout\Components\DynamicContent\DynamicContent;

class ACPT_Divi extends AbstractIntegration
{
	const MINIMUM_DIVI_VERSION = '4.19.3';

    /**
     * @var bool
     */
    private $is_divi_5 = false;

    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "divi";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		// 1. Check Divi theme
		$theme = wp_get_theme();
        $version = $this->getThemeVersion($theme);
        $this->is_divi_5 = version_compare($version, "5.0.0", ">=");

		if(( 'Divi' == $theme->name or 'Divi' == $theme->parent_theme )){
			return ACPT_ENABLE_META and $this->checkThemeVersion($theme, self::MINIMUM_DIVI_VERSION);
		}

		// 2. Check Divi builder
		$isActive = is_plugin_active( 'divi-builder/divi-builder.php' );

		if(!$isActive){
			return false;
		}

		return ACPT_ENABLE_META and $isActive;
	}

	/**
	 * @inheritDoc
	 */
	protected function runIntegration()
	{
		$this->registerDynamicFields();
		$this->registerDynamicTags();
	}

    /**
     * Register ACPT fields (DIVI 4 and 5)
     */
	private function registerDynamicFields()
    {
        define('ACPT_EXT_NAME', 'acpt-divi-extension');
        define('ACPT_EXT_TEXT_DOMAIN', 'acpt-divi-extension');

        // Include helper functions
        require_once plugin_dir_path( __FILE__ ) . 'includes/ACPT_Divi_Helper.php';

        // Run the Divi extension
        add_action( 'divi_extensions_init', function() {
            require_once plugin_dir_path( __FILE__ ) . 'includes/ACPT_Divi_Extension.php';
        });

        // Enqueue the assets
        require_once plugin_dir_path( __FILE__ ) . 'includes/ACPT_Divi_Assets.php';
        add_filter( 'et_required_module_assets', ['ACPT_Divi_Assets', 'enqueue_assets'], 10, 1 );

        // Run the dynamic content handler
        require_once plugin_dir_path( __FILE__ ) . 'includes/ACPT_Divi_Dynamic_Content_Fields.php';

        add_filter( 'et_builder_custom_dynamic_content_fields', [ 'ACPT_Divi_Dynamic_Content_Fields','get_fields'], 10, 3 );
        add_filter( 'et_builder_dynamic_content_meta_value', [ 'ACPT_Divi_Dynamic_Content_Fields','get_value'], 10, 3 );
        add_filter( 'et_builder_get_parent_modules', [ 'ACPT_Divi_Dynamic_Content_Fields','add_dynamic_support_for_gallery_field'], 10, 3 );
        add_filter( 'et_required_module_assets', ['ACPT_Divi_Assets', 'add_required_slugs'], 10, 2 );
    }

    /**
     * Dynamic data tags (only for DIVI 5)
     */
	private function registerDynamicTags()
    {
        if(!$this->is_divi_5){
            return;
        }

        require_once get_theme_file_path('includes/builder-5/server/vendor/autoload.php');
        require_once get_theme_file_path('includes/builder-5/server/Packages/Module/Layout/Components/DynamicContent/DynamicContent.php');
        require_once ACPT_PLUGIN_DIR_PATH . '/src/Integrations/Divi/includes/ACPT_Divi_Dynamic_Content_Tags.php';

        $dependency_tree->add_dependency(new \ACPT_Divi_Dynamic_Content_Tags());
        $dynamic_content = new DynamicContent( $dependency_tree );
        $dynamic_content->initialize();
    }
}