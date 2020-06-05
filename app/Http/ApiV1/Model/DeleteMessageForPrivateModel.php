<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:21
 */

namespace App\Http\ApiV1\Model;


class DeleteMessageForPrivateModel extends Model
{
    protected $table = 'delete_message_for_private';

    public static function u_insertGetId(string $identifier , int $user_id , int $message_id , string $chat_id)
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'user_id' => $user_id ,
            'message_id' => $message_id ,
            'chat_id' => $chat_id
        ]);
    }

    public static function countByChatIdAndMessageId(string $chat_id , int $message_id)
    {
        $count = self::where([
            ['chat_id' , '=' , $chat_id] ,
            ['message_id' , '=' , $message_id] ,
        ])->count();
        return (int) $count;
    }

    public static function delByChatId(string $chat_id)
    {
        return self::where('chat_id' , $chat_id)
            ->delete();
    }

    public static function delByMessageId(int $message_id)
    {
        return self::where('message_id' , $message_id)
            ->delete();
    }

    public static function delByMessageIds(array $id_list = [])
    {
        return self::whereIn('message_id' , $id_list)
            ->delete();
    }

}