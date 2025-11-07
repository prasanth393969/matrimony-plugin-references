<?php

namespace ACPT\Utils\PHP;

class Cookie
{
    /**
     * @param $key
     * @param $value
     * @param $time
     */
    public static function set($key, $value, $time = 0)
    {
        setcookie($key, serialize($value), $time);
    }

    /**
     * @param $key
     */
    public static function delete($key)
    {
        setcookie($key, "", time()-3600);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public static function get($key)
    {
        if(isset($_COOKIE[$key])){
            return unserialize( $_COOKIE[$key]);
        }

        return null;
    }
}