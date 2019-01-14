<?php
/**
 * User: 黄树斌
 * Date: 2019/1/14 0014
 * Time: 10:21
 */

namespace Nano;


class Manager
{
    /**
     * 操作系统
     *
     * @var string
     */
    private $os = '';

    /**
     * 主进程对象
     *
     * @var object Process
     */
    private $master = '';

    /**
     * 守护进程对象
     *
     * @var object Process
     */
    private $daemon = '';

    /**
     * 子进程池
     *
     * @var array [Process]
     */
    public $workers = [];

    /**
     * 信号处理池
     *
     * pool: array [Process]
     * signal:string reload/stop
     *
     * @var array
     */
    private $waitSignalProcessPool = [
        'pool'   => [],
        'signal' => '',
    ];

    /**
     * 注入子进程的闭包
     *
     * @var object Closure
     */
    private $workBusinessClosure = '';

    /**
     *最小进程数
     *
     * @var int
     */
    private $minNum = 1;

    /**
     * 启动的进程数
     *
     * @var int
     */
    private $startNum = 5;

    /**
     * 操作系统用户密码
     *
     * @var string
     */
    private $userPassword = '';

    /**
     * 管道目录
     *
     * @var string
     */
    private $pipeDir = '';

    /**
     * 配置项
     *
     * @var array
     */
    private $env = [];

    /**
     * 支持的信号
     *
     * @var array
     */
    private $signalSupport = [
        'int'       =>  2,   // 中断
        'stop'      =>  12,  // 优雅停止
        'reload'    =>  10,  // 重新加载
        'terminate' =>  15,  // 强制中断
    ];

    /**
     * 挂起时间
     *
     * default 200000μs
     *
     * @var int
     */
    private static $hangupLoopMicrotime = 200000;


    public function __construct($config = [], \Closure $closure)
    {
        // 加载环境配置项
        $this->loadEnv();

        // 配置时区
        date_default_timezone_set($this->env['config']['timezone'] ?? 'Asia/shanghai');

        // 欢迎界面
        $this->welcome();

        // 设置启动的基本参数
        $this->configure($config);
    }

    /**
     * 加载环境配置文件
     */
    private function loadEnv()
    {
        $this->env = parse_ini_file(__DIR__ . '/../.env', true);
    }

    /**
     * welcome
     *
     * @return void
     */
    public function welcome()
    {
        $welcome = <<<WELCOME
\033[36m
 _   _                   
| \ | | __ _ _ __   ___  
|  \| |/ _` | '_ \ / _ \ 
| |\  | (_| | | | | (_) |
|_| \_|\__,_|_| |_|\___/

A multi process manager for PHP

Version: 0.2

\033[0m
WELCOME;
        echo $welcome;
    }

    public function configure($config = [])
    {
        // 设置操作系统
        $this->os = $config['os'] ?? $this->os;

        // 设置用户密码
        $this->userPassword = $config['password'] ?? '';

        // 设置启动的进程数
        $this->startNum = isset($config['worker_num']) ? (int)$config['worker_num'] : $this->startNum;

        // 设置进程挂起时间
        self::$hangupLoopMicrotime = $config['hangup_loop_microtime'] ?? self::$hangupLoopMicrotime;

        // 设置管道文件路径
        $this->pipeDir = $config['pipe_dir'] ?? '';
    }
}