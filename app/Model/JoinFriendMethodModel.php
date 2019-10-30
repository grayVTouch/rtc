<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/30
 * Time: 11:28
 */

namespace App\Model;


class JoinFriendMethodModel extends Model
{
    protected $table = 'join_friend_method';

    public static function updateByUserIdAndJoinFriendMethodIdAndEnable(int $user_id , int $join_friend_method_id , int $enable)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['join_friend_method_id' , '=' , $join_friend_method_id] ,
        ])->update([
            'enable' => $enable
        ]);
    }
}