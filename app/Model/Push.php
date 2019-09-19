<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;


use Illuminate\Support\Facades\DB;

class Push extends Model
{
    protected $table = 'push';
    public $timestamps = false;

    // 未读的推送
    public static function unreadByUserId(int $user_id , int $limit = 20)
    {
        $res = DB::table('push as p')
            ->leftJoin('push_read_status as prs' , 'p.id' , '=' , 'prs.push_id')
            ->where([
                ['prs.user_id' , '=' , $user_id] ,
                ['prs.is_read' , '=' , 0] ,
            ])
            ->select('p.*')
            ->limit($limit)
            ->get();
        self::multiple($res);
        return $res;
    }

    // 新增数据
    public static function u_insertGetId(string $identifier , string $push_type , string $type , string $data = '' , string $role = 'all' , int $user_id = 0)
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'push_type' => $push_type ,
            'user_id' => $user_id ,
            'role' => $role ,
            'type' => $type ,
            'data' => $data ,
        ]);
    }
}