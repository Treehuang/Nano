<?php
/**
 * User: 黄树斌
 * Date: 2019/1/15 0015
 * Time: 9:28
 */

namespace Nano;

use Closure;
use Nano\Process;
use Nano\ProcessException;

class Master extends Process
{
    public function __construct()
    {
        $this->type = 'master';

        parent::__construct();
        ProcessException::info([
            'msg' => [
                'from'  => 'master',
                'extra' => 'master instance create'
            ]
        ]);

        $this->pipeMake();
    }

    protected function hangup(Closure $closure)
    {
        // TODO: Implement hangup() method.
    }
}