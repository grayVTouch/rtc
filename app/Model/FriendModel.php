<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 10:42
 */

namespace App\Model;


use function core\convert_obj;

class FriendModel extends Model
{
    protected $table = 'friend';
    /**
     * 检查是否时好友
     *
     * @param $user_id
     * @param $friend_id
     * @return mixed
     * @throws \Exception
     */
    public static function findByUserIdAndFriendId(int $user_id , int $friend_id): ?FriendModel
    {
        $res = self::where([
            ['user_id' , '=' , $user_id] ,
            ['friend_id' , '=' , $friend_id] ,
        ])->first();
        self::single($res);
        return $res;
    }

    /**
     * 检查是否时好友
     *
     * @param int $user_id
     * @param int $friend_id
     * @return bool
     * @throws \Exception
     */
    public static function isFriend(int $user_id , int $friend_id): bool
    {
        return !empty(self::findByUserIdAndFriendId($user_id , $friend_id));
    }

    /**
     * 添加记录
     *
     * @param int $user_id
     * @param int $friend_id
     * @return mixed
     */
    public static function u_insertGetId(int $user_id , int $friend_id): int
    {
        return self::insertGetId([
            'user_id'   => $user_id ,
            'friend_id' => $friend_id
        ]);
    }

    // 删除用户
    public static function delByUserIdAndFriendId(int $user_id , int $friend_id)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['friend_id' , '=' , $friend_id] ,
        ])->delete();
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public function friend()
    {
        return $this->belongsTo(UserModel::class , 'friend_id' , 'id');
    }

    public static function getByUserId(int $user_id)
    {
        $res = self::with(['user' , 'friend'])
            ->where('user_id' , $user_id)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
            UserModel::single($v->friend);
        }
        return $res;
    }
}