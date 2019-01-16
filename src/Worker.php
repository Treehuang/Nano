<?php
/**
 * User: 黄树斌
 * Date: 2019/1/15 0015
 * Time: 16:54
 */

namespace Nano;

use Closure;

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

    protected function hangup(Closure $closure)
    {
        // TODO: Implement hangup() method.
    }
}