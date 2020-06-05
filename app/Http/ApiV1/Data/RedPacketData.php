<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2020/2/16
 * Time: 18:49
 */

namespace App\Http\ApiV1\Data;


use App\Http\ApiV1\Model\RedPacketModel;

class RedPacketData extends Data
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $res = RedPacketModel::findById($id);
        if (empty($res)) {
            return ;
        }
        $res->user = UserData::findByIdentifierAndId($identifier , $res->user_id);
        return $res;
    }
}