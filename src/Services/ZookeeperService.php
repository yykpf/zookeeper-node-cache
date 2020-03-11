<?php
namespace ZookeeperNodeCache\Services;

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

        $key = $this->getKey($node); // 获取key值

        if ($value = CommonFunctions::getZkEnv(CommonFunctions::transToKey($key))) { // 是否存在env变量
            return $value;
        }

        $cache = CommonFunctions::getService(CommonFunctions::getZkConfigCache('cache_mode')); // 获取缓存策略
        if (!empty($cache)) {
            return $cache->getCacheConf($key);
        }

        // 调用zk原始获取
        return ZookeeperBaseService::getInstance()->getRetryNodeValue($value, $default);
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
        return rtrim(CommonFunctions::getZkConfigCache('zk_root_path'), '/') . '/' . trim($node, '/');
    }
}