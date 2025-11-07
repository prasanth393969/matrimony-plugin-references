<?php

namespace ACPT\Admin;

abstract class ACPT_Api
{
    /**
     * @param     $message
     * @param int $code
     *
     * @return \WP_Error
     */
    protected function restError($message, $code = 500)
    {
        return new \WP_Error( "rest_error", $message, ['status' => $code ] );
    }
}
