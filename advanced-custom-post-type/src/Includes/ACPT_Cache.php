<?php

namespace ACPT\Includes;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Drivers\Memcached\Config as MemcachedConfig;
use Phpfastcache\Drivers\Predis\Config as PredisConfig;
use Phpfastcache\Drivers\Redis\Config as RedisConfig;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Phpfastcache\Exceptions\PhpfastcacheLogicException;

class ACPT_Cache
{
    const ALLOWED_DRIVERS = [
        'redis',
        'files',
        'memcached',
    ];

    /**
     * @var string
     */
    private string $driver;

    /**
     * @var array
     */
    private array $config;

    /**
     * ACPT_Cache constructor.
     *
     * @param string $driver
     * @param array  $config
     *
     * @throws \Exception
     */
    public function __construct($driver = "files", array $config = [])
    {
        if(!in_array($driver, self::ALLOWED_DRIVERS)){
            throw new \Exception($driver . " is not an allowed driver");
        }

        $this->driver = $driver;
        $this->config = $config;
    }

    /**
     * @throws \Exception
     */
    public function testConnection()
    {
        switch ($this->driver){
            case "memcached":
                $this->MemcachedCache();
                break;

            case "redis":
                $this->RedisCache();
                break;

            case "files":
                $folder = (isset($this->config['folder'])) ? $this->config['folder'] : "cache";
                $cacheDir = $this->cacheDir($folder);

                if(!is_dir($cacheDir)){
                    $createDir = mkdir($cacheDir, 0777, true);

                    if(!$createDir){
                        throw new \Exception($folder . " cannot be created. Please check your root permissions");
                    }

                    rmdir($cacheDir);
                } elseif(!is_writable($cacheDir)){
                    throw new \Exception($folder . " is not a writable directory. Please check your root permissions");
                }

                break;
        }
    }

    /**
     * @param string $driver
     * @param array  $config
     *
     * @return \Phpfastcache\Cluster\AggregatablePoolInterface|\Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheLogicException
     * @throws \ReflectionException
     */
    public function getInstance()
    {
        switch ($this->driver){

            // files
            case "files":
                $folder = (isset($this->config['folder'])) ? $this->config['folder'] : "cache";

                return $this->FilesCache($folder);

            // memcached
            case "memcached":
                try {
                    return $this->MemcachedCache();
                } catch (\Exception $exception){
                    return $this->FilesCache("cache");
                }

            // redis
            case "redis":
                try {
                    return $this->RedisCache();
                } catch (\Exception $exception){
                    return $this->FilesCache("cache");
                }
        }
    }

    /**
     * @param $folder
     *
     * @return string
     */
    private function cacheDir($folder)
    {
        return plugin_dir_path( __FILE__ ) . "../../". $folder;
    }

    /**
     * @param $folder
     *
     * @return \Phpfastcache\Cluster\AggregatablePoolInterface|\Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     * @throws PhpfastcacheDriverCheckException
     * @throws PhpfastcacheDriverException
     * @throws PhpfastcacheDriverNotFoundException
     * @throws PhpfastcacheInvalidArgumentException
     * @throws PhpfastcacheInvalidConfigurationException
     * @throws PhpfastcacheLogicException
     * @throws \ReflectionException
     */
    private function FilesCache($folder)
    {
        $cacheDir = $this->cacheDir($folder);

        if(!is_dir($cacheDir)){
            mkdir($cacheDir, 0777, true);
        }

        $config = new ConfigurationOption();
        $config->setPath($cacheDir);

        CacheManager::setDefaultConfig($config);

        return CacheManager::getInstance('files');
    }

    /**
     * @return \Phpfastcache\Cluster\AggregatablePoolInterface|\Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheLogicException
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function MemcachedCache()
    {
        if(!isset($this->config['memcached_host'])){
            throw new \Exception("Memcached host is not defined");
        }

        if(!isset($this->config['memcached_port'])){
            throw new \Exception("Memcached port is not defined");
        }

        return CacheManager::getInstance(
                'memcached',
                new MemcachedConfig([
                        "host" => $this->config['memcached_host'],
                        "port" => (int)$this->config['memcached_port'],
                ])
        );
    }

    /**
     * @return \Phpfastcache\Cluster\AggregatablePoolInterface|\Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverCheckException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheLogicException
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function RedisCache()
    {
        if(!isset($this->config['redis_host'])){
            throw new \Exception("Redis host is not defined");
        }

        if(!isset($this->config['redis_port'])){
            throw new \Exception("Redis port is not defined");
        }

        if(!isset($this->config['redis_database'])){
            throw new \Exception("Redis database is not defined");
        }

        try {
            return CacheManager::getInstance(
                'redis',
                new RedisConfig([
                    'host'     => $this->config['redis_host'],
                    'port'     => (int)$this->config['redis_port'],
                    "database" => (int)$this->config['redis_database'],
                ])
            );
        } catch (
            PhpfastcacheDriverCheckException |
            PhpfastcacheDriverException |
            PhpfastcacheDriverNotFoundException |
            PhpfastcacheInvalidArgumentException |
            PhpfastcacheInvalidConfigurationException |
            PhpfastcacheLogicException  $e)
        {
            return CacheManager::getInstance(
                    'predis',
                    new PredisConfig([
                        'host'     => $this->config['redis_host'],
                        'port'     => (int)$this->config['redis_port'],
                        "database" => (int)$this->config['redis_database'],
                    ])
            );
        }
    }
}