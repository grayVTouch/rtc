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
    public static function unread(int $user_id , int $limit = 20)
    {
        $res = DB::table('push as p')
            ->leftJoin('push_read_status as prs' , 'p.id' , '=' , 'prs.push_id')
            ->where([
                ['prs.user_id' , '=' , $user_id] ,
                ['prs.is_read' , '=' , 'n'] ,
            ])
            ->select('p.*')
            ->limit($limit)
            ->get();
        self::multiple($res);
        return $res;
    }
}