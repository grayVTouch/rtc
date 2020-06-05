<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/19
 * Time: 11:14
 */

namespace App\Http\ApiV1\Model;


class UserActivityLogModel extends Model
{
    protected $table = 'user_activity_log';

    public static function findByUserIdAndDate(int $user_id , string $date)
    {
        $res = self::where([
                    ['user_id' , '=' , $user_id] ,
                    ['date' , '=' , $date] ,
                ])
                ->first();
        self::single($res);
        return $res;
    }
}