<?php
namespace ZookeeperNodeCache\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * 门面模式调用
 *
 * @package ZookeeperNodeCache\Facades
 */
class ZookeeperFacade extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'zk';
    }
}