<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/12
 * Time: 12:06
 */

namespace App\WebSocket\V1\Model;


class FundLogModel extends Model
{
    protected $table = 'fund_log';

    public static function delByUserId(int $user_id)
    {
        return self::where('user_id' , $user_id)
            ->delete();
    }
}