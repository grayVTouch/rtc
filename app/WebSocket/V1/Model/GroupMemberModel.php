<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:02
 */

namespace App\WebSocket\V1\Model;

use function core\convert_obj;
use Illuminate\Support\Facades\DB;

class GroupMemberModel extends Model
{
    protected $table = 'group_member';
    public $timestamps = false;

    // 获取用户id
    public static function getUserIdByGroupId(int $group_id = 0)
    {
        $res = self::where('group_id' , $group_id)->get();
        $id_list = [];
        foreach ($res as $v)
        {
            $id_list[] = $v->user_id;
        }
        return $id_list;
    }

    public static function findByUserIdAndGroupId(int $user_id = 0 , int $group_id = 0)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['group_id' , '=' , $group_id] ,
            ])
            ->first();
        $res = convert_obj($res);
        self::single($res);
        return $res;
    }

    // 删除掉排除了给定成员之后的群成员
    public static function delOther($group_id , array $exclude = [])
    {
        return self::where('group_id' , $group_id)
            ->whereNotIn('user_id' , $exclude)
            ->delete();
    }

    // 获取排除了给定成员之外的群成员列表
    public static function getOtherByGroupId($group_id , array $exclude = [])
    {
        $res = self::where('group_id' , $group_id)
            ->whereNotIn('user_id' , $exclude)
            ->get();
        self::multiple($res);
        return $res;
    }

    // 获取非平台用户
    public static function getWaiterIdByGroupId(int $group_id = 0)
    {
        $res = DB::table('group_member as gm')
            ->leftJoin('user as u' , 'gm.user_id' , '=' , 'u.id')
            ->where([
                ['gm.group_id' , '=' , $group_id] ,
                ['u.role' , '=' , 'admin'] ,
            ])
            ->select('gm.*')
            ->get();
        $id_list = [];
        foreach ($res as $v)
        {
            $id_list[] = $v->user_id;
        }
        return $id_list;
    }

    // 获取用户加入过的群组
    public static function getGroupIdByUserId(int $user_id = 0)
    {
        $res = self::where('user_id' , $user_id)->get();
        $id_list = [];
        foreach ($res as $v)
        {
            $id_list[] = $v->group_id;
        }
        return $id_list;
    }

    public static function delByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)->delete();
    }

    public static function delByGroupId(int $group_id)
    {
        return self::where('group_id' , $group_id)->delete();
    }

    public static function delByUserIdAndGroupId(int $user_id , int $group_id)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['group_id' , '=' , $group_id] ,
        ])->delete();
    }


    public static function getByUserId(int $user_id)
    {
        $res = self::with(['group' , 'user'])
            ->where('user_id' , $user_id)
            ->get();
        foreach ($res as $v)
        {
            self::single($v);
            GroupModel::single($v->group);
            UserModel::single($v->user);
        }
        return $res;
    }

    public function group()
    {
        return $this->belongsTo(GroupModel::class , 'group_id' , 'id');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public static function u_insertGetId(string $identifier , int $user_id , int $group_id , string $alias = '')
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'user_id' => $user_id ,
            'group_id' => $group_id ,
            'alias' => $alias
        ]);
    }

    /**
     * 检查是否存在
     *
     * @param int $user_id
     * @param int $group_id
     * @return bool
     */
    public static function exist(int $user_id , int $group_id)
    {
        return !empty(self::findByUserIdAndGroupId($user_id , $group_id));
    }

    public static function getByGroupId(int $group_id , int $limit = 0)
    {
        $res = self::with(['user' , 'group'])
            ->where('group_id' , $group_id);
        if (empty($limit)) {
            $res = $res->get();
        } else {
            $res = $res
                ->limit($limit)
                ->get();
        }
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
            GroupModel::single($v->group);
        }
        return $res;
    }

    public static function getByGroupIdV1(int $group_id , int $limit_id = 0 , int $limit = 10)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '>' , $limit_id];
        }
        $res = self::where('group_id' , $group_id)
            ->orderBy('id' , 'asc')
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    public static function getByGroupIdAndMasterIdAndLimitIdAndLimitExcludeMaster(int $group_id ,  int $master_id , int $limit_id = 0 , int $limit = 10)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
            ['user_id' , '<>' , $master_id] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '>' , $limit_id];
        }
        $res = self::where($where)
            ->orderBy('id' , 'asc')
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    public static function getByGroupIdAndLimit(int $group_id , int $limit = 9)
    {
        $res = self::where('group_id' , $group_id)
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        self::multiple($res);
        return $res;
    }

    public static function countByGroupId(int $group_id): int
    {
        return (int) (self::where('group_id' , $group_id)->count());
    }

    public static function firstByGroupIdAndValueAndLimitIdAndLimit(int $group_id , string $value , int $limit_id = 0 , int $limit = 10)
    {
        $where = [
            ['gm.group_id' , '=' , $group_id] ,
            ['u.nickname' , 'like' , "%{$value}%"] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['gm.id' , '<' , $limit_id];
        }
        $res = self::with(['group' , 'user'])
            ->from('group_member as gm')
            ->join('user as u' , 'gm.user_id' , '=' , 'u.id')
            ->where($where)
            ->select('gm.*,')
            ->orderBy('gm.id' , 'desc')
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
            GroupModel::single($v->group);
        }
        return $res;
    }

    public static function searchByGroupIdAndValueOnlyFirst(int $group_id , string $value)
    {
        $value = strtolower($value);
        $res = self::with(['group' , 'user'])
            ->from('group_member as gm')
            ->join('user as u' , 'gm.user_id' , '=' , 'u.id')
            ->where([
                ['gm.group_id' , '=' , $group_id] ,
            ])
            ->whereRaw(DB::raw("lower(rtc_u.nickname) like '%{$value}%'"))
            ->select('gm.*')
            ->first();
        if (empty($res)) {
            return ;
        }
        $res = convert_obj($res);
        self::single($res);
        UserModel::single($res->user);
        GroupModel::single($res->group);
        return $res;
    }

    // 是否部分存在
    public static function someoneExist(array $id_list , int $group_id)
    {
        return (self::whereIn('user_id' , $id_list)
            ->where('group_id' , $group_id)
            ->count()) > 0;
    }

    //
    public static function updateByUserIdAndGroupId(int $user_id , int $group_id , array $param = [])
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['group_id' , '=' , $group_id] ,
        ])->update($param);
    }

    // 客服群
    public static function customer(int $user_id)
    {
        $res = self::with(['group' , 'user'])
            ->from('group_member as gm')
            ->leftJoin('group as g' , 'gm.group_id' , '=' , 'g.id')
            ->where([
                ['gm.user_id' , '=' , $user_id] ,
                ['g.is_service' , '=' , 1] ,
            ])
            ->first();
        if (empty($res)) {
            return ;
        }
        self::single($res);
        UserModel::single($res->user);
        GroupModel::single($res->group);
        return $res;
    }
}