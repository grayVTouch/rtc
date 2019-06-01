<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

class PushReadStatus extends Model
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
                ['prs.is_read' , '=' , 'n'] ,
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
}