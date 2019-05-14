<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 12:26
 */

namespace App\Model;

class UserToken extends Model
{
    protected $table = 'user_token';
    public $timestamps = false;

    public static function findByToken($token = '')
    {
        $res = self::where('token' , $token)
            ->first();
        self::single($res);
        return $res;
    }
}