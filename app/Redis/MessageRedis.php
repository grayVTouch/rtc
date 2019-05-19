<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/16
 * Time: 15:17
 */

namespace App\Redis;


class MessageRedis extends Redis
{
    // 保存消息到未读消息队列中（需要分配服务员处理的消息）
    public static function saveUnhandleMsg($identifier , int $user_id = 0 , array $data = [])
    {
        $name = sprintf(self::$unhandleMsg , $identifier);
        $data = json_encode($data);
        return redis()->hash($name , $user_id , $data , config('app.timeout'));
    }

    // 消费未读消息数量
    public static function consumeUnhandleMsg($identifier , int $limit = 0)
    {
        $name = sprintf(self::$unhandleMsg , $identifier);
        $res = redis()->hashAll($name);
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
        redis()->del($name);
        redis()->hashAll($name , $remaining , config('app.timeout'));
        return $result;
    }
}