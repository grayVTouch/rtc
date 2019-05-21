<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 15:56
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

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
        return $this->belongsTo(User::class , 'user_id' , 'id');
    }

    public static function findById(int $id = 0)
    {
        $res = self::with([
                'group' ,
                'user' ,
            ])
            ->find($id);
        if (empty($res)) {
            return ;
        }
        self::single($res);
        Group::single($res->group);
        User::single($res->user);
        return $res;
    }

    public static function delByGroupId(int $group_id)
    {
        return self::where('group_id' , $group_id)->delete();
    }

    // 用户发送的最近一条信息：最近一条消息
    public static function recentMessage(int $group_id)
    {
        $res = DB::table('group_message as gm')
            ->leftJoin('user as u' , 'gm.user_id' , '=' , 'u.id')
            ->where([
                ['gm.group_id' , '=' , $group_id] ,
                ['u.role' , '=' , 'user'] ,
            ])
            ->orderBy('gm.id' , 'desc')
            ->select('gm.*')
            ->first();
        if (empty($res)) {
            return ;
        }
        $res->group = Group::findById($res->group_id);
        $res->user = User::findById($res->user_id);
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
            $v->group = Group::single($v->group);
            $v->user = User::single($v->user);
        }
        return $res;
    }

}