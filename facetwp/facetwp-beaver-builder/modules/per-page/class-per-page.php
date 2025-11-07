<?php


/**
 * @class FacetPerPage
 */
class FacetPerPage extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Per Page', 'fwp' ),
			'description'     => __( 'Module to use display a Per Page selector.', 'fwp' ),
			'category'        => __( 'FacetWP Modules', 'fwp' ),
			'partial_refresh' => true,
		) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module( 'FacetPerPage', array() );