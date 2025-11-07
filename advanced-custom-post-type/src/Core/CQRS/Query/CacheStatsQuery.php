<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Core\Helper\Strings;
use ACPT\Includes\ACPT_Plugin;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Entities\DriverStatistic;

class CacheStatsQuery implements QueryInterface
{
    /**
     * @inheritDoc
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     */
    public function execute()
    {
        $cache = ACPT_Plugin::getCache();

        if(!$cache){
            return [];
        }

        $stats = $cache->getStats();

        return $this->formatStats($cache, $stats);
    }

    /**
     * @param ExtendedCacheItemPoolInterface $cache
     * @param DriverStatistic                $stats
     *
     * @return array
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     */
    private function formatStats(ExtendedCacheItemPoolInterface $cache, DriverStatistic $stats)
    {
        $data = explode(", ", $stats->getData());
        $items = [];

        foreach ($data as $key){
            $item = $cache->getItem($key);
            $items[] = [
                'key'  => $key,
                'tags' => $item->getTagsAsString(),
                'data' => $item->getDataAsJsonString(),
            ];
        }

        return [
            "info" => $stats->getInfo(),
            "size" => Strings::bytesToHumanReadable($stats->getSize()),
            "itemsCount" => count($data),
            "items" => $items,
        ];
    }
}
