<?php

namespace ACPT\Integrations;

abstract class AbstractIntegration
{
    /**
     * Integration name
     *
     * @return string
     */
    protected abstract function name();

    /**
     * Check if the corresponding plugin/theme is active
     *
     * @return bool
     */
    protected abstract function isActive();

    /**
     * Run the integration
     *
     * @return mixed
     */
    protected abstract function runIntegration();

    /**
     * Run the code
     */
    public function run()
    {
        //
        // External filter to override $this->isActive() check.
        //
        // Example: acpt_is_elementor_pro_active
        //
        $isActiveFromFilter = apply_filters( 'acpt_is_'.$this->name().'_active', false );

        if($isActiveFromFilter === true){
            $isActive = true;
        } else {
            $isActive = $this->isActive();
        }

        if($isActive){
            $this->runIntegration();
        }
    }

    /**
     * @param \WP_Theme $theme
     *
     * @return false|string
     */
    protected function getThemeVersion(\WP_Theme $theme)
    {
        return $theme->parent_theme ? $theme->parent()->get('Version') : $theme->get('Version');
    }

	/**
	 * @param \WP_Theme $theme
	 * @param $minimumVersion
	 *
	 * @return bool|int
	 */
    protected function checkThemeVersion(\WP_Theme $theme, $minimumVersion)
    {
	    $version = $this->getThemeVersion($theme);

	    if(!$version){
		    return false;
	    }

	    return version_compare( $version, $minimumVersion, '>=' );
    }
}