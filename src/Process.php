<?php
/**
 * User: 黄树斌
 * Date: 2019/1/14 0014
 * Time: 16:05
 */

namespace Nano;

use Closure;
use Nano\ProcessException;

abstract class Process
{
    /**
     * 进程类型
     *
     * @var string
     */
    public $type = '';

    /**
     * 进程id
     *
     * @var int
     */
    public $pid = '';

    /**
     * 管道名称
     *
     * @var string
     */
    protected $pipeName = '';

    /**
     * 管道模式
     *
     * @var int
     */
    protected $pipeMode = 0777;

    /**
     * 管道名称前缀
     *
     * @var string
     */
    protected $pipeNamePrefix = 'nano_pipe_';

    /**
     * 管道存放路径
     *
     * @var string
     */
    protected $pipeDir = '/tmp/nano_pipe/';

    /**
     * 管道生成路径
     *
     * @var string
     */
    protected $pipePath = '';

    /**
     * 读取管道数据的字节数
     *
     * @var int
     */
    protected $readPipeType = 1024;

    /**
     * 进程退出标志位
     *
     * @var bool
     */
    protected $workerExitFlag = false;

    /**
     * 当前接受到的信号
     *
     * @var string
     */
    protected $signal = '';

    /**
     * 挂起间隔睡眠时间
     *
     * @var int
     */
    protected static $hangupLoopMicrotime = 200000;

    /**
     * current execute times
     *
     * default 0
     *
     * @var int
     */
    protected static $currentExecuteTimes = 0;

    /**
     * max execute times
     *
     * default 5*60*60*24
     *
     * @var int
     */
    protected static $maxExecuteTimes = 5 * 60 * 60 * 24;

    /**
     * construct function
     *
     * Process constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (empty($this->pid))
        {
            // 获取进程pid
            $this->pid = posix_getpid();
        }

        // 管道名称
        $this->pipeName = $this->pipeNamePrefix . $this->pid;
        // 管道路径
        $this->pipePath = $this->pipeDir . $this->pipeName;

        // 睡眠时间
        self::$hangupLoopMicrotime = isset($config['hangup_loop_microtime']) ? $config['hangup_loop_microtime'] : self::$hangupLoopMicrotime;
    }

    /**
     * hangup abstract function
     *
     * @param Closure $closure
     * @return mixed
     */
    abstract protected function hangup(Closure $closure);

    /**
     * 创建管道
     *
     * @return void
     */
    public function pipeMake()
    {
        if (!is_dir($this->pipeDir))
        {
            mkdir($this->pipeDir);
        }

        if (!file_exists($this->pipePath))
        {
            // 创建管道
            if (!posix_mkfifo($this->pipePath, $this->pipeMode))
            {
                ProcessException::error([
                    'msg'  => [
                        'from'  => $this->type,
                        'extra' => "pipe make {$this->pipePath}"
                    ]
                ]);

                exit;
            }
        }

        // 赋予管道权限
        chmod($this->pipePath, $this->pipeMode);
        ProcessException::info([
            'msg' => [
                'from'  => $this->type,
                'extra' => "pipe make {$this->pipePath}"
            ]
        ]);
    }

    /**
     * 将信号写入管道
     *
     * @param string $signal
     */
    public function pipeWrite($signal = '')
    {
        // 打开管道
        $pipe = fopen($this->pipePath, 'w');
        if (!$pipe)
        {
            ProcessException::error([
                'msg' => [
                    'from'  => $this->type,
                    'extra' => "pipe open {$this->pipePath}"
                ]
            ]);

            return;
        }

        ProcessException::info([
            'msg' => [
                'from'  => $this->type,
                'extra' => "pipe open {$this->pipePath}"
            ]
        ]);

        // 将信号写入管道
        $res = fwrite($pipe, $signal);

        if (!$res)
        {
            ProcessException::error([
                'msg' => [
                    'pid'   => $this->pid,
                    'from'  => $this->type,
                    'extra' => "pipe write '{$signal}' to {$this->pipePath}",
                ]
            ]);

            return;
        }

        ProcessException::info([
            'msg' => [
                'from'  => $this->type,
                'extra' => "pipe write '{$signal}' to {$this->pipePath}",
            ]
        ]);

        // 关闭管道
        if (!fclose($pipe))
        {
            ProcessException::error([
                'msg' => [
                    'from'  => $this->type,
                    'extra' => "pipe close {$this->pipePath}"
                ]
            ]);

            return;
        }

        ProcessException::info([
            'msg' => [
                'from'  => $this->type,
                'extra' => "pipe close {$this->pipePath}"
            ]
        ]);
    }

    /**
     * 读取信号
     *
     * @return bool|string|void
     */
    public function pipeRead()
    {
        // 检测管道
        while (!file_exists($this->pipePath))
        {
            usleep(self::$hangupLoopMicrotime);
        }

        // 打开管道, 'r+'模式不会阻塞
        do {
            $workerPipe = fopen($this->pipePath, 'r+');
            usleep(self::$hangupLoopMicrotime);
        }while(!$workerPipe);

        // 将管道设置为非堵塞，用于适应超时机制
        stream_set_blocking($workerPipe, false);

        // 读取管道
        $msg = fread($workerPipe, $this->readPipeType);

        if (!empty($msg))
        {
            ProcessException::info([
                'msg' => [
                    'from'  => $this->type,
                    'extra' => "pipe read '{$msg}' {$this->pipePath}",
                ]
            ]);
        }

        return $msg;
    }

    /**
     * 清除管道
     *
     * @return bool
     */
    public function clearPipe()
    {
        $msg = [
            'msg' => [
                'from'  => $this->type,
                'extra' => "pipe clear {$this->pipePath}"
            ]
        ];
        ProcessException::info($msg);

        if (!unlink($this->pipePath)) {
            shell_exec("rm -rf {$this->pipePath}");
        }
        
        return true;
    }

    /**
     * 停止进程
     *
     * @return bool
     */
    public function stop()
    {
        $msg = [
            'msg' => [
                'from'  => $this->type,
                'extra' => "{$this->pid} stop"
            ]
        ];
        ProcessException::info($msg);
        $this->clearPipe();
        if (! posix_kill($this->pid, SIGKILL)) {
            ProcessException::error($msg);
            return false;
        }
        return true;
    }

    /**
     * 设置进程名称
     *
     * @return void
     */
    public function setProcessName($title = '')
    {
        cli_set_process_title($title);
    }
}