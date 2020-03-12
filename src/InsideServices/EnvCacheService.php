<?php
namespace ZookeeperNodeCache\InsideServices;

use ZookeeperNodeCache\CacheStrategys\CacheAbs;
use ZookeeperNodeCache\Tools\InstanceTrait;
use ZookeeperNodeCache\Tools\CommonFunctions;

/**
 * Class EnvCacheService
 *
 * @package ZookeeperNodeCache\Services
 */
class EnvCacheService {

    use InstanceTrait;

    /**
     * 批量设置缓存
     *
     * @param \ZookeeperNodeCache\CacheStrategys\CacheAbs $cache
     *
     * @return array
     */
    public function setCacheConf(CacheAbs $cache): array
    {
        $config = $cache->getCacheAllConf();
        CommonFunctions::putZkEnv($config);

        return $config;
    }

    /**
     * 获取env变量
     *
     * @param string $zkFullPath
     *
     * @return string
     */
    public function getCacheConf(string $zkFullPath):string
    {
        $key = CommonFunctions::transToKey($zkFullPath);

        return (string) CommonFunctions::getZkEnv($key);
    }
}