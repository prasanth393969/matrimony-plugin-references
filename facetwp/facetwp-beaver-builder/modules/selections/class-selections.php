<?php


/**
 * @class FacetSelections
 */
class FacetSelections extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Selections', 'fwp' ),
			'description'     => __( 'Module to use display user selections.', 'fwp' ),
			'category'        => __( 'FacetWP Modules', 'fwp' ),
			'partial_refresh' => true,
		) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module( 'FacetSelections', array() );