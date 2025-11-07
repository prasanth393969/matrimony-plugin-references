<?php

namespace ACPT\Integrations\Etch;

use ACPT\Integrations\AbstractIntegration;
use ACPT\Integrations\Etch\Provider\EtchProvider;

class ACPT_Etch extends AbstractIntegration
{
    /**
     * @inheritDoc
     */
    protected function name()
    {
        return "etch";
    }

    /**
     * @inheritDoc
     */
    protected function isActive()
    {
        if(!ACPT_ENABLE_META){
            return false;
        }

        return is_plugin_active( 'etch/etch.php' );
    }

    /**
     * @inheritDoc
     */
    protected function runIntegration()
    {
        add_filter('etch/add_dynamic_data', function(array $data, string $objectType, int $objectId, ?string $taxonomy = null) {
            try {

                $etchProvider = new EtchProvider($data, $objectType, $objectId, $taxonomy);

                if(!isset($data['acpt'])){
                    $data['acpt'] = [];
                }

                $data['acpt'] = array_merge($etchProvider->fields(), $data['acpt']);

                return $data;
            } catch (\Exception $exception){
                do_action("acpt/error", $exception);

                return $data;
            }
        }, 10, 4);
    }
}