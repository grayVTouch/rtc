<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/9
 * Time: 11:41
 */

namespace App\Model;


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

    // 更新用户信息
    public static function updateByUserIdAndTypeAndTargetId(int $user_id , string $type , string $target_id , int $top)
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['type' , '=' , $type] ,
            ['target_id' , '=' , $target_id] ,
        ])->update([
            'top' => $top
        ]);
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
}