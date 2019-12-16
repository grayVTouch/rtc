<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 15:21
 */

namespace App\Model;


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

}