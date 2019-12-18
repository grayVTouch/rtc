<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

class PushReadStatusModel extends Model
{
    protected $table = 'push_read_status';
    public $timestamps = false;

    // 未读消息数量：统计
    public static function unreadCountByUserId(int $user_id)
    {
        return DB::table('push as p')
            ->leftJoin('push_read_status as prs' , 'p.id' , '=' , 'prs.push_id')
            ->where([
                ['prs.user_id' , '=' , $user_id] ,
                ['prs.is_read' , '=' , 0] ,
            ])
            ->count();
    }

    public static function findByUserIdAndPushId(int $user_id , int $push_id)
    {
        $res = self::where([
            ['user_id' , '=' , $user_id] ,
            ['push_id' , '=' , $push_id] ,
        ])->first();
        self::single($res);
        return $res;
    }

    public static function u_insertGetId(int $user_id , int $push_id , $type = '' , int $is_read = 0)
    {
        return self::insertGetId([
            'user_id' => $user_id ,
            'push_id' => $push_id ,
            'type'    => $type ,
            'is_read' => $is_read
        ]);
    }

    // 初始化
    public static function initByPushIdAndUserIds(int $push_id , array $user_id = [])
    {
        foreach ($user_id as $v)
        {
            self::u_insertGetId($v , $push_id , 0);
        }
    }

    public function u_push()
    {
        return $this->belongsTo(PushModel::class , 'push_id' , 'id');
    }

    public static function unreadByUserId(int $user_id , int $limit = 10)
    {
        $res = self::with('u_push')
            ->where([
                ['user_id' , '=' , $user_id] ,
                ['is_read' , '=' , 0] ,
            ])
            ->limit($limit)
            ->get();
        foreach ($res as $v)
        {
            self::single($v);
            PushModel::single($v->u_push);
        }
        return $res;
    }

    public static function updateIsReadByUserIdAndPushId(int $user_id , int $push_id , int $is_read)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['push_id' , '=' , $push_id] ,
        ])->update([
            'is_read' => $is_read
        ]);
    }

    public static function updateIsReadByUserIdAndType(int $user_id , string $type , int $is_read)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['type' , '=' , $type] ,
        ])->update([
            'is_read' => $is_read
        ]);
    }

    public static function delByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)
            ->delete();
    }
}