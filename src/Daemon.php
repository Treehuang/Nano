<?php
/**
 * User: 黄树斌
 * Date: 2019/1/15 0015
 * Time: 22:12
 */

namespace Nano;

use Closure;
use Nano\Manager;

class Daemon extends Process
{
    public function __construct()
    {
        $this->type = 'daemon';

        ProcessException::info([
            'msg' => [
                'from'  => $this->type,
                'extra' => "daemon instance create"
            ]
        ]);
    }

    /**
     * 检查系统的子进程数，如没达到设置的启动数，则进行创建达到启动数
     *
     * @param \Nano\Manager $manager
     *
     * @return void
     */
    public function check(Manager $manager)
    {
        // 检查信号-进程池有没有信号
        if (!empty($manager->waitSignalProcessPool['signal'])) {
            return;
        }

        // 获取当前系统运行的子进程数
        $num = intval(shell_exec("pstree -p {$manager->master->pid} | grep php | wc -l"));
        // 检查当前系统运行的子进程数
        $diff = $manager->startNum - $num;
        if ($diff > 0) {
            // 创建子进程
            $manager->execFork($diff);
        }
    }

    protected function hangup(Closure $closure)
    {
        // TODO: Implement hangup() method.
    }
}