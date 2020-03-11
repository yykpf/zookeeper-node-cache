<?php
namespace ZookeeperNodeCache\CacheStrategys;

use ZookeeperNodeCache\Tools\CommonFunctions;

abstract class  CacheAbs {

    /**
     * 设置缓存
     *
     * @param array $pairs
     */
    abstract public function setCacheConf(array $pairs): array;

    /**
     * 删除单个缓存
     */
    abstract public function delCacheConf(string $zkFullPath): void;

    /**
     * 获取数据
     */
    abstract public function getCacheConf(string $zkFullPath):string;

    abstract public function getAllData():array;

    /**
     * 数据转换
     *
     * @param array $pairs
     *
     * @return array
     */
    protected function transformData(array $pairs):array
    {
        $newConfig = [];
        foreach ($pairs as $k => $v) {
            $newConfig[$this->transToCachePathFromFullZkPath($k)] = $v;
        }

        return $newConfig;
    }

    /**
     * 将 ZK 节点转换为缓存的键
     *
     * @param string $zkFullPath
     *
     * @return string
     */
    protected function transToCachePathFromFullZkPath(string $zkFullPath): string
    {
        return CommonFunctions::transToKey($zkFullPath);
    }

}