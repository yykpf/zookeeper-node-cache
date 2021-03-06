<?php
namespace ZookeeperNodeCache\CacheStrategys;

use ZookeeperNodeCache\Tools\InstanceTrait;

/**
 * Class NullCache
 *
 * @package ZookeeperNodeCache\CacheStrategys
 */
class NullCache extends CacheAbs {

    use InstanceTrait;

    public function setCacheConf(array $pairs): array
    {
    }

    public function delCacheConf(string $zkFullPath): void
    {
    }

    public function getCacheConf(string $zkFullPath):string
    {
    }

    public function getCacheAllConf():array
    {
    }
}