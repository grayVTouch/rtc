<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 15:56
 */

namespace App\Http\ApiV1\Model;


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
                $query->select('id')
                    ->from('delete_message_for_group')
                    ->whereRaw('rtc_delete_message_for_group.group_message_id = rtc_gm.id')
                    ->where([
                        ['user_id' , '=' , $user_id] ,
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

    public static function history(int $user_id , $group_id , string $join_group_time , int $limit_id = 0 , int $limit = 20)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
            // 另外，加载的消息记录必须 >= 加入群的时间
            ['create_time' , '>=' , $join_group_time] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '<' , $limit_id];
        }
        $res = self::with(['group' , 'user'])
            // 不能加载被删除的消息记录
            ->whereNotExists(function($query) use($user_id){
                $query->select('id')
                    ->from('delete_message_for_group')
                    ->whereRaw('rtc_group_message.id = rtc_delete_message_for_group.group_message_id')
                    ->where([
                        ['user_id' , '=' , $user_id] ,
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

    public static function lastest(int $user_id , $group_id , string $join_group_time , int $limit_id = 0)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
            // 加载消息的时间必须大于加入群的时间
            ['create_time' , '>=' , $join_group_time] ,
        ];
        if (!empty($limit_id)) {
            $where[] = ['id' , '>' , $limit_id];
        }
        $res = self::with(['group' , 'user'])
            ->whereNotExists(function($query) use($user_id){
                $query->select('id')
                    ->from('delete_message_for_group')
                    ->whereRaw('rtc_group_message.id = rtc_delete_message_for_group.group_message_id')
                    ->where([
                        ['user_id' , '=' , $user_id] ,
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

    public static function getByUserIdAndIdsExcludeDeleted(int $user_id , array $id_list = [])
    {
        $res = self::with(['user' , 'group'])
            ->whereIn('id' , $id_list)
            ->whereNotExists(function($query) use($user_id){
                $query->select('id')
                    ->from('delete_message_for_group')
                    ->where('user_id' , $user_id)
                    ->whereRaw('rtc_group_message.id = rtc_delete_message_for_group.group_message_id');
            })
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

    public static function getByTypeAndNotExpired(array $type = [])
    {
        $res = self::whereIn('type' , $type)
            ->where('res_expired' , '<>' , 1)
            ->get();
        self::multiple($res);
        return $res;
    }
}