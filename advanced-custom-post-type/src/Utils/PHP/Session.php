<?php

namespace ACPT\Utils\PHP;

class Session
{
	/**
	 * Close a session
	 */
	public static function close()
	{
		session_write_close();
	}

	/**
	 * Start a session
	 */
	public static function start()
	{
		if (session_status() === PHP_SESSION_NONE) {
            $sessionId = $_COOKIE[session_name()] ?? null;

            if ($sessionId and !preg_match('/^[a-zA-Z0-9,-]{22,250}$/', $sessionId)) {
                // the session ID in the header is invalid, create a new one
                session_id(session_create_id());
            }

			session_start();
		}
	}

    /**
     * @param bool $destroy
     * @param int|null $lifetime
     * @return bool
     */
    public function regenerate(bool $destroy = false, ?int $lifetime = null): bool
    {
        // Cannot regenerate the session ID for non-active sessions.
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            return false;
        }

        if (headers_sent()) {
            return false;
        }

        if (null !== $lifetime and $lifetime != \ini_get('session.cookie_lifetime')) {
            ini_set('session.cookie_lifetime', $lifetime);
            $this->start();
        }

        return session_regenerate_id($destroy);
    }

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public static function has($key)
	{
		return isset($_SESSION[$key]);
	}

	/**
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public static function get($key)
	{
		if(!self::has($key)){
			return null;
		}

		return unserialize($_SESSION[$key]);
	}

	/**
	 * @param $key
	 */
	public static function flush($key)
	{
		unset($_SESSION[$key]);
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public static function set($key, $value)
	{
		$_SESSION[$key] = serialize($value);
	}
}