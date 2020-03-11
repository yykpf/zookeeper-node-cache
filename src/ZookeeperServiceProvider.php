<?php
namespace ZookeeperNodeCache;

use Illuminate\Support\ServiceProvider;
use ZookeeperNodeCache\Commands\NodeCacheCommand;
use ZookeeperNodeCache\Services\ZookeeperService;

/**
 * Class ZookeeperServiceProvider
 *
 * @package ZookeeperNodeCache
 */
class ZookeeperServiceProvider extends ServiceProvider {

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('zk', function () {
            return new ZookeeperService;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('zookeepercache.php'),
        ]);

        $this->registerCommand();
    }

    /**
     * 注册命令行
     */
    public function registerCommand()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                NodeCacheCommand::class,
            ]);
        }
    }
}