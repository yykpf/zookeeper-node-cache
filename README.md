# zk节点命令行缓存工具
### 命令行缓存
[^_^]:
        通过调用命令行命令来执行zk的监听机制最终实现缓存
    
    1、通过执行以下命令来开启zk缓存，缓存方式在config文件配置zookeepercache[支持文件和redis两种方式]
    
    2、通过配置local_env变量来开启是否支持本地env缓存
    
    3、执行命令 php artisan zookeeper:node:cache
   
   
### 节点获取

    1、调用方式 zk::getNode($key)