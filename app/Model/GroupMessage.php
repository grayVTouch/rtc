<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 15:56
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;
use Exception;

class GroupMessage extends Model
{
    protected $table = 'group_message';
    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(Group::class , 'group_id' , 'id');
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
        $m->message_type = 'group';
    }

    public static function findById(int $id = 0)
    {
        $res = self::with([
                'group' ,
                'user',
            ])
            ->find($id);
        if (empty($res)) {
            return ;
        }
        self::single($res);
        Group::single($res->group);
        UserModel::single($res->user);
        return $res;
    }

    public static function delByGroupId(int $group_id)
    {
        return self::where('group_id' , $group_id)->delete();
    }

    // 用户发送的最近一条信息：最近一条消息
    public static function recentMessage(int $group_id , string $role = 'none')
    {
        $where = [
            ['gm.group_id' , '=' , $group_id] ,
        ];
        $role = in_array($role , ['none' , 'user', 'admin']) ? $role : 'none';
        if ($role != 'none') {
            $where[] = ['u.role' , '=' , $role];
        }
        $res = DB::table('group_message as gm')
            ->leftJoin('user as u' , 'gm.user_id' , '=' , 'u.id')
            ->where($where)
            ->orderBy('gm.id' , 'desc')
            ->select('gm.*')
            ->first();
        if (empty($res)) {
            return ;
        }
        $res->group = Group::findById($res->group_id);
        $res->user = UserModel::findById($res->user_id);
        self::single($res);
        return $res;
    }

    public static function history($group_id , int $group_message_id = 0 , int $limit = 20)
    {
        $where = [
            ['group_id' , '=' , $group_id] ,
        ];
        if (!empty($group_message_id)) {
            $where[] = ['id' , '<' , $group_message_id];
        }
        $res = self::with(['group' , 'user'])->where($where)
            ->orderBy('id' , 'desc')
            ->limit($limit)
            ->get();
        foreach ($res as $v)
        {
            self::single($v);
            Group::single($v->group);
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

}