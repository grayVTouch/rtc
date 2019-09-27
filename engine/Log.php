<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/18
 * Time: 9:49
 */

namespace Engine;

use Core\Lib\Log as BaseLog;

class Log extends BaseLog
{
    public function log(string $log = '' , $flag = 'runtime')
    {
        return $this->write(sprintf('[%s] %10s %s' , date('Y-m-d H:i:s') , $log , $flag));
    }
}