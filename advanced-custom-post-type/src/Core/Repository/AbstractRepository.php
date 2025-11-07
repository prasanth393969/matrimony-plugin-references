<?php

namespace ACPT\Core\Repository;

use ACPT\Core\Validators\ArgumentsArrayValidator;
use ACPT\Includes\ACPT_Plugin;
use ACPT\Utils\PHP\Arrays;

abstract class AbstractRepository
{
    const CACHE_KEY_PREFIX = "AbstractRepository_";
    const CACHE_TTL = 3600; // 1 hour

	/**
	 * @param array $mandatoryKeys
	 * @param array $args
	 * @throws \Exception
	 */
	protected static function validateArgs(array $mandatoryKeys = [], array $args = [])
	{
		$validator = new ArgumentsArrayValidator();

		if(!$validator->validate($mandatoryKeys, $args)){
			throw new \Exception('Invalid parameters. Required: ['.Arrays::toPlainText($mandatoryKeys).']. Provided: ['.Arrays::toPlainText($args).']');
		}
	}

    /**
     * @param $identifier
     *
     * @return mixed|null
     */
	protected static function fromCache($identifier)
    {
        $cache = ACPT_Plugin::getCache();

        if($cache === null){
            return null;
        }

        try {
            $cacheKey = md5(self::CACHE_KEY_PREFIX.$identifier);
            $cachedElement = $cache->getItem($cacheKey);

            if (!$cachedElement->isHit()) {
                return null;
            }

            return $cachedElement->get();
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return null;
        } catch (\Psr\Cache\InvalidArgumentException $exception){

            do_action("acpt/error", $exception);

            return null;
        }
    }

    /**
     * @param $identifier
     * @param $value
     */
    protected static function saveInCache( $identifier, $value)
    {
        $cache = ACPT_Plugin::getCache();

        if($cache === null){
            return;
        }

        try {
            $cacheKey = md5(self::CACHE_KEY_PREFIX.$identifier);
            $cachedElement = $cache->getItem($cacheKey);
            $tag = md5(static::class);
            $cachedElement->addTag($tag)->set($value)->expiresAfter(self::CACHE_TTL);
            $cache->save($cachedElement);
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        } catch (\Psr\Cache\InvalidArgumentException $exception){
            do_action("acpt/error", $exception);
        }
    }
}