<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 15:17
 */

namespace App\WebSocket\V1\Redis;

use Engine\Facade\Redis as RedisFacade;

class MessageRedis extends Redis
{
    // 保存消息到未读消息队列中（需要分配服务员处理的消息）
    public static function saveUnhandleMsg($identifier , int $user_id = 0 , array $data = [])
    {
        $name = sprintf(self::$unhandleMsg , $identifier , $identifier);
        $data = json_encode($data);
        return RedisFacade::hash($name , $user_id , $data , config('app.timeout'));
    }

    // 消费未读消息数量
    public static function consumeUnhandleMsg($identifier , int $limit = 0)
    {
        $name = sprintf(self::$unhandleMsg , $identifier , $identifier);
        $res = RedisFacade::hashAll($name);
        if (empty($res)) {
            return [];
        }
        $count = config('app.number_of_receptions');
        $max = min($count , count($res));
        if (!empty($limit)) {
            $max = min($count , $max);
        }
        $result = [];
        $remaining = [];
        $counter = 1;
        foreach ($res as $k => $v)
        {
            if ($counter++ <= $max) {
                $result[] = json_decode($v , true);
                continue ;
            }
            $remaining[$k] = $v;
        }
        // 删除掉旧 key
        RedisFacade::del($name);
        RedisFacade::hashAll($name , $remaining , config('app.timeout'));
        return $result;
    }

    public static function messageReadStatus(string $identifier , int $user_id , int $message_id , $is_read = null)
    {
        $name = sprintf(self::$messageReadStatus , $identifier , $user_id , $message_id);
        if (is_null($is_read)) {
            // 仅获取数据
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $is_read);
    }

    public static function unreadForPrivate(string $identifier , int $user_id , int $other_id , $unread = null)
    {
        $name = sprintf(self::$unreadForPrivate , $identifier , $user_id , $other_id);
        if (is_null($unread)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $unread);
    }

    public static function incrUnreadForPrivate(string $identifier , int $user_id , int $other_id , int $step = 1)
    {
        $unread = (int) self::unreadForPrivate($identifier , $user_id , $other_id);
        $unread += $step;
        return self::unreadForPrivate($identifier , $user_id , $other_id , $unread);
    }

    public static function decrUnreadForPrivate(string $identifier , int $user_id , int $other_id , int $step = 1)
    {
        $unread = (int) self::unreadForPrivate($identifier , $user_id , $other_id);
        $unread -= $step;
        $unread = min(0 , $unread);
        return self::unreadForPrivate($identifier , $user_id , $other_id , $unread);
    }
}