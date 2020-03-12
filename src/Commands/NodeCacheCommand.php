<?php
namespace ZookeeperNodeCache\Commands;

use Illuminate\Console\Command;
use ZookeeperNodeCache\CacheStrategys\NullCache;
use ZookeeperNodeCache\Events\WatcherEvent;
use ZookeeperNodeCache\Tools\CommonFunctions;

/**
 * 节点缓存开启
 *
 * @package ZookeeperNodeCache\Commands
 */
class NodeCacheCommand extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zookeeper:node:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '缓存Zookeeper节点';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        CommonFunctions::setZkConfigCache();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CommonFunctions::commonOutput('node cache starting');

        // 获取配置
        $host = CommonFunctions::getZkConfigCache('zk_host');
        $root = CommonFunctions::getWatchPath(); // 获取监听路径
        $mode = CommonFunctions::getZkConfigCache('cache_mode');

        // 获取缓存策略
        $cache = CommonFunctions::getService($mode);
        if ($cache instanceof NullCache) {
            CommonFunctions::commonOutput('zookeepercache.php file cache_mode is not null');
            exit();
        }

        // 开始监听
        (new WatcherEvent($host, $root))->setCacheStrategy($cache)->run();

        while (true) {
            sleep(1);
        }
    }

}