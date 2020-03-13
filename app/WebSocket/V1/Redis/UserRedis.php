<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:43
 */

namespace App\WebSocket\V1\Redis;

use App\WebSocket\V1\Model\UserModel;
use Engine\Facade\Redis as RedisFacade;

class UserRedis extends Redis
{
    // 绑定 user_id 和 客户端连接
    public static function userIdMappingFd(string $identifier , int $user_id , int $fd = null)
    {
        $extranet_ip = config('app.extranet_ip');
        $name = sprintf(self::$userIdMappingFd , $identifier , $user_id);
        if (is_null($fd)) {
            return RedisFacade::setAll($name);
        }
        $data = [
            'extranet_ip'   => $extranet_ip ,
            'client_id'     => $fd
        ];
        return RedisFacade::sAdd($name , json_encode($data) , config('app.timeout'));
    }

    public static function delUserIdMappingFd($identifier , $user_id , int $fd)
    {
        $name = sprintf(self::$userIdMappingFd , $identifier , $user_id);
        return RedisFacade::sRem($name , $fd);
    }


    public static function fdMappingUserId($identifier , $fd , int $user_id = 0)
    {
        $extranet_ip = config('app.extranet_ip');
        $name = sprintf(self::$fdMappingUserId , $identifier , $extranet_ip , $fd);
        if (empty($user_id)) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $user_id , config('app.timeout'));
    }

    public static function delFdMappingUserId($identifier , $fd)
    {
        $extranet_ip = config('app.extranet_ip');
        $name = sprintf(self::$fdMappingUserId , $identifier , $extranet_ip , $fd);
        return RedisFacade::del($name);
    }

    public static function fdMappingPlatform(string $identifier , int $fd , string $platform = '')
    {
        $extranet_ip = config('app.extranet_ip');
        $name = sprintf(self::$fdMappingPlatform , $identifier , $extranet_ip , $fd);
        if (empty($platform)) {
            // 获取
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $platform);
    }

    public static function delFdMappingPlatform(string $identifier , int $fd)
    {
        $extranet_ip = config('app.extranet_ip');
        $name = sprintf(self::$fdMappingPlatform , $identifier , $extranet_ip , $fd);
        return RedisFacade::del($name);
    }

    // 检查用户是否在线
    public static function isOnline(string $identifier = '' , int $user_id = 0): bool
    {
        return !empty(self::userIdMappingFd($identifier , $user_id));
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
            return RedisFacade::string($name , $count);
        }
        $count = RedisFacade::string($name);
        if ($count == false) {
            return 0;
        }
        return intval($count);
    }

    public static function delNumberOfReceptionsForWaiter($identifier , $user_id)
    {
        $name = sprintf(self::$numberOfReceptionsForWaiter , $identifier , $user_id);
        return RedisFacade::del($name);
    }

    // 自动分配客服
    public static function allocateWaiter($identifier)
    {
        // 分配在线客服
        $waiter_ids = UserModel::getIdByIdentifierAndRole($identifier , 'admin');
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
            return (int) RedisFacade::string($name);
        }
        return RedisFacade::string($name , $waiter);
    }

    public static function delGroupBindWaiter(string $identifier , int $group_id)
    {
        $name = sprintf(self::$groupActiveWaiter , $identifier , $group_id);
        return RedisFacade::del($name);
    }

    public static function noWaiterForGroup(string $identifier , int $group_id , bool $get = true)
    {
        $name = sprintf(self::$noWaiterForGroup , $identifier , $group_id);
        if ($get) {
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , '消息并不重要！重要的是这个 key 的存在！表示某个群组已经通知过了！' , config('app.timeout'));
    }

    public static function delNoWaiterForGroup(string $identifier , int $group_id)
    {
        $name = sprintf(self::$noWaiterForGroup , $identifier , $group_id);
        return RedisFacade::del($name);
    }

    // fd 映射的 platform
    public static function userRecentOnlineTimestamp(string $identifier , int $user_id , string $timestamp = '')
    {
        $name = sprintf(self::$userRecentOnlineTimestamp , $identifier , $user_id);
        if (empty($timestamp)) {
            // 获取
            return RedisFacade::string($name);
        }
        return RedisFacade::string($name , $timestamp);
    }

    // 删除 fd 的映射关系
    public static function delUserRecentOnlineTimestamp(string $identifier , int $user_id)
    {
        $name = sprintf(self::$userRecentOnlineTimestamp , $identifier , $user_id);
        return RedisFacade::del($name);
    }


    public static function userByIdentifierAndUserId(string $identifier , int $user_id , $value = null)
    {
        $name = sprintf(self::$user , $identifier , $user_id);
        if (empty($value)) {
            $res = self::string($name);
            return json_decode($res);
        }
        return self::string($name , json_encode($value));
    }

    public static function delUserByIdentifierAndUserId(string $identifier , int $user_id)
    {
        $name = sprintf(self::$user , $identifier , $user_id);
        return RedisFacade::del($name);
    }
}