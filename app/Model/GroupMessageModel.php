<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 15:56
 */

namespace App\Model;


use function core\convert_obj;
use Illuminate\Support\Facades\DB;
use Exception;

class GroupMessageModel extends Model
{
    protected $table = 'group_message';
    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(GroupModel::class , 'group_id' , 'id');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class , 'user_id' , 'id');
    }

    public static function single($m = null)
    {
        if (empty($m)) {
            return ;
        }
        if (!is_object($m)) {
            throw new Exception('参数 1 错误');
        }
    }

    public static function findById(int $id = 0)
    {
        $res = self::with(['group' , 'user'])
            ->find($id);
        if (empty($res)) {
            return ;
        }
        $res = convert_obj($res);
        self::single($res);
        GroupModel::single($res->group);
        UserModel::single($res->user);
        return $res;
    }

    public static function delByGroupId(int $group_id)
    {
        return self::where('group_id' , $group_id)->delete();
    }

    // 用户发送的最近一条信息：最近一条消息
    public static function recentMessage(int $user_id , int $group_id , string $role = 'none')
    {
        $where = [
            ['gm.group_id' , '=' , $group_id] ,
        ];
        $role = in_array($role , ['none' , 'user', 'admin']) ? $role : 'none';
        if ($role != 'none') {
            $where[] = ['u.role' , '=' , $role];
        }
        $res = self::with(['group' , 'user'])
            ->from('group_message as gm')
            ->leftJoin('user as u' , 'gm.user_id' , '=' , 'u.id')
            ->where($where)
            ->whereNotExists(function($query) use($user_id){
                $query->select('dm.id')
                    ->from('delete_message as dm')
                    ->whereRaw('rtc_dm.message_id = rtc_gm.id')
                    ->where([
                        ['dm.user_id' , '=' , $user_id] ,
                        ['dm.type' , '=' , 'group'] ,
                    ]);
            })
            ->orderBy('gm.id' , 'desc')
            ->select('gm.*')
            ->first();
        if (empty($res)) {
            return ;
        }
        self::single($res);
        UserModel::single($res->user);
        GroupModel::single($res->group);
        return $res;
    }

    public static function history(int $user_id , $group_id , int $limit_id = 0 , int $limit = 20)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '<' , $limit_id];
        }
        $res = self::with(['group' , 'user'])
            ->from('group_message as gm')
            ->whereNotExists(function($query) use($user_id){
                $query->select('dm.id')
                    ->from('delete_message as dm')
                    ->whereRaw('rtc_gm.id = rtc_dm.message_id')
                    ->where([
                        ['dm.type' , '=' , 'group'] ,
                        ['dm.user_id' , '=' , $user_id] ,
                    ]);
            })
            ->where($where)
            ->orderBy('id' , 'desc')
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            GroupModel::single($v->group);
            UserModel::single($v->user);
        }
        return $res;
    }

    public static function lastest(int $user_id , $group_id , int $limit_id = 0)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '>' , $limit_id];
        }
        $res = self::with(['group' , 'user'])
            ->from('group_message as gm')
            ->whereNotExists(function($query) use($user_id){
                $query->select('dm.id')
                    ->from('delete_message as dm')
                    ->whereRaw('rtc_gm.id = rtc_dm.message_id')
                    ->where([
                        ['dm.type' , '=' , 'group'] ,
                        ['dm.user_id' , '=' , $user_id] ,
                    ]);
            })
            ->where($where)
            ->orderBy('id' , 'desc')
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            GroupModel::single($v->group);
            UserModel::single($v->user);
        }
        return $res;
    }

    public static function u_insertGetId(int $user_id , int $group_id , string $type , string $message = '' , string $extra = '')
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'group_id' => $group_id ,
            'type' => $type ,
            'message' => $message ,
            'extra' => $extra ,
        ]);
    }

    // 通过群id获取所有的消息记录id
    public static function getIdByGroupId(int $group_id): array
    {
        $id_list = [];
        $res = self::where('group_id' , $group_id)->get();
        foreach ($res as $v)
        {
            $id_list[] = $v->id;
        }
        return $id_list;
    }

    public static function getByGroupId(int $group_id)
    {
        $res = self::with(['group' , 'user'])
            ->where('group_id' , $group_id)
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

    public static function getByGroupIdAndUserId(int $group_id , int $user_id)
    {
        $res = self::with(['group' , 'user'])
            ->where([
                ['group_id' , '=' , $group_id] ,
                ['user_id' , '=' , $user_id] ,
            ])
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

    public static function countByGroupIdAndValue(int $group_id , string $value)
    {
        return self::where([
            ['group_id' , '=' , $group_id] ,
            ['message' , 'like' , "%{$value}%"] ,
        ])->count();
    }

    public static function searchByGroupIdAndValueAndLimitIdAndLimit(int $group_id , string $value , int $limit_id = 0 , int $limit = 20)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
            ['message' , 'like' , "%{$value}%"] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '<' , $limit_id];
        }
        $res = self::with(['user'])
            ->where($where)
            ->limit($limit)
            ->orderBy('id' , 'desc')
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
            UserModel::single($v->user);
        }
        return $res;
    }
}