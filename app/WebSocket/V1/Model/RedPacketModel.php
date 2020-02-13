<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/12
 * Time: 12:06
 */

namespace App\WebSocket\V1\Model;


class RedPacketModel extends Model
{
    protected $table = 'red_packet';

    public static function findByIdWithLock(int $id)
    {
        $res = self::lockForUpdate()
            ->find($id);
        if (empty($res)) {
            return ;
        }
        self::single($res);
        return $res;
    }
}