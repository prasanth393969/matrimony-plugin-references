<?php

namespace ACPT\Integrations\WPAllImport\Addon;

use ACPT\Constants\FormatterFormat;
use ACPT\Core\Data\Import\MetadataImport;
use ACPT\Integrations\WPAllImport\Helper\RapidAddon;
use ACPT\Utils\Wordpress\Translator;

class WPAIAddon
{
	/**
	 * @var WPAIAddon
	 */
	protected static $instance;

	/**
	 * @var RapidAddon
	 */
	protected $addon;

	/**
	 * @var mixed|void
	 */
	private $logger;

	/**
	 * @return WPAIAddon
	 */
	static public function getInstance()
	{
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WPAIAddon constructor.
	 */
	protected function __construct()
	{
		// Init logger
		$this->initLogger();

		// Define the add-on
		$this->addon = new RapidAddon( 'ACPT Add-On', 'wpai_acpt_add_on' );
		$this->addon->add_field( 'acpt_meta', 'ACPT meta', 'text' );
		$this->addon->set_import_function( [ $this, 'import' ] );

		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Init the logger
	 */
	private function initLogger()
	{
		$logger = function($m) {
			print("<p>[". date("H:i:s") ."] ".wp_all_import_filter_html_kses($m)."</p>\n");
		};

		$this->logger = apply_filters('wp_all_import_logger', $logger);
	}

	/**
	 * Run the plugin
	 */
	public function admin_init()
	{
		$this->addon->run();
	}

	/**
	 * @param $message
	 */
	private function log($message)
	{
		call_user_func($this->logger, Translator::translate($message));
	}

	/**
	 * @param $newItemId
	 * @param $data
	 * @param $importOptions
	 */
	public function import( $newItemId, $data, $importOptions )
	{
		$this->log('ACPT meta import start');

		try {
			MetadataImport::import($newItemId, FormatterFormat::XML_FORMAT, $data['acpt_meta']);
		} catch (\Exception $exception){

            do_action("acpt/error", $exception);

			$this->log($exception->getMessage());
			return;
		}

		$this->log('ACPT meta import end');
	}
}
