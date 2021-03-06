<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/30
 * Time: 11:35
 */

namespace App\Model;


class UserJoinFriendOptionModel extends Model
{
    protected $table = 'user_join_friend_option';

    public static function enable(int $user_id , int $join_friend_method_id) :int
    {
        return (int) (self::where([
                ['user_id' , '=' , $user_id] ,
                ['join_friend_method_id' , '=' , $join_friend_method_id] ,
            ])
            ->value('enable'));
    }

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