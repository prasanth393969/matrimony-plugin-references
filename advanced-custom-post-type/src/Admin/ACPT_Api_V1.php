<?php

namespace ACPT\Admin;

use ACPT\Core\API\V1\Controllers\BlocksController;
use ACPT\Core\API\V1\Controllers\CustomPostTypeController;
use ACPT\Core\API\V1\Controllers\FilterQueryController;
use ACPT\Core\API\V1\Controllers\FormController;
use ACPT\Core\API\V1\Controllers\LicenseController;
use ACPT\Core\API\V1\Controllers\MetaController;
use ACPT\Core\API\V1\Controllers\OptionPageController;
use ACPT\Core\API\V1\Controllers\SchemaController;
use ACPT\Core\API\V1\Controllers\TaxonomyController;
use ACPT\Core\API\V1\Controllers\TemplateController;
use ACPT\Core\API\V1\Controllers\WooCommerceController;

class ACPT_Api_V1
{
    const BASE_V1 = 'acpt/v1';

    /**
     * Register REST routes
     */
    public function registerRestRoutes()
    {
    	// license
	    $this->registerRestRoute('/license/deactivate', 'POST', [new LicenseController(), 'deactivate'], false);

        // schema
        $this->registerRestRoute('/schema', 'GET', [new SchemaController(), 'schema'], false);

        // filter
        $this->registerRestRoute('/(?P<slug>[a-zA-Z0-9-_]+)/filter/query', 'POST', [new FilterQueryController(), 'search']);

        // Custom Post Types
        $this->registerRestRoute('/cpt', 'GET', [new CustomPostTypeController(), 'getAll']);
        $this->registerRestRoute('/cpt/(?P<slug>[a-zA-Z0-9-_]+)', 'GET', [new CustomPostTypeController(), 'get']);
        $this->registerRestRoute('/cpt', 'POST', [new CustomPostTypeController(), 'create']);
        $this->registerRestRoute('/cpt/(?P<slug>[a-zA-Z0-9-_]+)', 'DELETE', [new CustomPostTypeController(), 'delete']);
        $this->registerRestRoute('/cpt/(?P<slug>[a-zA-Z0-9-_]+)', 'PUT', [new CustomPostTypeController(), 'update']);

        // Option page
	    $this->registerRestRoute('/option-page', 'GET', [new OptionPageController(), 'getAll']);
	    $this->registerRestRoute('/option-page/(?P<slug>[a-zA-Z0-9-_]+)', 'GET', [new OptionPageController(), 'get']);
	    $this->registerRestRoute('/option-page', 'POST', [new OptionPageController(), 'create']);
	    $this->registerRestRoute('/option-page/(?P<slug>[a-zA-Z0-9-_]+)', 'DELETE', [new OptionPageController(), 'delete']);
	    $this->registerRestRoute('/option-page/(?P<slug>[a-zA-Z0-9-_]+)', 'PUT', [new OptionPageController(), 'update']);

        // Taxonomy
        $this->registerRestRoute('/taxonomy', 'GET', [new TaxonomyController(), 'getAll']);
        $this->registerRestRoute('/taxonomy/(?P<slug>[a-zA-Z0-9-_]+)', 'GET', [new TaxonomyController(), 'get']);
        $this->registerRestRoute('/taxonomy', 'POST', [new TaxonomyController(), 'create']);
        $this->registerRestRoute('/taxonomy/(?P<slug>[a-zA-Z0-9-_]+)', 'DELETE', [new TaxonomyController(), 'delete']);
        $this->registerRestRoute('/taxonomy/(?P<slug>[a-zA-Z0-9-_]+)', 'PUT', [new TaxonomyController(), 'update']);
        $this->registerRestRoute('/taxonomy/assoc/(?P<slug>[a-zA-Z0-9-_]+)/(?P<cpt>[a-zA-Z0-9-_]+)', 'POST', [new TaxonomyController(), 'assocToPostType']);

	    // Meta
	    $this->registerRestRoute('/meta', 'GET', [new MetaController(), 'getAll']);
	    $this->registerRestRoute('/meta', 'POST', [new MetaController(), 'create']);
	    $this->registerRestRoute('/meta/(?P<id>[a-zA-Z0-9-_]+)', 'GET', [new MetaController(), 'get']);
	    $this->registerRestRoute('/meta/(?P<id>[a-zA-Z0-9-_]+)', 'DELETE', [new MetaController(), 'delete']);
	    $this->registerRestRoute('/meta/(?P<id>[a-zA-Z0-9-_]+)', 'PUT', [new MetaController(), 'update']);

	    // Form
	    $this->registerRestRoute('/form', 'GET', [new FormController(), 'getAll']);
	    $this->registerRestRoute('/form', 'POST', [new FormController(), 'create']);
	    $this->registerRestRoute('/form/(?P<id>[a-zA-Z0-9-_]+)', 'GET', [new FormController(), 'get']);
	    $this->registerRestRoute('/form/(?P<id>[a-zA-Z0-9-_]+)', 'DELETE', [new FormController(), 'delete']);
	    $this->registerRestRoute('/form/(?P<id>[a-zA-Z0-9-_]+)', 'PUT', [new FormController(), 'update']);

        // Blocks
        $this->registerRestRoute('/block', 'GET', [new BlocksController(), 'getAll']);
        $this->registerRestRoute('/block', 'POST', [new BlocksController(), 'create']);
        $this->registerRestRoute('/block/(?P<id>[a-zA-Z0-9-_]+)', 'GET', [new BlocksController(), 'get']);
        $this->registerRestRoute('/block/(?P<id>[a-zA-Z0-9-_]+)', 'DELETE', [new BlocksController(), 'delete']);
        $this->registerRestRoute('/block/(?P<id>[a-zA-Z0-9-_]+)', 'PUT', [new BlocksController(), 'update']);

        // WooCommerce
        $this->registerRestRoute('/woocommerce/product-data', 'GET', [new WooCommerceController(), 'getAll']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)', 'GET', [new WooCommerceController(), 'get']);
        $this->registerRestRoute('/woocommerce/product-data', 'POST', [new WooCommerceController(), 'create']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)', 'DELETE', [new WooCommerceController(), 'delete']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)', 'PUT', [new WooCommerceController(), 'update']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)/fields', 'GET', [new WooCommerceController(), 'getFields']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)/fields', 'POST', [new WooCommerceController(), 'createFields']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)/fields', 'PUT', [new WooCommerceController(), 'updateFields']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)/fields', 'DELETE', [new WooCommerceController(), 'deleteFields']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)/fields/(?P<field>[a-zA-Z0-9-_]+)', 'GET', [new WooCommerceController(), 'getField']);
        $this->registerRestRoute('/woocommerce/product-data/(?P<id>[a-zA-Z0-9-_]+)/fields/(?P<field>[a-zA-Z0-9-_]+)', 'DELETE', [new WooCommerceController(), 'deleteField']);
    }

    /**
     * @param string   $route
     * @param string   $methods
     * @param callable $callback
     * @param bool     $isASecuredRoute
     */
    private function registerRestRoute( $route, $methods, $callback, $isASecuredRoute = true)
    {
        $options['methods'] = $methods;
        $options['callback'] = $callback;
        $options['permission_callback'] = ($isASecuredRoute) ? [new ACPT_Api_Auth(), 'authenticate'] : '__return_true';

        register_rest_route( self::BASE_V1, $route, $options );
    }
}