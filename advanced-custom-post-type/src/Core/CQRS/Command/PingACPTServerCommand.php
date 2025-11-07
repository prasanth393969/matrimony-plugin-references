<?php

namespace ACPT\Core\CQRS\Command;

use ACPT\Utils\Http\ACPTApiClient;

class PingACPTServerCommand implements CommandInterface
{

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $call = ACPTApiClient::call("/ping");

        if(empty($call)){
            return "No response from acpt.io server";
        }

        if($call['response'] !== "pong"){

            if(isset($call['data']['status'])){
                $httpStatus = $call['data']['status'];

                if($httpStatus !== 200){
                    return "Got a ".$httpStatus." HTTP status from acpt.io server";
                }
            }

            return "Wrong response from acpt.io server";
        }

        return 'ok';
    }
}