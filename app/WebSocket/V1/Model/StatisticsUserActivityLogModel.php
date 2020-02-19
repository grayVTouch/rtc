<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/19
 * Time: 11:14
 */

namespace App\WebSocket\V1\Model;


class StatisticsUserActivityLogModel extends Model
{
    protected $table = 'statistics_user_activity_log';

    public static function findByIdentifierAndDate(string $identifier , string $date)
    {
        $res = self::where([
                    ['identifier' , '=' , $identifier] ,
                    ['date' , '=' , $date] ,
                ])
                ->first();
        self::single($res);
        return $res;
    }

    public static function findByDate(string $date)
    {
        $res = self::where([
            ['date' , '=' , $date] ,
        ])
            ->first();
        self::single($res);
        return $res;
    }
}