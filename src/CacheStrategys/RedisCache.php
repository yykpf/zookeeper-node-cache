<?php
namespace ZookeeperNodeCache\CacheStrategys;

use Predis\Client;
use ZookeeperNodeCache\InstanceTrait;
use ZookeeperNodeCache\Tools\CommonFunctions;

/**
 * Class RedisCache
 *
 * @package ZookeeperNodeCache\CacheStrategys
 */
class RedisCache extends CacheAbs {

    use InstanceTrait;
    private static $redis = null;
    /**
     * 是否redis集群配置
     *
     * @var null
     */
    private static $isRedisCluster = null;

    /**
     * 设置节点
     *
     * @param array $pairs
     *
     * @return array
     */
    public function setCacheConf(array $pairs): array
    {
        $newConfig = [];
        try {
            $redisClient = $this->getInstantiation();
            $newConfig   = $this->transformData($pairs);
            if ($this->setLock(time(), time())) {
                if (true === self::$isRedisCluster) {
                    //批量缓存
                    $cb = function ($pipe) use ($newConfig) {
                        foreach ($newConfig as $key => $val) {
                            $pipe->set($key, $val);
                        }
                    };
                    $redisClient->pipeline($cb);
                } else {
                    foreach ($newConfig as $key => $val) {
                        $redisClient->set($key, $val);
                    }
                }
            }
        } catch (\Exception $e) {
            CommonFunctions::commonOutput($e->getMessage());
        }

        return $newConfig;
    }

    /**
     * 获取实例
     *
     * @return null
     */
    private function getInstantiation(): Client
    {
        self::init();

        return self::$redis;
    }

    /**
     * 初始化连接
     */
    protected function init():void
    {
        $redisConfig = [
            'host'     => CommonFunctions::getZkConfigCache('redis_host'),
            'port'     => CommonFunctions::getZkConfigCache('redis_port'),
            'database' => CommonFunctions::getZkConfigCache('redis_database'),
            'password' => CommonFunctions::getZkConfigCache('redis_password'),
        ];
        try {
            if (!$redisConfig['password']) {
                unset($redisConfig['password']);
                $redisConfig['parameters'] = ['password' => ''];
            }
            self::$redis = new Client($redisConfig);
            $this->isRedisCluster($redisConfig);
        } catch (\Exception $e) {
            CommonFunctions::commonOutput($e->getMessage());
        }
    }

    /**
     * 判断是否redis集群
     *
     * @return null
     */
    private function isRedisCluster($redisConfig)
    {
        if (null !== self::$isRedisCluster) {
            return self::$isRedisCluster;
        }
        self::$isRedisCluster = false;
        $res                  = strpos($redisConfig['host'], ',');
        if (false !== $res) {
            self::$isRedisCluster = true;
        }
    }

    /**
     * 设置锁
     *
     * @param string $key
     * @param string $val
     * @param int    $expire
     *
     * @return bool
     */
    private function setLock(string $key, string $val, ?int $expire = 1):bool
    {
        try {
            if (self::$redis->set($key, $val, 'NX', 'EX', $expire)) {
                return true;
            }

            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getAllData():array
    {
        return [];
    }

    /**
     * 删除节点
     *
     * @param string $zkFullPath
     */
    public function delCacheConf(string $zkFullPath): void
    {
        try {
            $redisClient = $this->getInstantiation();
            if ($this->setLock(time(), time())) {
                $key = $this->transToCachePathFromFullZkPath($zkFullPath);
                $redisClient->del($key);
            }
        } catch (\Exception $e) {
            CommonFunctions::commonOutput($e->getMessage());
        }
    }

    /**
     * 获取缓存值
     *
     * @param string $zkFullPath
     *
     * @return string
     */
    public function getCacheConf(string $zkFullPath):string
    {
        $value = '';
        try {
            $redisClient = $this->getInstantiation();
            $value       = (string) $redisClient->get($this->transToCachePathFromFullZkPath($zkFullPath));
        } catch (\Exception $e) {
            CommonFunctions::commonOutput($e->getMessage());
        }

        return $value;
    }
}