<?php

namespace ACPT\Integrations\WooCommerce;

use ACPT\Includes\ACPT_DB;
use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\WooCommerce\Ajax\WooCommerceAjax;
use ACPT\Integrations\WooCommerce\Filters\WooCommerceFilters;
use ACPT\Integrations\WooCommerce\Generators\WooCommerceProductData;
use ACPT\Integrations\WooCommerce\Generators\WooCommerceProductVariationMetaGroups;

class ACPT_WooCommerce extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "woocommerce";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
        return ACPT_ENABLE_META and is_plugin_active( 'woocommerce/woocommerce.php');
    }

    /**
     * Public facade for ACPT_WooCommerce::isActive() method
     *
     * @return bool
     */
    public static function active()
    {
        return (new ACPT_WooCommerce)->isActive();
    }

    /**
     * @inheritDoc
     */
    protected function runIntegration()
    {
        if(!ACPT_DB::tableExists("TABLE_WOOCOMMERCE_PRODUCT_DATA")){
            ACPT_DB::removeOrCreateFeatureTables("woocommerce");
        }

        (new WooCommerceProductData())->generate();
        (new WooCommerceProductVariationMetaGroups())->generate();
        (new WooCommerceFilters())->run();
        (new WooCommerceAjax())->routes();
    }
}
