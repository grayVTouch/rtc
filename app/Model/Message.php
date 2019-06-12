<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 16:53
 */

namespace App\Model;


class Message extends Model
{
    protected $table = 'message';
    public $timestamps = false;

    /**
     * 新增数据
     *
     * @param int $user_id
     * @param int $group_id
     * @param string $type
     * @param string $message
     * @param string $extra
     * @return mixed
     */
    public static function u_insertGetId(int $user_id , string $chat_id , string $type , string $message = '' , string $extra = '')
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'chat_id' => $chat_id ,
            'type'  => $type ,
            'message' => $message ,
            'extra' => $extra ,
        ]);
    }

    /**
     * 删除消息
     *
     * @param string $chat_id
     * @return mixed
     */
    public static function delByChatId(string $chat_id)
    {
        return self::where('chat_id' , $chat_id)->delete();
    }
}