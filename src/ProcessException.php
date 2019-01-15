<?php
/**
 * User: 黄树斌
 * Date: 2019/1/14 0014
 * Time: 15:11
 */

namespace Nano;

use Exception;

class ProcessException
{
    /**
     * 标志,是否第一次加载
     *
     * @var bool
     */
    private static $sign = true;

    /**
     * 日志方法
     *
     * @var array
     */
    private static $methodSupport = ['info', 'error', 'debug'];

    /**
     * 日志路径
     *
     * @var string
     */
    private static $logPath = '/tmp/nano';

    /**
     * the magic __callStatic function
     *
     * @param string $method
     * @param array $data
     * @throws Exception
     */
    public static function __callStatic($method = '', $data = [])
    {
        $data = $data[0];
        if(!in_array($method, self::$methodSupport))
        {
            throw new Exception('log method not support', 500);
        }

        self::$logPath = isset($data['path']) ? $data['path'] : self::$logPath;

        $msg = self::decorate($method, $data['msg']);
        error_log($msg, 3, self::$logPath . '.' . date('Y-m-d', time()) . '.log');
    }

    /**
     * 处理日志信息
     *
     * @param string $rank
     * @param array $msg
     * @return string
     */
    private static function decorate($rank = 'info', $msg = [])
    {
        $pid  = posix_getpid();
        $time = date('Y-m-d H:i:s', time());
        $memoryUsage = round(memory_get_usage()/1024, 2) . ' kb';

        $default = [
            'pid'    =>  $pid,
            'time'   =>  $time,
            'rank'   =>  $rank,
            'memory' =>  $memoryUsage
        ];

        $default['from'] = $msg['from'];
        unset($msg['from']);

        $tmp = '';
        if (self::$sign == true)
        {
            $tmp = PHP_EOL;
            self::$sign = false;
        }

        $tmp .= '[' . $default['time'] . ']' . '[' . $default['rank'] . ']';
        $tmp .= ($default['rank'] == 'info') ? ' [' . $default['from'] . ']' : '['. $default['from'] . ']';
        $tmp .= ' [' . $default['pid'] . ']' . ' ' . $msg['extra'] . PHP_EOL;

        return $tmp;
    }
}