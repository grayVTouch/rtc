<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 11:37
 */

namespace App\Cache;


use App\Model\GroupModel;
use App\Redis\GroupRedis;

class GroupCache extends Cache
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $cache = GroupRedis::group($identifier , $id);
        if (empty($cache)) {
            $group = GroupModel::findById($id);
            if (empty($group)) {
                return ;
            }
            GroupRedis::group($identifier , $id , $group);
            $cache = $group;
        }
        return $cache;
    }

    public static function delByIdentifierAndId(string $identifier , int $id)
    {
        return GroupRedis::delGroup($identifier , $id);
    }
}