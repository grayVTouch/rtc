<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 10:02
 */

namespace App\Model;


class GroupMember extends Model
{
    protected $table = 'group_member';
    public $timestamps = false;

    // 获取用户id
    public static function getUserIdByGroupId($group_id)
    {
        $res = self::where('group_id' , $group_id)->get();
        $id_list = [];
        foreach ($res as $v)
        {
            $id_list[] = $v->user_id;
        }
        return $id_list;
    }

    public static function findByUserIdAndGroupId($user_id , $group_id)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['group_id' , '=' , $group_id] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }
}