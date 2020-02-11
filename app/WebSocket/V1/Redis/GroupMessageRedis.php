<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/12/12
 * Time: 15:01
 */

namespace App\WebSocket\V1\Redis;

use Engine\Facade\Redis as RedisFacade;

class GroupMessageRedis extends Redis
{
    public static function groupMessageReadStatus(string $identifier , int $user_id , int $group_message_id , $is_read = null)
    {
        $name = sprintf(self::$groupMessageReadStatus , $identifier , $user_id , $group_message_id);
        if (is_null($is_read)) {
            // 仅获取数据
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $is_read);
    }

    public static function unreadForGroup(string $identifier , int $user_id , int $group_id , $unread = null)
    {
        $name = sprintf(self::$unreadForGroup , $identifier , $user_id , $group_id);
        if (is_null($unread)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $unread);
    }

    public static function incrUnreadForGroup(string $identifier , int $user_id , int $group_id , int $step = 1)
    {
        $unread = (int) self::unreadForGroup($identifier , $user_id , $group_id);
        $unread += $step;
        return self::unreadForGroup($identifier , $user_id , $group_id , $unread);
    }

    public static function decrUnreadForGroup(string $identifier , int $user_id , int $group_id , int $step = 1)
    {
        $unread = (int) self::unreadForGroup($identifier , $user_id , $group_id);
        $unread -= $step;
        $unread = min(0 , $unread);
        return self::unreadForGroup($identifier , $user_id , $group_id , $unread);
    }
}