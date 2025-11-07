<?php

namespace ACPT\Utils\Cache;

use ACPT\Core\Helper\Strings;
use ACPT\Includes\ACPT_Plugin;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;

class RepeaterFieldCache
{
    const CACHE_TTL = 3600; // 1 hour

    /**
     * @var bool
     */
    private $cacheEnabled = false;

    /**
     * @var ExtendedCacheItemPoolInterface
     */
    private $cache;

    /**
     * RepeaterFieldCache constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $cache = ACPT_Plugin::getCache();

        if($cache !== null){
            $this->cacheEnabled = true;
            $this->cache = $cache;
        }
    }

    /**
     * @param $id
     * @param $mediaType
     * @param $parentIndex
     * @param $parentName
     * @param $index
     * @param null $formId
     * @return mixed|string|string[]|null
     */
    public function get(
        $id,
        $mediaType,
        $parentIndex,
        $parentName,
        $index,
        $formId = null
    )
    {
        try {
            if($this->cacheEnabled === false){
                return null;
            }

            if($this->cache === false){
                return null;
            }

            $cacheKey = md5($id."-".$mediaType);
            $cacheKey .= !empty($formId) ? "_".$formId : "";
            $cachedElement = $this->cache->getItem($cacheKey);

            if (!$cachedElement->isHit()) {
                return null;
            }

            $randId = Strings::generateRandomId();
            $elementId = 'element-'.rand(999999,111111);

            $template = $cachedElement->get();
            $template = str_replace("{index}", $index, $template);
            $template = str_replace("{parentIndex}", $parentIndex, $template);
            $template = str_replace("{parentName}", $parentName, $template);
            $template = str_replace("{element-id}", $elementId, $template);
            $template = str_replace("{admin-meta-row_id}", "<div class='acpt-admin-meta-row' id='".Strings::generateRandomId()."'>", $template);
            $template = str_replace("{id}", $randId, $template);

            return $template;

        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return null;
        } catch (\Psr\Cache\InvalidArgumentException $exception){

            do_action("acpt/error", $exception);

            return null;
        }
    }

    /**
     * Save a repeater field template into cache
     *
     * @param $fields
     * @param $id
     * @param $mediaType
     * @param $parentIndex
     * @param $parentName
     * @param $index
     * @param $formId
     */
    public function save(
        $fields,
        $id,
        $mediaType,
        $parentIndex,
        $parentName,
        $index,
        $formId = null
    )
    {
        if($this->cacheEnabled === false){
            return;
        }

        try {
            $replaced = str_replace($parentName, '{parentName}', $fields);
            $replaced = preg_replace('/'.$parentIndex.'/', '{parentIndex}', $replaced);
            $replaced = str_replace("[".$index."]", "[{index}]", $replaced);
            $replaced = str_replace("#".$index, '#{index}', $replaced);
            $replaced = preg_replace('/element-(\d+)/', '{element-id}', $replaced);
            $replaced = preg_replace('/data-index="(\d+)"/', 'data-index="{index}"', $replaced);
            $replaced = preg_replace('/data-parent-index="(\d+)"/', 'data-parent-index="{index}"', $replaced);
            $replaced = preg_replace('/data-conditional-rules-field-index="(\d+)"/', 'data-conditional-rules-field-index="{index}"', $replaced);
            $replaced = preg_replace('/<div class=\'acpt-admin-meta-row\' id=\'id_(\d+)\'>/', '{admin-meta-row_id}', $replaced);
            $replaced = preg_replace('/id_(\d+)/', '{id}', $replaced);

            $cacheKey = md5($id."-".$mediaType);
            $cacheKey .= !empty($formId) ? "_".$formId : "";

            $cachedElement = $this->cache->getItem($cacheKey);
            $tag = md5(RepeaterFieldCache::class);
            $cachedElement->addTag($tag)->set($replaced)->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cachedElement);

        }
        catch (\Exception $exception){ do_action("acpt/error", $exception); }
        catch (\Psr\Cache\InvalidArgumentException $exception){ do_action("acpt/error", $exception); }
    }
}