<?php

namespace ACPT\Integrations\Yoast;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Yoast\Provider\FieldProvider;

class ACPT_Yoast extends AbstractIntegration
{
	/**
	 * @var array
	 */
	private array $data = [];

	/**
	 * ACPT_Yoast constructor.
	 */
	public function __construct()
	{
		if($this->isActive()){
			$this->data = FieldProvider::getData();
		}
	}

    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "yoast";
    }

	/**
	 * @inheritDoc
	 */
	protected function isActive()
	{
		$isActive = is_plugin_active( 'wordpress-seo/wp-seo.php' ) or is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' );

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
		add_action('wpseo_register_extra_replacements', [new ACPT_Yoast(), 'registerCustomVariables']);
		add_action('admin_footer', [new ACPT_Yoast(), 'enqueueScripts'] );
	}

	/**
	 * Back-end
	 *
	 * Define the action for register yoast_variable replacements
	 */
	public function registerCustomVariables()
	{
		if(function_exists('wpseo_register_var_replacement')){
			foreach ($this->data as $datum){
				wpseo_register_var_replacement( $datum['snippet'], function () use ($datum){
					return $datum['replace'];
				}, 'advanced', $datum['help'] );
			}
		}
	}

	/**
	 * Front-end (live preview)
	 *
	 * Enqueue JS scripts
	 */
	public function enqueueScripts()
	{
		wp_register_script( 'yoast-inline-js-run', '', [], '', true );
		wp_enqueue_script('yoast-inline-js-run');
		wp_add_inline_script( 'yoast-inline-js-run', '
			const replacementsMap = '.json_encode($this->data).';
		');

		wp_enqueue_script( 'yoast-var-replacer', plugins_url( 'advanced-custom-post-type/src/Integrations/Yoast/assets/js/yoast-var-replacer.js'), [ 'jquery' ] );
	}
}