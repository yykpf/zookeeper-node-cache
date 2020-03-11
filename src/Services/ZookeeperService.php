<?php
namespace ZookeeperNodeCache\Services;

use ZookeeperNodeCache\CacheStrategys\CacheAbs;
use ZookeeperNodeCache\CacheStrategys\EnvCache;
use ZookeeperNodeCache\CacheStrategys\NullCache;
use ZookeeperNodeCache\Tools\CommonFunctions;

/**
 * zk服务调用
 *
 * @package ZookeeperNodeCache\Services
 */
class ZookeeperService {

    /**
     * 获取节点值
     *
     * @param string $key
     *
     * @return string
     */
    public function getNode(string $node, string $default = ''):string
    {
        CommonFunctions::setZkConfigCache(); // 缓存zk配置

        $cache = CommonFunctions::getService(CommonFunctions::getZkConfigCache('cache_mode')); // 获取缓存策略
        if ($cache instanceof NullCache) {
            // 调用zk原始获取
            return ZookeeperBaseService::getInstance()->getRetryNodeValue($node, $default);
        }

        return $this->getCache($cache, $node);
    }

    /**
     * 获取缓存
     *
     * @param \ZookeeperNodeCache\CacheStrategys\CacheAbs $cache
     * @param string                                      $node
     *
     * @return string
     */
    private function getCache(CacheAbs $cache, string $node):string
    {
        $key = $this->getKey($node); // 获取key值
        if ($value = EnvCacheService::getInstance()->getCacheConf($key)) { // 是否存在env变量
            return (string) $value;
        }

        return (string) $cache->getCacheConf($key);
    }

    /**
     * 获取需要的key
     *
     * @param string $node
     *
     * @return string
     */
    private function getKey(string $node):string
    {
        // 获取保存的键
        return CommonFunctions::getZkRootPath('zk_root_path') . '/' . trim($node, '/');
    }

    /**
     * 目前只支持 file 方式的 env缓存
     *
     * @return \ZookeeperNodeCache\Services\viod
     */
    public function putEnv():viod
    {
        $cache = CommonFunctions::getService(CommonFunctions::getZkConfigCache('cache_mode')); // 获取缓存策略
        if (!$cache instanceof NullCache) {
            EnvCacheService::getInstance()->setCacheConf($cache);
        }
    }
}