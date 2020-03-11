<?php
namespace ZookeeperNodeCache\Tools;

use ZookeeperNodeCache\CacheStrategys\CacheAbs;
use ZookeeperNodeCache\CacheStrategys\FileCache;
use ZookeeperNodeCache\CacheStrategys\RedisCache;

/**
 * Class CommonFunctions
 *
 * @package ZookeeperNodeCache\Tools
 */
class CommonFunctions {

    /**
     * @param string $key
     *
     * @return \ZookeeperNodeCache\CacheStrategys\CacheAbs
     */
    public static function getService(string $key):CacheAbs
    {
        switch ($key) {
            case 'redis':
                return RedisCache::getInstance();
            case 'file':
                return FileCache::getInstance();
            default:
                return null;
        }
    }

    /**
     * 公共输出
     *
     * @param $msg
     */
    public static function commonOutput(string $msg):void
    {
        echo date('Y-m-d H:i:s'), ': ', $msg, ' ...', PHP_EOL;
    }

    /**
     * @param array $config
     */
    public static function putZkEnv(array $config):void
    {
        foreach ($config as $k => $v) {
            putenv("{$k}={$v}");
        }
    }

    /**
     * @param string $key
     */
    public static function delZkEnv(string $key):void
    {
        unset($_ENV[$key]);
    }

    /**
     * @param string $key
     */
    public static function getZkEnv(string $key):string
    {
        return getenv($key);
    }

    /**
     * 将 ZK 节点路径转换为缓存的 key
     *
     * @param string $zkFullPath
     *
     * @return string
     */
    public static function transToKey(string $zkFullPath): string
    {
        return self::getZkConfigCache('cache_prefix') . ':' . str_replace('/', ':', trim($zkFullPath, '/'));
    }

    /**
     * 获取zk配置缓存
     *
     * @param string $key
     *
     * @return string
     */
    public static function getZkConfigCache(string $key):string
    {
        return array_get(ZOOKEEPER_CACHE_STRATEGY_CONFIG, $key);
    }

    /**
     * 设置zk配置缓存
     */
    public static function setZkConfigCache()
    {
        defined('ZOOKEEPER_CACHE_STRATEGY_CONFIG') or define('ZOOKEEPER_CACHE_STRATEGY_CONFIG', config('zookeepercache'));
    }

    /**
     * 获取默认文件路径
     *
     * @return string
     */
    public static function getDefaultFilePath():string
    {
        return storage_path() . DIRECTORY_SEPARATOR . 'zkconfig' . DIRECTORY_SEPARATOR . 'config.json';
    }

}