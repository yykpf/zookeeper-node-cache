<?php
namespace ZookeeperNodeCache\Events;

use ZookeeperNodeCache\CacheStrategys\CacheAbs;
use ZookeeperNodeCache\Tools\CommonFunctions;

/**
 * 监听事件
 *
 * @package ZookeeperNodeCache\Events
 */
class WatcherEvent {

    private $host          = '';
    private $root          = '';
    private $cacheStrategy = null;
    private $zookeeper     = null;

    public function __construct($host, $root)
    {
        $this->host = $host;
        $this->root = $root;
    }

    /**
     * @param \ZookeeperNodeCache\CacheStrategys\CacheAbs $cache
     */
    public function setCacheStrategy(CacheAbs $cache)
    {
        $this->cacheStrategy = $cache;

        return $this;
    }

    /**
     * 运行
     */
    public function run()
    {
        $this->zookeeper = new \Zookeeper($this->zkHost, [$this, 'watch']);
        // 设置报错级别
        $this->zookeeper->setDebugLevel(\Zookeeper::LOG_LEVEL_ERROR);
    }

    /**
     * 监控回调事件{连接事件 节点事件 子节点事件}
     *
     * @param $eventType
     * @param $connectionState
     * @param $path
     */
    public function watch($eventType, $connectionState, $path)
    {
        switch ($eventType) {
            case \Zookeeper::DELETED_EVENT:
                // 2 数据监控返回,节点删除,通过 exists 和 get 监控,通过 delete 操作触发
                if ($this->deleteCacheNode($path)) {
                    $this->cacheStrategy->delCacheConf($path);
                }
                break;
            case \Zookeeper::CREATED_EVENT:
                // 1 数据监控返回,节点创建,需要watch一个不存在的节点,通过exists监控,通过create操作触发
            case \Zookeeper::CHANGED_EVENT:
                // 3 数据监控返回, 节点数据改变, 通过 exists 和 get 监控, 通过set操作触发
                $res = $this->getAndCacheNode($path);
                if (!empty($res)) {
                    $this->cacheStrategy->setCacheConf($res);
                }
                break;
            case \Zookeeper::CHILD_EVENT:
                // 4 节点监控返回,通过 getchild 监控, 通过子节点的 delete 和 create 操作触发
                $res = $this->getAndCacheChild($path);
                if (!empty($res)) {
                    $this->cacheStrategy->setCacheConf($res);
                }
                break;
            case \Zookeeper::SESSION_EVENT:
                // -1 会话监控返回,客户端与服务端断开或重连时触发
                if (3 == $connectionState) {
                    $res = $this->getAndCacheChild($this->root);
                    if (!empty($res)) {
                        $this->cacheStrategy->setCacheConf($res);
                    }
                }
                break;
            case \Zookeeper::NOTWATCHING_EVENT:
                // -2 watch移除事,服务端不再回调客户端
            default:
        }
    }

    /**
     * 删除节点
     *
     * @param $nodePath
     */
    protected function deleteCacheNode($nodePath): string
    {
        if (!$this->zookeeper->exists($nodePath)) {
            CommonFunctions::commonOutput("delete cache: $nodePath");

            return $nodePath;
        } else {
            return "";
        }
    }

    /**
     * 获取节点值 缓存并监控
     *
     * @param $nodePath
     */
    protected function getAndCacheNode($nodePath): array
    {
        if ($this->zookeeper->exists($nodePath)) {
            CommonFunctions::commonOutput("watch node : $nodePath");
            $tempValue = $this->zookeeper->get($nodePath, [$this, 'watch']);
            CommonFunctions::commonOutput("cache node : $nodePath");

            return [$nodePath => $tempValue];
        }

        return [];
    }

    /**
     * 获取子节点缓存并监控
     *
     * @param $root
     */
    protected function getAndCacheChild($root): array
    {
        $res = [];
        if ($this->zookeeper->exists($root)) {
            CommonFunctions::commonOutput("watch child: $root");
            $nodes = $this->zookeeper->getChildren($root, [$this, 'watch']);
            if (empty($nodes)) {
                $res = $this->getAndCacheNode($root);
            } else {
                foreach ($nodes as $node) {
                    CommonFunctions::commonOutput("cache node: $node");
                    $res = array_merge($res, $this->getAndCacheChild($root . '/' . $node));
                }
            }
        }

        return $res;
    }
}