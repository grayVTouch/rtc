<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/18
 * Time: 10:52
 */

namespace App\Model;


class UserOptionModel extends Model
{
    protected $table = 'user_option';

    public static function findByUserId(int $user_id)
    {
        $res = self::where('user_id' , $user_id)
            ->first();
        self::single($res);
        return $res;
    }

    public static function delByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)->delete();
    }
}