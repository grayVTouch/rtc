<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 13:38
 */

namespace App\Data;


use App\Cache\GroupCache;

class GroupData extends Data
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $res = GroupCache::findByIdentifierAndId($identifier , $id);
        if (empty($res)) {
            return ;
        }
        $res->user = UserData::findByIdentifierAndId($identifier , $res->user_id);
        return $res;
    }
}