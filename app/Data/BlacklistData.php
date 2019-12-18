<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/16
 * Time: 18:49
 */

namespace App\Data;


use App\Cache\BlacklistCache;

class BlacklistData extends Data
{
    public static function blockedByIdentifierAndUserIdAndBlockUserId(string $identifier , int $user_id , int $block_user_id)
    {
        return BlacklistCache::blockedByIdentifierAndUserIdAndBlockUserId($identifier , $user_id , $block_user_id);
    }
}