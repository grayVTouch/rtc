<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/11/11
 * Time: 16:54
 */

namespace App\Model;


class TaskLogModel extends Model
{
    protected $table = 'task_log';

    public static function u_insertGetId($data = '' , $desc = '')
    {
        return self::insertGetId([
            'data' => $data ,
            'desc' => $desc
        ]);
    }
}