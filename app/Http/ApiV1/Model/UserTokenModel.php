<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 12:26
 */

namespace App\Http\ApiV1\Model;

class UserTokenModel extends Model
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

    public static function u_insertGetId(string $identifier , int $user_id , string $token , string $expire , string $platform)
    {
        return self::insertGetId([
            'identifier' => $identifier ,
            'user_id' => $user_id ,
            'token' => $token ,
            'expire' => $expire ,
            'platform' => $platform ,
        ]);
    }

    public static function delByUserIdAndPlatform(int $user_id , string $platform = '')
    {
        return self::where([
            ['user_id' , '=' , $user_id] ,
            ['platform' , '=' , $platform] ,
        ])->delete();
    }

    public static function delByToken(string $token)
    {
        return self::where('token' , $token)
            ->delete();
    }
}