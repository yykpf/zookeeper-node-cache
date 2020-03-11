<?php

return [
    // 缓存方法 file(文件)  reids  null(不用)
    'cache_mode'     => 'file',

    // 缓存前缀
    'cache_prefix'   => 'test',

    // 环境变量配置
    'zk_host'        => '127.0.0.1:2181',
    'zk_root_path'   => '/test/project',
    'zk_version'     => 'v1.0',

    // redis配置
    'redis_host'     => '127.0.0.1',
    'redis_port'     => 6379,
    'redis_database' => 6,
    'redis_password' => '',

    // 文件配置
    'file_path'      => storage_path() . DIRECTORY_SEPARATOR . 'zkconfig',
    'file_name'      => 'config.json',
];