<?php

namespace ACPT\Admin;

use ACPT\Utils\Http\ACPTApiClient;
use ACPT\Utils\Wordpress\Transient;

class ACPT_Updater
{
	/**
	 * @var string
	 */
    private $file;

	/**
	 * @var array
	 */
    private $plugin;

	/**
	 * @var string
	 */
    private $basename;

	/**
	 * @var bool
	 */
    private $active;

    /**
     * ACPT_Updater constructor.
     * @param $file
     */
    public function __construct( $file )
    {
        $this->file = $file;
        add_action( 'admin_init', [ $this, 'setPluginProperties' ] );

        return $this;
    }

    /**
     * Set the plugin properties
     */
    public function setPluginProperties()
    {
        $this->plugin   = get_plugin_data( $this->file );
        $this->basename = plugin_basename( $this->file );
        $this->active   = is_plugin_active( $this->basename );
    }

    /**
     * init the plugin
     */
    public function initialize()
    {
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'modifyTransient' ], 10, 1 );
        add_filter( 'plugins_api', [ $this, 'pluginPopup' ], 10, 3);
        add_filter( 'upgrader_post_install', [ $this, 'afterInstall' ], 10, 3 );
    }

    /**
     * @param $transient
     *
     * @return mixed
     * @throws \Exception
     */
    public function modifyTransient( $transient )
    {
        if( !ACPT_IS_LICENSE_VALID ) {
            return $transient;
        }

        if(is_string($transient) or is_object($transient)){
            if( property_exists( $transient, 'checked') ) {
                if( $checked = $transient->checked ) {

                    $pluginInfo = $this->getPluginInfo();

                    if($this->plugin === null){
                        $this->plugin = get_plugin_data($this->file);
                    }

                    if(is_array($pluginInfo) and isset($pluginInfo['version'])){
                        $version = $pluginInfo['version'];
                        $version = str_replace(['v.', 'v'], '', $version);

                        $outOfDate = version_compare( $version, $this->plugin["Version"], 'gt' );
                        if( $outOfDate ) {
                            $newFiles = ACPT_License_Manager::getDownloadLink();
                            $slug = current( explode('/', $this->basename ) );

                            $plugin = [
                                'url' => $this->plugin["PluginURI"],
                                'slug' => $slug,
                                'package' => $newFiles,
                                'new_version' => $version
                            ];

                            $transient->response[ $this->basename ] = (object) $plugin;
                        }
                    }
                }
            }
        }

        return $transient;
    }

    /**
     * @param $result
     * @param $action
     * @param $args
     *
     * @return object
     * @throws \Exception
     */
    public function pluginPopup( $result, $action, $args )
    {
        if( ACPT_IS_LICENSE_VALID and ! empty( $args->slug ) ) {
            if( $args->slug == current( explode( '/' , $this->basename ) ) ) {

	            if($this->plugin === null){
		            $this->plugin = get_plugin_data($this->file);
	            }

                $pluginInfo = $this->getPluginInfo();
                $version = $pluginInfo['version'];
                $version = str_replace(['v.', 'v'], '', $version);
                $downloadLink = ACPT_License_Manager::getDownloadLink();
                $updates = '<div><h3>ACPT v'.$version.'</h3><p>If you can\'t automatically update the plugin, you can download it here and manually load it on your website.</p> <a href="'
                        .$downloadLink.'">Download here</a></div>';

                $plugin = [
                    'name'              => $this->plugin["Name"],
                    'slug'              => $this->basename,
                    'version'           => $version,
                    'author'            => $this->plugin["AuthorName"],
                    'author_profile'    => $this->plugin["AuthorURI"],
                    'last_updated'      => $pluginInfo['published_at'],
                    'homepage'          => $this->plugin["PluginURI"],
                    'short_description' => $this->plugin["Description"],
                    'sections'          => [
                        'Description'   => $this->plugin["Description"],
                        'Updates'       => $updates,
                    ],
                    'download_link'     => $downloadLink
                ];

                return (object)$plugin;
            }
        }

        return $result;
    }

    /**
     * @param $response
     * @param $hook_extra
     * @param $result
     *
     * @return mixed
     */
    public function afterInstall( $response, $hook_extra, $result )
    {
        global $wp_filesystem;

        $install_directory = plugin_dir_path( $this->file );
        $wp_filesystem->move( $result['destination'], $install_directory );
        $result['destination'] = $install_directory;

        if ( $this->active ) {
            activate_plugin( $this->basename );
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getPluginInfo()
    {
        $transientKey = 'advanced-custom-post-type-info';
        $cachedPluginInfo = get_transient( $transientKey );
	    $licenseActivation = ACPT_License_Manager::getLicense();
	    $pluginInfo = [];

        if(false === $cachedPluginInfo or '' === $cachedPluginInfo){

            try {
            	$payload = [];
            	if(!empty($licenseActivation) and isset($licenseActivation['user_id'])){
					$payload = [
						'user_id' => $licenseActivation['user_id'],
                        'enable_beta' => ACPT_ENABLE_BETA,
					];
	            }

	            $pluginInfo = ACPTApiClient::call('/plugin/fetch', $payload);
	            Transient::set( $transientKey, $pluginInfo, 3600 );
            } catch (\Exception $exception){
                do_action("acpt/error", $exception);
	            Transient::set( $transientKey, [], 3600 ); // no-connection
            }

            return $pluginInfo;
        }

        return $cachedPluginInfo;
    }

    /**
     * Send insights
     * (once a day)
     */
    public function sendInsights()
    {
        $transientKey = 'advanced-custom-post-type-insights';
        $cachedPluginInfo = get_transient( $transientKey );

        if(false === $cachedPluginInfo){

            global $wpdb;
            global $wp_version;
            $licenseActivation = ACPT_License_Manager::getLicense();

            if(!empty($licenseActivation)){
	            try {
		            $insights = ACPTApiClient::call('/license/insights/send', [
			            'id' => $licenseActivation['activation_id'],
			            'insights' => [
				            [
					            'key' => 'WP_VERSION',
					            'value' => $wp_version
				            ],
				            [
					            'key' => 'PHP_VERSION',
					            'value' => phpversion()
				            ],
				            [
					            'key' => 'MYSQL_VERSION',
					            'value' => $wpdb->db_version()
				            ],
				            [
					            'key' => 'PLUGIN_VERSION',
					            'value' => ACPT_PLUGIN_VERSION
				            ],
			            ],
		            ]);

		            Transient::set( $transientKey, $insights, 86400 );
	            } catch (\Exception $exception){
                    do_action("acpt/error", $exception);
		            Transient::set( $transientKey, [], 86400 ); // no-connection
	            }
            } else {
	            Transient::set( $transientKey, [], 86400 ); // not-valid license?
            }
        }
    }
}