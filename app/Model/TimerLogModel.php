<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/12
 * Time: 11:52
 */

namespace App\Model;


class TimerLogModel extends Model
{
    protected $table = 'timer_log';

    // 记录定时器执行日志
    public static function u_insertGetId(string $log , string $type = 'common')
    {
        return self::insertGetId([
            'type' => $type ,
            'log' => $log
        ]);
    }

    // 追加日志
    public static function appendById(int $id , string $log)
    {
        $res = self::findById($id);
        $log = empty($res->log) ? $log : sprintf('%s%s' , $res->log , $log);
        return self::updateById($id , [
            'log' => $log
        ]);
    }
}