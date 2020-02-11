<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:21
 */

namespace App\WebSocket\V1\Model;


class DeleteMessageForGroupModel extends Model
{
    protected $table = 'delete_message_for_group';

    public static function u_insertGetId(string $identifier , int $user_id , int $group_message_id , int $group_id)
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'user_id' => $user_id ,
            'group_message_id' => $group_message_id ,
            'group_id' => $group_id
        ]);
    }

    public static function countByGroupIddAndGroupMessageId(int $group_id , int $group_message_id)
    {
        $count = self::where([
            ['group_id' , '=' , $group_id] ,
            ['group_message_id' , '=' , $group_message_id] ,
        ])->count();
        return (int) $count;
    }

    public static function delByGroupId(string $group_id)
    {
        return self::where('group_id' , $group_id)
            ->delete();
    }

    public static function delByGroupMessageId(int $group_message_id)
    {
        return self::where('group_message_id' , $group_message_id)
            ->delete();
    }

}