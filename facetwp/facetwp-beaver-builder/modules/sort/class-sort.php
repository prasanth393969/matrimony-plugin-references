<?php


/**
 * @class FacetSort
 */
class FacetSort extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Sort', 'fwp' ),
			'description'     => __( 'Module to use display a Results Sort selector.', 'fwp' ),
			'category'        => __( 'FacetWP Modules', 'fwp' ),
			'partial_refresh' => true,
		) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module( 'FacetSort', array() );