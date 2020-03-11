<?php
namespace ZookeeperNodeCache\Services;

use ZookeeperNodeCache\InstanceTrait;
use ZookeeperNodeCache\Tools\CommonFunctions;

class ZookeeperBaseService {

    use InstanceTrait;

    private static $zkConfig;
    private static $zk;

    /**
     * 重试获取节点值
     *
     * @param        $path
     * @param string $default
     *
     * @return null|string
     */
    public function getRetryNodeValue($path, $default = "")
    {
        $reconnectCounter = 0;
        for ($counter = 0; $counter < 10; ++$counter) {
            try {
                $data = $this->getNodeValue($path, $default);

            } catch (\Exception $e) {

                $data = null;
            }
            if ($data != null) {
                return $data;
            }
            if ($reconnectCounter < 10) {
                //获取数据失败时，释放zk连接，使得下一轮重新建立zk连接
                $this->destruct();
                $reconnectCounter++;
            }
        }

        return $default;
    }

    /**
     * @param $path
     * @param $default
     *
     * @return string
     */
    private function getNodeValue($path, $default = '')
    {
        $pathDir = '/' . trim(self::$zkConfig['path'], '/') . '/';
        if (!empty(self::$zkConfig['version'])) {
            $pathDir .= trim(self::$zkConfig['version'], '/') . '/';
        }
        $pathDir .= trim($path, '/');

        try {
            if (self::$zk->exists($pathDir)) {
                return rtrim(self::$zk->get($pathDir), '/');
            }
        } catch (\Exception $e) {
            $this->destruct();
        }

        return $default;
    }

    /**
     * 初始化
     */
    protected function init()
    {
        self::$zkConfig = [
            'host'    => CommonFunctions::getZkConfigCache('zk_host'),
            'path'    => CommonFunctions::getZkConfigCache('zk_root_path'),
            'version' => CommonFunctions::getZkConfigCache('zk_version'),
        ];
        self::$zk       = new \Zookeeper(self::$zkConfig['host'], null, 500);
    }
}