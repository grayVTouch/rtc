<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/10
 * Time: 11:38
 */

namespace App\WebSocket\V1\Model;


class GroupNoticeModel extends Model
{
    protected $table = 'group_notice';

    // 查找
    public static function findByUserIdAndGroupId(int $user_id , int $group_id)
    {
        $res = self::where([
            ['user_id' , '=' , $user_id] ,
            ['group_id' , '=' , $group_id] ,
        ])->first();
        self::single($res);
        return $res;
    }

    // 初始化
    public static function u_insertGetId(int $user_id , $group_id)
    {
        return self::insertGetId([
            'user_id'   => $user_id ,
            'group_id'  => $group_id ,
            'can_notice' => 1
        ]);
    }
}