<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;

use Illuminate\Support\Facades\DB;

class MessageReadStatusModel extends Model
{
    protected $table = 'message_read_status';
    public $timestamps = false;

    // 初始化消息
    public static function initByMessageId(int $message_id , int $sender , int $receiver)
    {
        self::u_insertGetId($sender , $message_id , 1);
        self::u_insertGetId($receiver , $message_id , 0);
    }

    public static function u_insertGetId(int $user_id , int $message_id , int $is_read)
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'message_id' => $message_id ,
            'is_read' => $is_read
        ]);
    }

    // 批量更新：更新状态
    public static function updateReadStatus(int $user_id , string $chat_id , int $is_read)
    {
        return DB::table('message_read_status as mrs')
            ->leftJoin('message as m' , 'mrs.message_id' , '=' , 'm.id')
            ->where([
                ['mrs.user_id' , '=' , $user_id] ,
                ['m.chat_id' , '=' , $chat_id] ,
            ])
            ->update([
                'is_read' => $is_read
            ]);
    }

    /**
     * 更新消息读取状态
     *
     */
    public static function updateReadStatusByUserIdAndMessageId(int $user_id , int $message_id , int $is_read)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['message_id' , '=' , $message_id] ,
        ])->update([
            'is_read' => $is_read
        ]);
    }

    public static function isRead(int $user_id , int $message_id)
    {
        return (int) (self::where([
                ['user_id' , '=' , $user_id] ,
                ['message_id' , '=' , $message_id] ,
            ])
            ->value('is_read'));
    }

    // 已读/未读消息数量
    public static function countByUserIdAndIsRead(int $user_id , int $is_read)
    {
        return (int) (self::where([
            ['user_id' , '=' , $user_id] ,
            ['is_read' , '=' , $is_read] ,
        ])->count());
    }
}