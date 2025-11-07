<?php

namespace ACPT\Utils\Cache;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Repository\MetaRepository;
use ACPT\Includes\ACPT_Plugin;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;

class FlexibleFieldCache
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
     * @param $blockId
     * @param $mediaType
     * @param $parentName
     * @param $elementIndex
     * @param $blockIndex
     * @param $realBlockId
     * @return mixed|string|string[]|null
     */
    public function get(
        $blockId,
        $mediaType,
        $parentName,
        $elementIndex,
        $blockIndex,
        $realBlockId
    )
    {
        if($this->cacheEnabled === false){
            return null;
        }

        if($this->cache === false){
            return null;
        }

        try {
            $cacheKey = md5($blockId."-".$mediaType);
            $cachedElement = $this->cache->getItem($cacheKey);

            if (!$cachedElement->isHit()) {
                return null;
            }

            $randId = Strings::generateRandomId();
            $elementId = 'element-'.rand(999999,111111);

            $template = $cachedElement->get();
            $template = str_replace("{blockIndex}", $blockIndex, $template);
            $template = str_replace("{elementIndex}", $elementIndex, $template);
            $template = str_replace("{parentName}", $parentName, $template);
            $template = str_replace("{realBlockId}", $realBlockId, $template);
            $template = str_replace("{sortableLiId}", "sortable-li-".$blockId."-".$blockIndex, $template);
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
     * Save a flexible field template into cache
     *
     * @param $fields
     * @param $blockId
     * @param $mediaType
     * @param $parentName
     * @param $elementIndex
     * @param $blockIndex
     * @param $realBlockId
     */
    public function save(
        $fields,
        $blockId,
        $mediaType,
        $parentName,
        $elementIndex,
        $blockIndex,
        $realBlockId
    )
    {
        if($this->cacheEnabled === false){
            return;
        }

        if($this->cache === false){
            return;
        }

        try {
            $replaced = str_replace($parentName, '{parentName}', $fields);
            $replaced = str_replace($realBlockId, '{realBlockId}', $replaced);
            $replaced = str_replace("sortable-li-".$blockId."-".$blockIndex, '{sortableLiId}', $replaced);
            $replaced = str_replace("{parentName}[blocks][".$blockIndex."]", "{parentName}[blocks][{blockIndex}]", $replaced);
            $replaced = str_replace("[".$elementIndex."]", "[{elementIndex}]", $replaced);
            $replaced = preg_replace('/data-index="(\d+)"/', 'data-index="{elementIndex}"', $replaced);
            $replaced = preg_replace('/data-parent-index="(\d+)"/', 'data-parent-index="{elementIndex}"', $replaced);
            $replaced = preg_replace('/data-conditional-rules-field-index="(\d+)"/', 'data-conditional-rules-field-index="{elementIndex}"', $replaced);
            $replaced = preg_replace('/element-(\d+)/', '{element-id}', $replaced);
            $replaced = preg_replace('/<div class=\'acpt-admin-meta-row\' id=\'id_(\d+)\'>/', '{admin-meta-row_id}', $replaced);
            $replaced = preg_replace('/id_(\d+)/', '{id}', $replaced);

            $cacheKey = md5($blockId."-".$mediaType);
            $cacheTtl = 3600; // 1 hour

            $cachedElement = $this->cache->getItem($cacheKey);
            $tag = MetaRepository::class;
            $cachedElement->addTag($tag)->set($replaced)->expiresAfter($cacheTtl);
            $this->cache->save($cachedElement);

        }
        catch (\Exception $exception){ do_action("acpt/error", $exception); }
        catch (\Psr\Cache\InvalidArgumentException $exception){ do_action("acpt/error", $exception); }
    }
}