<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/30
 * Time: 10:14
 */

namespace App\Model;


class DeleteMessageModel extends Model
{
    protected $table = 'delete_message';

    public static function delByTypeAndMessageId(string $type , int $message_id)
    {
        return self::where([
            ['type' , '=' , $type] ,
            ['message_id' , '=' , $message_id] ,
        ])->delete();
    }

    // 检查计数
    public static function countByTypeAndTargetIdAndMessageId(string $type , $target_id , int $message_id): int
    {
        return (int) (self::where([
            ['type' , '=' , $type] ,
            ['target_id' , '=' , $target_id] ,
            ['message_id' , '=' , $message_id] ,
        ])->count());
    }

    public static function u_insertGetId(string $type , int $user_id , int $message_id , $target_id)
    {
        return self::insertGetId([
            'type' => $type ,
            'user_id' => $user_id ,
            'message_id' => $message_id ,
            'target_id' => $target_id ,
        ]);
    }

    public static function delByTypeAndTargetId(string $type , $target_id)
    {
        return self::where([
            ['type' , '=' , $type] ,
            ['target_id' , '=' , $target_id] ,
        ])->delete();
    }


}