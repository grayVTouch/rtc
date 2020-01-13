<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/18
 * Time: 10:34
 */

namespace App\Model;


use function core\convert_obj;
use Illuminate\Support\Facades\DB;

class PushModel extends Model
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
    public static function u_insertGetId(string $identifier , string $push_type , string $type , string $role = 'all' , int $user_id = 0 , string $title = '' , string $content = '' , string $desc = '' , int $is_show = 1)
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'push_type' => $push_type ,
            'user_id' => $user_id ,
            'role' => $role ,
            'type' => $type ,
            'title' => $title ,
            'content' => $content ,
            'desc' => $desc ,
            'is_show' => $is_show ,
        ]);
    }

    // 未读消息推送数量
    public static function unreadCountByUserIdAndTypeAndIsRead(int $user_id , string $type = '' , int $is_read = 0)
    {
        $where = [
            ['prs.user_id' , '=' , $user_id] ,
            ['prs.is_read' , '=' , $is_read] ,
        ];
        if (!empty($type)) {
            $where[] = ['p.type' , '=' , $type];
        }
        $count = self::from('push as p')
            ->leftJoin('push_read_status as prs' , 'p.id' , '=' , 'prs.push_id')
            ->where($where)
            ->count();
        return (int) $count;
    }

    // 最近一条推送消息
    public static function recentByUserIdAndType(int $user_id , string $type = '')
    {
        $where = [
            ['prs.user_id' , '=' , $user_id] ,
        ];
        if (!empty($type)) {
            $where[] = ['p.type' , '=' , $type];
        }
        // sql 语句建议
        $res = self::from('push as p')
            ->leftJoin('push_read_status as prs' , 'p.id' , '=' , 'prs.push_id')
            ->where($where)
            ->orderBy('p.id' , 'desc')
            ->select('p.*' , 'prs.create_time')
            ->orderBy('prs.id' , 'desc')
            ->first();
        self::single($res);
        return $res;
    }

    // 最近一条推送消息
    public static function getByUserIdAndTypeAndLimitIdAndLimit(int $user_id , $type = '' , $limit_id = 0 , int $limit = 20)
    {
        $where = [
            ['prs.user_id' , '=' , $user_id] ,
        ];
        if (!empty($type)) {
            $where[] = ['p.type' , '=' , $type];
        }
        if (!empty($limit_id)) {
            $where[] = ['p.id' , '<' , $limit_id];
        }
        $res = self::from('push as p')
            ->leftJoin('push_read_status as prs' , 'p.id' , '=' , 'prs.push_id')
            ->where($where)
            ->orderBy('prs.id' , 'desc')
            ->select('p.*' , 'prs.create_time')
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
        }
        return $res;
    }

    public static function latestByUserIdAndTypeAndLimitId(int $user_id , $type = '' , $limit_id = 0)
    {
        $where = [
            ['prs.user_id' , '=' , $user_id] ,
        ];
        if (!empty($type)) {
            $where[] = ['p.type' , '=' , $type];
        }
        if (!empty($limit_id)) {
            $where[] = ['p.id' , '>' , $limit_id];
        }
        $res = self::from('push as p')
            ->leftJoin('push_read_status as prs' , 'p.id' , '=' , 'prs.push_id')
            ->where($where)
            ->orderBy('prs.id' , 'desc')
            ->select('p.*' , 'prs.create_time')
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
        }
        return $res;
    }
}