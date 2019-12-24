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
use Illuminate\Support\Facades\DB;

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

    public static function findByUserIdAndFriendIdWithV1(int $user_id , int $friend_id)
    {
        $res = self::where([
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
    public static function u_insertGetId(string $identifier , int $user_id , int $friend_id): int
    {
        return self::insertGetId([
            'identifier' => $identifier ,
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

    // 获取不在黑名单列表的好友列表
    public static function getByUserIdNotInBlacklist(int $user_id)
    {
        $res = self::with(['user' , 'friend'])
            ->from('friend as f')
            ->where('f.user_id' , $user_id)
            ->whereNotExists(function($query) use($user_id){
                $query->select('b.id')
                    ->from('blacklist as b')
                    ->where('b.user_id' , $user_id)
                    ->whereRaw('rtc_b.block_user_id = rtc_f.friend_id');
            })
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
        $ins = self::with(['user' , 'friend'])
            ->where([
                ['user_id' , '=' , $user_id] ,
                ['alias' , 'like' , "%{$alias}%"] ,
            ]);
        if (!empty($limit)) {
            $ins->limit($limit);
        }
        $res = $ins->get();
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
    public static function searchByUserIdAndValueAndLimit(int $user_id , string $value , int $limit = 20)
    {
        $value = strtolower($value);
        $where = [
            ['f.user_id' , '=' , $user_id]
        ];
        if (!empty($limit_id)) {
            $where[] = ['f.id' , '<' , $limit_id];
        }
        $ins = self::with(['user' , 'friend'])
            ->from('friend as f')
            ->join('user as u' , 'u.id' , '=' , 'f.friend_id')
            ->where($where)
//            ->whereRaw('lower(rtc_f.alias) like "%:alias%" or rtc_u.nickname like :nickname' , [
//                'alias' => "%{$value}%" ,
//                'nickname' => "%{$value}%" ,
//            ])
//            ->where(function($query) use($value){
//                $query->where('f.alias' , 'like' , "%{$value}%")
//                    ->orWhere('u.nickname' , 'like' , "%{$value}%");
//            })
            ->whereRaw('lower(rtc_friend.alias) like "%:alias%" or lower(rtc_user.nickname) like "%:nickname%"' , [
                'alias' => $value ,
                'nickname' => $value
            ])
            ->select('f.*')
            ->orderBy('f.id' , 'desc');
//        if (!empty($limit)) {
//            $ins->limit($limit);
//        }
        $res = $ins->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
            UserModel::single($v->friend);
        }
        return $res;
    }

    // 搜索用户
    public static function searchByUserIdWithAliasAndNicknameAndUsername(int $user_id , $value)
    {
        $res = self::with(['user' , 'friend'])
            ->from('friend as f')
            ->leftJoin('user as u' , 'u.id' , '=' , 'f.friend_id')
            ->where('f.user_id' , $user_id)
            ->where(function($query) use($value){
                $query->where('f.alias' , 'like' , "%{$value}%")
                    ->orWhere('u.nickname' , 'like' , "%{$value}%")
                    ->orWhere('u.username' , 'like' , "%{$value}%");
            })
            ->select('f.*')
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

    // 客服
    public static function waiter(int $user_id)
    {
        $res = self::with(['user' , 'friend'])
            ->from('friend as f')
            ->leftJoin('user as u' , 'u.id' , '=' , 'f.friend_id')
            ->where([
                ['f.user_id' , '=' , $user_id] ,
                ['u.is_system' , '=' , 1] ,
            ])
            ->select('f.*')
            ->first();
        if (empty($res)) {
            return ;
        }
        self::single($res);
        UserModel::single($res->user);
        UserModel::single($res->friend);
        return $res;
    }
}