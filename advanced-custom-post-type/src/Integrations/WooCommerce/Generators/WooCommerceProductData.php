<?php

namespace ACPT\Integrations\WooCommerce\Generators;

use ACPT\Core\Generators\Meta\WooCommerceProductDataGenerator;
use ACPT\Core\Repository\WooCommerceProductDataRepository;

class WooCommerceProductData
{
    /**
     * Add product data
     */
    public function generate()
    {
        try {
            $WooCommerceProductData = WooCommerceProductDataRepository::get([]);

            if(!empty($WooCommerceProductData)){
                $wooCommerceProductDataGenerator = new WooCommerceProductDataGenerator($WooCommerceProductData);
                $wooCommerceProductDataGenerator->generate();
            }
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        }
    }

}
