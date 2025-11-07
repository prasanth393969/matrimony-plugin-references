<?php


/**
 * @class FacetTemplate
 */
class FacetTemplate extends FLBuilderModule {

	/**
	 * @method __construct
	 */
	public function __construct() {
		parent::__construct( array(
			'name'            => __( 'Template', 'fwp' ),
			'description'     => __( 'Module to display a template.', 'fwp' ),
			'category'        => __( 'FacetWP Modules', 'fwp' ),
			'partial_refresh' => true,
		) );
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module( 'FacetTemplate', array(
	'general' => array(
		'title'    => __( 'Facet Template', 'fl-builder' ),
		'sections' => array(
			'general' => array(
				'title'  => '',
				'fields' => array(
					'template' => array(
						'type'        => 'select',
						'label'       => __( 'Template', 'fwp' ),
						'placeholder' => __( 'Select a Template', 'fwp' ),
						'options'     => load_facet_templates(),
						'preview'     => array(
							'type' => 'refresh',
						),
					),
				),
			),
		),
	),
) );

function load_facet_templates() {
	$templates = FWP()->helper->get_templates();
	$options   = array(
		0 => '',
	);
	foreach ( $templates as $template ) {
		$options[ $template['name'] ] = $template['label'];
	}

	return $options;
}