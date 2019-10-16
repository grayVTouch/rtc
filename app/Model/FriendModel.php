<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 10:42
 */

namespace App\Model;


use function core\convert_obj;
use function core\obj_to_array;

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
    public static function findByUserIdAndFriendId(int $user_id , int $friend_id)
    {
        $res = self::with(['user' , 'friend'])
            ->where([
                ['user_id' , '=' , $user_id] ,
                ['friend_id' , '=' , $friend_id] ,
            ])
            ->first();
        $res = convert_obj($res);
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

    public static function updateByUserIdAndFriendId(int $user_id , int $friend_id , array $param)
    {
        return self::where([
                    ['user_id' , '=' , $user_id] ,
                    ['friend_id' , '=' , $friend_id] ,
                ])
                ->update($param);
    }

    // 好友备注姓名
    public static function alias(int $user_id , int $friend_id): string
    {
        return (string) (self::where([
                    ['user_id' , '=' , $user_id] ,
                    ['friend_id' , '=' , $friend_id] ,
                ])
                ->value('alias'));
    }

    // 获取所有好友的id
    public static function getFriendIdByUserId(int $user_id): array
    {
        $res = self::where('user_id' , $user_id)->get();
        $id_list = [];
        foreach ($res as $v)
        {
            $id_list[] = $v->friend_id;
        }
        return $id_list;
    }


    public static function searchByUserIdAndNicknameAndLimit(int $user_id , string $nickname = '' , int $limit = 3)
    {
        $res = self::with(['user' , 'friend'])
            ->from('friend as f')
            ->join('user as u' , 'f.friend_id' , '=' , 'u.id')
            ->where([
                ['f.user_id' , '=' , $user_id] ,
                ['u.nickname' , 'like' , "%{$nickname}%"] ,
            ])
            ->select('f.*')
            ->limit($limit)
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

    public static function searchByUserIdAndAliasAndLimit(int $user_id , string $alias = '' , int $limit = 3)
    {
        $res = self::with(['user' , 'friend'])
            ->where([
                ['user_id' , '=' , $user_id] ,
                ['alias' , 'like' , "%{$alias}%"] ,
            ])
            ->limit($limit)
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

    // 搜索好友（用户名 + 昵称）
    public static function searchByUserIdAndValueAndLimitIdAndLimit(int $user_id , string $value , int $limit_id = 0 , int $limit = 20)
    {
        $where = [
            ['f.user_id' , '=' , $user_id]
        ];
        if (!empty($limit_id)) {
            $where[] = ['f.id' , '<' , $limit_id];
        }
        $res = self::with(['user' , 'friend'])
            ->from('friend as f')
            ->join('user as u' , 'u.id' , '=' , 'f.friend_id')
            ->where($where)
            ->orWhere([
                ['f.alias' , 'like' , "%{$value}%"] ,
                ['u.nickname' , 'like' , "%{$value}%"] ,
            ])
            ->select('f.*')
            ->orderBy('f.id' , 'desc')
            ->limit($limit)
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