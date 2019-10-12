<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/11
 * Time: 15:24
 */

namespace App\Model;


use function core\convert_obj;

class ClearTimerLogModel extends Model
{
    protected $table = 'clear_timer_log';

    // 获取某用户最后一次执行的记录
    public static function lastByTypeAndUserId(string $type , int $user_id)
    {
        $res = self::where([
                ['type' , '=' , $type] ,
                ['user_id' , '=' , $user_id] ,
            ])
            ->orderBy('id' , 'desc')
            ->first();
        $res = convert_obj($res);
        self::single($res);
        return $res;
    }

    public static function u_insertGetId(int $user_id , string $type)
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'type' => $type
        ]);
    }
}