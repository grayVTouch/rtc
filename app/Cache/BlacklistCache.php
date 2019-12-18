<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 18:57
 */

namespace App\Cache;


use App\Model\BlacklistModel;
use App\Redis\BlacklistRedis;

class BlacklistCache extends Cache
{
    public static function blockedByIdentifierAndUserIdAndBlockUserId(string $identifier , int $user_id , int $block_user_id)
    {
        $cache = BlacklistRedis::blockedByIdentifierAndUserIdAndBlockUserIdAndValue($identifier , $user_id , $block_user_id);
        if (!empty($cache)) {
            return $cache;
        }
        $cache = BlacklistModel::blocked($user_id , $block_user_id);
        if (empty($cache)) {
            return ;
        }
        $cache = (int) $cache;
        BlacklistRedis::blockedByIdentifierAndUserIdAndBlockUserIdAndValue($identifier , $user_id , $block_user_id , $cache);
        return $cache;
    }

    public static function delByIdentifierAndUserIdAndBlockUserId(string $identifier , int $user_id , int $block_user_id)
    {
        return BlacklistRedis::delByIdentifierAndUserIdAndBlockUserId($identifier , $user_id , $block_user_id);
    }
}