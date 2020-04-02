# zk节点命令行缓存工具
### 命令行缓存
[^_^]:
        通过调用命令行命令来执行zk的监听机制最终实现缓存
   
### 使用方法

    1、引入项目
    
        composer require yykpf/zookeeper-node-cache
    
    2、发布服务创建配置文件
    
         php artisan vendor:publish --provider='ZookeeperNodeCache\ZookeeperServiceProvider'
         
         lumen 需要引入  laravelista/lumen-vendor-publish 否则不存在 vendor:publish 这个命令
       有些服务可能不支持自动发布，就需要：
          1)、在config/app.php 中添加
          
              ```
                 'providers' => [
                       ...
                       ZookeeperNodeCache\ZookeeperServiceProvider::class
                 ],
                 'aliases' => [
                      'zk' => ZookeeperNodeCache\Facades\ZookeeperFacade::class
                  ],
              ```
        
         2)、查看是否存在文件 config/zookeepercache.php,如果没有就创建 并复制 config.php 内容进去
    3、通过执行以下命令来开启zk缓存，缓存方式在config文件配置zookeepercache[支持文件和redis两种方式]
        
    4、通过配置local_env变量来开启是否支持本地env缓存
        
    5、执行命令 php artisan zookeeper:node:cache
    
    6、调用方式 ($node 监听路劲[路径+版本号]下的节点值)
        use zk;
        
            zk::getNode($node)   
            
    7、如果想使用env缓存(目前支持文件类型的缓存)
        use zk;
        
            zk::putZkEnv() 
