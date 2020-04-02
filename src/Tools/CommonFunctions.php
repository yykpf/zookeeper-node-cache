<?php
namespace ZookeeperNodeCache\Tools;

use ZookeeperNodeCache\CacheStrategys\CacheAbs;
use ZookeeperNodeCache\CacheStrategys\FileCache;
use ZookeeperNodeCache\CacheStrategys\NullCache;
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
                return NullCache::getInstance();
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
        $node = str_replace(self::getWatchPath(), '', $zkFullPath);// 去除多余路径
        !empty($cachePrefix = self::getZkConfigCache('cache_prefix')) && $cachePrefix .= ':';

        return $cachePrefix . ':' . str_replace('/', ':', trim($node, '/'));
    }

    /**
     * 获取监听路径
     *
     * @return string
     */
    public static function getWatchPath():string
    {
        $path = '/' . trim(self::getZkRootPath('zk_root_path'), '/');
        !empty($version = trim(self::getZkRootPath('zk_version'), '/')) && $path .= '/' . $version . '/';

        return $path;// 路径加版本号
    }

    /**
     * 根路径
     *
     * @param string $path
     *
     * @return string
     */
    public static function getZkRootPath(string $path):string
    {
        return rtrim(self::getZkConfigCache($path), '/');
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
        return (string) array_get(ZOOKEEPER_NODE_CACHE_CONFIG, $key);
    }

    /**
     * 设置zk配置缓存
     */
    public static function setZkConfigCache()
    {
        defined('ZOOKEEPER_NODE_CACHE_CONFIG') or define('ZOOKEEPER_NODE_CACHE_CONFIG', @require_once self::getConfigPath());
    }

    /**
     * 获取配置文件路径
     *
     * @return string
     */
    public static function getConfigPath()
    {
        return base_path() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'zookeepercache.php';
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