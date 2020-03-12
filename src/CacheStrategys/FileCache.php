<?php
namespace ZookeeperNodeCache\CacheStrategys;

use ZookeeperNodeCache\Tools\InstanceTrait;
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
        $zkFp         = '';
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
            if (!$this->cacheFile = (rtrim(CommonFunctions::getZkConfigCache('file_path'), '/') . '/' . CommonFunctions::getZkConfigCache('file_name'))) {
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
    private function setLock($zkFp):bool
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
     * 获取全部值
     *
     * @return array
     */
    public function getCacheAllConf():array
    {
        $zkConfigFile = $this->getCacheFile();
        $config       = [];
        if (file_exists($zkConfigFile) && is_file($zkConfigFile)) {
            $zkFp = '';
            try {
                $zkFp   = fopen($zkConfigFile, 'r');
                $config = $this->analysisFile($zkConfigFile);
                fclose($zkFp);
            } catch (\Exception $e) {
                if (is_resource($zkFp)) {
                    fclose($zkFp);
                }
            }
        }

        return $config;
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
            $zkFp = '';
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
     * 获取缓存值
     *
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
            $zkFp = '';
            try {
                $zkFp   = fopen($zkConfigFile, 'r');
                $config = $this->analysisFile($zkConfigFile);
                $key    = $this->transToCachePathFromFullZkPath($zkFullPath);
                $value  = (string) $config[$key]??'';
                fclose($zkFp);
            } catch (\Exception $e) {
                if (is_resource($zkFp)) {
                    fclose($zkFp);
                }
            }
        }

        return $value;
    }

    /**
     * 初始化
     */
    protected function init():void
    {
        $path = CommonFunctions::getZkConfigCache('file_path');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}