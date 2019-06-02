<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:43
 */

namespace App\Redis;

use App\Model\User;

class UserRedis extends Redis
{
    // 绑定 user_id 和 客户端连接
    public static function fdByUserId(string $identifier , int $user_id , int $fd = null)
    {
        $name = sprintf(self::$fdKey , $identifier , $user_id);
        if (is_null($fd)) {
            $res = redis()->string($name);
            return json_decode($res , true);
        }
        $value = redis()->string($name);
        $value = json_decode($value , true);
        if (empty($value)) {
            $value = [$fd];
        } else {
            $copy = [];
            foreach ($value as $v)
            {
                if ($v == $fd) {
                    continue ;
                }
                $copy[] = $v;
            }
            $copy[] = $fd;
            $value = $copy;
        }
        $value = json_encode($value);
        // 注意我们这个允许多端登录！！
        return redis()->string($name , $value , config('app.timeout'));
    }

    public static function delFdByUserId($identifier , $user_id , int $fd)
    {
        $name = sprintf(self::$fdKey , $identifier , $user_id);
        $res = self::fdByUserId($identifier , $user_id);
        if (empty($res)) {
            return true;
        }
        $result = [];
        foreach ($res as $v)
        {
            if ($v == $fd) {
                continue ;
            }
            $result[] = $v;
        }
        if (empty($result)) {
            return redis()->del($name);
        }
        return redis()->string($name , json_encode($result) , config('app.timeout'));
    }

    // 检查用户是否在线
    public static function isOnline(string $identifier = '' , int $user_id = 0)
    {
        return !empty(self::fdByUserId($identifier , $user_id));
    }

    public static function isAllOnline($identifier , array $user_ids = [])
    {
        foreach ($user_ids as $v)
        {
            if (!self::isOnline($identifier , $v)) {
                return false;
            }
        }
        return true;
    }

    public static function hasOnline($identifier , array $user_ids = [])
    {
        foreach ($user_ids as $v)
        {
            if (self::isOnline($identifier , $v)) {
                return true;
            }
        }
        return false;
    }

    // 服务员目前的接待数量
    public static function numberOfReceptionsForWaiter($identifier , $user_id , int $count = 0)
    {
        $name = sprintf(self::$numberOfReceptionsForWaiter , $identifier , $user_id);
        if (!empty($count)) {
            return redis()->string($name , $count);
        }
        $count = redis()->string($name);
        if ($count == false) {
            return 0;
        }
        return intval($count);
    }

    public static function delNumberOfReceptionsForWaiter($identifier , $user_id)
    {
        $name = sprintf(self::$numberOfReceptionsForWaiter , $identifier , $user_id);
        return redis()->del($name);
    }

    // 自动分配客服
    public static function allocateWaiter($identifier)
    {
        // 分配在线客服
        $waiter_ids = User::getIdByIdentifierAndRole($identifier , 'admin');
        $online = [];
        foreach ($waiter_ids as $v)
        {
            if (UserRedis::isOnline($identifier , $v)) {
                $online[] = [
                    'user_id' => $v ,
                    'loader'  => UserRedis::numberOfReceptionsForWaiter($identifier , $v) ,
                ];
            }
        }
        if (empty($online)) {
            // 没有在线客服，消息保存在待处理的队列中
            return false;
        }
        usort($online , function($a , $b){
            if ($a['loader'] == $b['loader']) {
                return 0;
            }
            return $a['loader'] > $b['loader'] ? 1 : -1;
        });
        $waiter = $online[0];
        if ($waiter['loader'] > config('app.number_of_receptions')) {
            // 超过客服当前接通的最大数量
            return false;
        }
        return $waiter['user_id'];
    }

    // 绑定客服活跃的群组
    public static function groupBindWaiter(string $identifier , int $group_id , int $waiter = 0)
    {
        $name = sprintf(self::$groupActiveWaiter , $identifier , $group_id);
        if (empty($waiter)) {
            return (int) redis()->string($name);
        }
        return redis()->string($name , $waiter);
    }

    public static function delGroupBindWaiter(string $identifier , int $group_id)
    {
        $name = sprintf(self::$groupActiveWaiter , $identifier , $group_id);
        return redis()->del($name);
    }

    public static function fdMappingUserId($identifier , $fd , int $user_id = 0)
    {
        $name = sprintf(self::$fdMappingUserIdKey , $identifier , $fd);
        if (empty($user_id)) {
            return redis()->string($name);
        }
        return redis()->string($name , $user_id , config('app.timeout'));
    }

    public static function delFdMappingUserId($identifier , $fd)
    {
        $name = sprintf(self::$fdMappingUserIdKey , $identifier , $fd);
        return redis()->del($name);
    }

    public static function noWaiterForGroup(string $identifier , int $group_id , bool $get = true)
    {
        $name = sprintf(self::$noWaiterForGroup , $identifier , $group_id);
        if ($get) {
            return redis()->string($name);
        }
        return redis()->string($name , '消息并不重要！重要的是这个 key 的存在！表示某个群组已经通知过了！' , config('app.timeout'));
    }

    public static function delNoWaiterForGroup(string $identifier , int $group_id)
    {
        $name = sprintf(self::$noWaiterForGroup , $identifier , $group_id);
        return redis()->del($name);
    }
}