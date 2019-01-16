<?php
/**
 * User: 黄树斌
 * Date: 2019/1/15 0015
 * Time: 16:54
 */

namespace Nano;

use Closure;
use Nano\ProcessException;

class Worker extends Process
{
    public function __construct(array $config = [])
    {
        $this->pid  = isset($config['pid']) ? $config['pid'] : $this->pid;
        $this->type = isset($config['type']) ? $config['type'] : 'worker';
        parent::__construct($config);

        ProcessException::info([
            'msg' => [
                'from'  => $this->type,
                'extra' => 'worker instance create'
            ]
        ]);
    }

    public function hangup(Closure $closure)
    {
        while (true) {
            // 检查是否有退出标志
            if ($this->workerExitFlag) {
                $this->workerExit();
            }

            // 当前运行时间大于进程最大运行时间
            if (self::$currentExecuteTimes >= self::$maxExecuteTimes) {
                $this->workerExit();
            }

            // 读管道
            if ($this->signal = $this->pipeRead()) {
                $this->dispatchSig();
            }

            ++self::$currentExecuteTimes;

            usleep(self::$hangupLoopMicrotime);
        }
    }

    /**
     * 退出子进程
     *
     * @return void
     */
    private function workerExit()
    {
        $this->clearPipe();

        ProcessException::info([
            'msg' => [
                'from'  => $this->type,
                'extra' => "signal: $this->signal  worker process exit"
            ]
        ]);

        exit;
    }

    /**
     * 分发信号
     *
     * @return void
     */
    private function dispatchSig()
    {
        switch ($this->signal) {
            case 'stop':
            case 'reload':
                $this->workerExitFlag = true;
                break;

            default:
                break;
        }
    }
}