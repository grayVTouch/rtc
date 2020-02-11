<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/30
 * Time: 11:35
 */

namespace App\WebSocket\V1\Model;


use function core\convert_obj;

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

    public static function updateByUserId(int $user_id , array $data = [])
    {
        return self::where('user_id' , $user_id)
            ->update($data);
    }

    public static function updateByUserIdAndJoinFriendMethodId(int $user_id , int $join_friend_method_id , array $data = [])
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['join_friend_method_id' , '=' , $join_friend_method_id] ,
        ])->update($data);
    }

    public static function getByUserId(int $user_id)
    {
        $res = self::where('user_id' , $user_id)
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    public static function findByUserIdAndJoinFriendMethodId(int $user_id , int $join_friend_method_id)
    {
        $res = self::where([
            ['user_id' , '=' , $user_id] ,
            ['join_friend_method_id' , '=' , $join_friend_method_id] ,
        ])->first();
        if (empty($res)) {
            return ;
        }
        $res = convert_obj($res);
        self::single($res);
        return $res;
    }

    public static function delByUserIdAndJoinFriendMethodId(int $user_id , int $join_friend_method_id)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['join_friend_method_id' , '=' , $join_friend_method_id] ,
        ])->delete();
    }
}