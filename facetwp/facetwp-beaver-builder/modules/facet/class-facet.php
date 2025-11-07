<?php


/**
 * @class FacetWPModule
 */
class FacetModule extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Facet', 'fwp' ),
			'description'     => __( 'Module to use display a Facet.', 'fwp' ),
			'category'        => __( 'FacetWP Modules', 'fwp' ),
			'partial_refresh' => true,
		) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module( 'FacetModule', array(
	'general' => array(
		'title'    => __( 'Facet', 'fl-builder' ),
		'sections' => array(
			'general' => array(
				'title'  => '',
				'fields' => array(
					'title' => array(
						'type'        => 'text',
						'label'       => __( 'Title', 'fwp' ),
						'placeholder' => __( 'Leave blank for no title.', 'fwp' ),
						'default'     => '',
					),
					'facet' => array(
						'type'        => 'select',
						'label'       => __( 'Facet', 'fwp' ),
						'placeholder' => __( 'Select a Facet', 'fwp' ),
						'options'     => load_facet_options(),
						'preview'     => array(
							'type' => 'refresh',
						),
					),
				),
			),
		),
	),
) );


function load_facet_options() {
	$facets  = FWP()->helper->get_facets();
	$options = array();
	foreach ( $facets as $facet ) {
		$options[ $facet['name'] ] = $facet['label'];
	}

	return $options;
}