<?php


/**
 * @class FacetCounts
 */
class FacetCounts extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Counts', 'fwp' ),
			'description'     => __( 'Module to use display a result counts.', 'fwp' ),
			'category'        => __( 'FacetWP Modules', 'fwp' ),
			'partial_refresh' => true,
		) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module( 'FacetCounts', array() );