<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/9
 * Time: 11:41
 */

namespace App\Http\ApiV1\Model;


use function core\convert_obj;

class SessionModel extends Model
{
    protected $table = 'session';

    // 检查是否存在
    public static function exist(int $user_id , string $type , string $target_id)
    {
        return (self::where([
                ['user_id' , '=' , $user_id] ,
                ['type' , '=' , $type] ,
                ['target_id' , '=' , $target_id] ,
            ])->count()) > 0;
    }

    public static function findByUserIdAndTypeAndTargetIdAndTop(int $user_id , string $type , string $target_id , int $top)
    {
        $res = self::where([
                    ['user_id' , '=' , $user_id] ,
                    ['type' , '=' , $type] ,
                    ['target_id' , '=' , $target_id] ,
                    ['top' , '=' , $top] ,
                ])
                ->first();
        self::single($res);
        return $res;
    }

    public static function delByTypeAndTargetId(string $type , $target_id)
    {
        return self::where([
            ['type' , '=' , $type] ,
            ['target_id' , '=' , $target_id] ,
        ])->delete();
    }

    public static function findByUserIdAndTypeAndTargetId(int $user_id , string $type , string $target_id)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['type' , '=' , $type] ,
                ['target_id' , '=' , $target_id] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }

    public static function findByUserIdAndType(int $user_id , string $type)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['type' , '=' , $type] ,
            ])
            ->first();
        self::single($res);
        return $res;
    }

    // 获取置顶会话
    public static function topSessionByUserId(int $user_id)
    {
        $res = self::where([
                ['user_id' , '=' , $user_id] ,
                ['top' , '=' , 1] ,
            ])
            ->get();
        self::multiple($res);
        return $res;
    }

    public static function noTopCountByUserId(int $user_id , int $offset = 0 , int $limit = 10)
    {
        return self::where([
                ['user_id' , '=' , $user_id] ,
                ['top' , '=' , 0] ,
            ])
            ->count();
    }

    public static function noTopGetByUserIdAndOffsetAndLimit(int $user_id , int $offset = 0 , int $limit = 10)
    {
        $where = [
            ['user_id' , '=' , $user_id] ,
            ['top' , '=' , 0] ,
        ];
        $res = self::where($where)
            ->orderBy('update_time' , 'desc')
            ->orderBy('id' , 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            self::single($v);
        }
        return $res;
    }

    public static function getByUserId(int $user_id)
    {
        $where = [
            ['user_id' , '=' , $user_id] ,
        ];
        $res = self::where($where)
            ->orderBy('update_time' , 'desc')
            ->orderBy('id' , 'desc')
            ->get();
        self::multiple($res);
        return $res;
    }

    public static function getByUserIdAndType(int $user_id , string $type)
    {
        $res = self::where([
                    ['user_id' , '=' , $user_id] ,
                    ['type' , '=' , $type] ,
                ])
                ->get();
        self::multiple($res);
        return $res;
    }

    // 是否
    public static function existOtherByIds(int $user_id , array $id_list)
    {
        return (self::where('user_id' , '!=' , $user_id)
                ->whereIn('id' , $id_list)
                ->count()) >= 1;
    }

    public static function delByUserIdAndType(int $user_id , string $type)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['type' , '=' , $type] ,
        ])->delete();
    }

}