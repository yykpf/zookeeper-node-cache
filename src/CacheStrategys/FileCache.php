<?php
namespace ZookeeperNodeCache\CacheStrategys;

use ZookeeperNodeCache\Exception\ZookeeperException;
use ZookeeperNodeCache\InstanceTrait;
use ZookeeperNodeCache\Tools\CommonFunctions;

/**
 * 文件缓存
 *
 * @package ZookeeperNodeCache\CacheStrategys
 */
class FileCache extends CacheAbs {

    use InstanceTrait;

    protected $cacheFile = '';

    /**
     * 设置缓存
     *
     * @param array $pairs
     *
     * @return array
     */
    public function setCacheConf(array $pairs): array
    {
        $newConfig    = [];
        $zkConfigFile = $this->getCacheFile();
        try {
            $zkFp = fopen($zkConfigFile, 'a+');
            if ($this->setLock($zkFp)) {
                $config    = $this->analysisFile($zkConfigFile);
                $newConfig = $this->transformData($pairs);
                unset($pairs);
                $config = array_merge($config, $newConfig);
                ftruncate($zkFp, 0);
                rewind($zkFp);
                fwrite($zkFp, $this->getPutFileData($config));
                fflush($zkFp);
                flock($zkFp, LOCK_UN);
                // 调用env设置
                CommonFunctions::putZkEnv($newConfig);
            }
            fclose($zkFp);
        } catch (\Exception $e) {
            if (is_resource($zkFp)) {
                flock($zkFp, LOCK_UN);
                fclose($zkFp);
            }
            CommonFunctions::commonOutput($e->getMessage());
        }

        return $newConfig;
    }

    /**
     * @return string
     */
    public function getCacheFile(): string
    {
        if (empty($this->cacheFile)) {
            if (!$this->cacheFile = CommonFunctions::getZkConfigCache('file_path')) {
                $this->cacheFile = CommonFunctions::getDefaultFilePath();
            }
        }

        return $this->cacheFile;
    }

    /**
     * 获取锁
     *
     * @param $zkFp
     *
     * @return bool
     */
    private function setLock(resource $zkFp):bool
    {
        if (flock($zkFp, LOCK_EX | LOCK_NB)) { // LOCK_NB 防止阻塞 不等待
            return true; //加锁成功
        }

        return false;
    }

    /**
     * 解析文件
     *
     * @param $zkConfigFile
     *
     * @return array|mixed
     */
    private function analysisFile(string $zkConfigFile):array
    {
        return json_decode(file_get_contents($zkConfigFile), true) ?? [];
    }

    /**
     * 获取需要写入的文件内容
     *
     * @param $config
     *
     * @return string
     */
    private function getPutFileData(array $config):string
    {
        return json_encode($config, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 删除节点
     *
     * @param string $zkFullPath
     */
    public function delCacheConf(string $zkFullPath): void
    {
        $zkConfigFile = $this->getCacheFile();
        if (file_exists($zkConfigFile) && is_file($zkConfigFile)) {
            try {
                $zkFp = fopen($zkConfigFile, 'a+');
                if ($this->setLock($zkFp)) {
                    $config = $this->analysisFile($zkConfigFile);
                    $key    = $this->transToCachePathFromFullZkPath($zkFullPath);
                    unset($config[$key]);
                    ftruncate($zkFp, 0);
                    rewind($zkFp);
                    fwrite($zkFp, $this->getPutFileData($config));
                    fflush($zkFp);
                    flock($zkFp, LOCK_UN);
                    // 调用env设置
                    CommonFunctions::delZkEnv($key);
                }
                fclose($zkFp);
            } catch (\Exception $e) {
                if (is_resource($zkFp)) {
                    flock($zkFp, LOCK_UN);
                    fclose($zkFp);
                }
                CommonFunctions::commonOutput($e->getMessage());
            }
        }
    }

    /**
     * @param string $zkFullPath
     *
     * @return string
     * @throws \ZookeeperNodeCache\Exception\ZookeeperException
     */
    public function getCacheConf(string $zkFullPath):string
    {
        $zkConfigFile = $this->getCacheFile();
        $value        = '';
        if (file_exists($zkConfigFile) && is_file($zkConfigFile)) {
            try {
                $zkFp   = fopen($zkConfigFile, 'r');
                $config = $this->analysisFile($zkConfigFile);
                $key    = $this->transToCachePathFromFullZkPath($zkFullPath);
                $value  = $config[$key]??'';
                fclose($zkFp);
            } catch (\Exception $e) {
                if (is_resource($zkFp)) {
                    fclose($zkFp);
                }
            }
        }

        return $value;
    }
}