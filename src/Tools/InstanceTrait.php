<?php
namespace ZookeeperNodeCache;

// 单例 trait
trait InstanceTrait
{

    protected static $_instance;

    private function __construct()
    {
        $this->init();
    }

    final public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    public function destruct()
    {
        self::$_instance = null;
    }

    protected function init()
    {
    }

    final private function __sleep()
    {
    }

    final private function __clone()
    {
    }
}