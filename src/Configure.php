<?php
/**
 * User: 黄树斌
 * Date: 2019/1/17 0017
 * Time: 11:07
 */

namespace Nanov;

class Configure
{
    /**
     * 配置文件
     *
     * @var string
     */
    private $nanov_ini = __DIR__ . '/../etc/nanov.ini';

    /**
     * 配置项
     *
     * @var array
     */
    public $config = [];

    /**
     * 实例
     */
    static private $instance = '';

    private function __construct()
    {
        $this->loadConfig();
    }

    /**
     * 防止被克隆
     */
    private function __clone() {}


    /**
     * 获取配置项
     *
     * @return array
     */
    static public function getConfig()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance->config;
    }

    /**
     * 解析配置文件
     *
     * @return void
     */
    protected function loadConfig()
    {
        if (!file_exists($this->nanov_ini)) {
            var_dump($this->nanov_ini);
            echo 'nanov.ini does not exist' . PHP_EOL; exit;
        }

       $this->config = parse_ini_file($this->nanov_ini, true);

        if(!$this->config) {
            echo 'Parsing nanov.ini fail' . PHP_EOL; exit;
        }
    }
}