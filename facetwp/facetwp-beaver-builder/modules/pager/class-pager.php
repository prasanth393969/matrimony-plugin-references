<?php


/**
 * @class FacetPager
 */
class FacetPager extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Pager', 'fwp' ),
			'description'     => __( 'Module to use display a pager.', 'fwp' ),
			'category'        => __( 'FacetWP Modules', 'fwp' ),
			'partial_refresh' => true,
		) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module( 'FacetPager', array() );