<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:21
 */

namespace App\Model;


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

}