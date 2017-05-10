<?php
namespace luffyzhao\helper;

/**
 *
 */
class Command extends think\console\Command
{
    /**
     * 文件锁
     * @method   lock
     * @DateTime 2017-04-26T12:00:05+0800
     * @param    bool                     $op [description]
     * @return   [type]                       [description]
     */
    protected function lock(bool $op = true)
    {
        $lockFile = RUNTIME_PATH . md5(get_class()) . '.lock';
        if ($op === false) {
            @unlink($lockFile);
        } else {
            if (file_exists($lockFile)) {
                if (time() - filemtime($lockFile) > 600) {
                    throw new \LogicException('进程已运行 5 分钟...');
                } else {
                    throw new \LogicException('进程运行中...');
                }
            }
            // 创建
            touch($lockFile);
            if (preg_match('/linux/i', PHP_OS) || preg_match('/Unix/i', PHP_OS)) {
                chmod($lockFile, 0777);
            }
        }

    }
}
