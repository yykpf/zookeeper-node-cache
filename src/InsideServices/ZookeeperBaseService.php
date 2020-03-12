<?php
namespace ZookeeperNodeCache\InsideServices;

use ZookeeperNodeCache\Tools\CommonFunctions;
use ZookeeperNodeCache\Tools\InstanceTrait;

class ZookeeperBaseService {

    use InstanceTrait;

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
        $pathDir = CommonFunctions::getWatchPath();
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
        self::$zk = new \Zookeeper(CommonFunctions::getZkConfigCache('zk_host'), null, 500);
    }
}