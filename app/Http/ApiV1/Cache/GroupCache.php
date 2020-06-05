<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 11:37
 */

namespace App\Http\ApiV1\Cache;


use App\Http\ApiV1\Model\GroupModel;
use App\Http\ApiV1\Redis\GroupRedis;

class GroupCache extends Cache
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $cache = GroupRedis::groupByIdentifierAndGroupIdAndValue($identifier , $id);
        if (!empty($cache)) {
            return $cache;
        }
        $cache = GroupModel::findById($id);
        if (empty($cache)) {
            return ;
        }
        GroupRedis::groupByIdentifierAndGroupIdAndValue($identifier , $id , $cache);
        return $cache;
    }

    public static function delByIdentifierAndId(string $identifier , int $id)
    {
        return GroupRedis::delByIdentifierAndGroupId($identifier , $id);
    }
}