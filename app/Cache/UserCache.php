<?php

namespace App\Cache;

use App\Model\UserModel;
use App\Redis\UserRedis;

/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/13
 * Time: 10:55
 */

class UserCache
{
    public static function findByIdentifierAndId(string $identifier , int $id)
    {
        $cache = UserRedis::userByIdentifierAndUserId($identifier , $id);
        if (!empty($cache)) {
            return $cache;
        }
        $cache = UserModel::findByIdWithV1($id);
        UserRedis::userByIdentifierAndUserId($identifier , $id , $cache);
        return $cache;
    }

    public static function updateById(string $identifier , $id , array $data = [])
    {
        UserModel::updateById($id , $data);
        UserRedis::delUserByIdentifierAndUserId($identifier , $id);
    }

    public static function updateByIds(string $identifier , array $ids = [] , array $data = [])
    {
        foreach ($ids as $v)
        {
            self::updateById($identifier , $v , $data);
        }
    }

    public static function delByIdentifierAndId(string $identifier , int $id)
    {
        return UserRedis::delUserByIdentifierAndUserId($identifier , $id);
    }
}