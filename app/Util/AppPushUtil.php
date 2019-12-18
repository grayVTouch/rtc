<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 11:14
 */

namespace App\Util;


use App\Data\FriendData;
use App\Data\UserData;
use App\Lib\Push\AppPush;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupNoticeModel;
use App\Model\ProgramErrorLogModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use App\Redis\SessionRedis;
use Exception;

class AppPushUtil extends Util
{
    public static $module = 'chat';

    // 私聊|顶栏推送|单人
    public static function pushForPrivate(int $user_id , string $content , string $title = '' , $data = [] , bool $async = true)
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'private' ,
            'data' => $data ,
        ];
        $extra = json_encode($extra);
        if ($async) {
            return self::push($user_id , $content , $title , $extra);
        }
        return AppPush::push($user_id , $content , $title , $extra);
    }

    /**
     *
     * app 推送采用异步任务的方式进行调用
     *
     * @param $callback
     * @param mixed ...$param
     * @return mixed
     */
    public static function taskPush($callback , ...$param)
    {
        return PushUtil::deliveryTask(json_encode([
            'type' => 'callback' ,
            'data' => [
                'callback'  => $callback ,
                'param'     => $param
            ]
        ]));
    }

    // 群聊|顶栏推送|单人
    public static function pushForGroup(int $user_id , string $content , string $title = '' , $data = [] , bool $async = true)
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'group' ,
            'data' => $data ,
        ];
        $extra = json_encode($extra);
        if ($async) {
            return self::push($user_id , $content , $title , $extra);
        }
        return AppPush::push($user_id , $content , $title , $extra);
    }

    // 群聊|顶栏推送|多人
    public static function pushAllForGroup(array $user_ids , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'group' ,
            'data' => $data ,
        ];
        $extra = json_encode($extra);
        return self::taskPush([AppPush::class , 'pushAll'] , $user_ids , $content , $title , $extra);
    }

    // 邀请进群|顶栏推送|单人
    public static function pushForInviteGroup(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'invite_into_group' ,
            'data' => $data ,
        ];
        $extra = json_encode($extra);
        return self::taskPush([AppPush::class , 'push'] , $user_id , $content , $title , $extra);
    }

    // 个人申请进群|顶栏推送|单人
    public static function pushForAppGroup(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'app_group' ,
            'data' => $data ,
        ];
        $extra = json_encode($extra);
        return self::taskPush([AppPush::class , 'push'] , $user_id , $content , $title , $extra);
    }

    // 申请成为好友|顶栏推送|单人
    public static function pushForAppFriend(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'app_friend' ,
            'data' => $data ,
        ];
        $extra = json_encode($extra);
        return self::taskPush([AppPush::class , 'push'] , $user_id , $content , $title , $extra);
    }

    // 普通通知信息|顶栏推送|单人
    public static function push(int $user_id , string $content , string $title = '' , $extra = null)
    {
        return self::taskPush([AppPush::class , 'push'] , $user_id , $content , $title , $extra);
    }

    // 普通通知信息|顶栏推送|单人
    public static function pushAll(array $user_ids , string $content , string $title = '' , $extra = null)
    {
        return self::taskPush([AppPush::class , 'pushAll'] , $user_ids , $content , $title , $extra);
    }

    /**
     * 私聊-推送检查
     * @throws \Exception
     */
    public static function pushCheckForOther(string $identifier , string $platform , int $user_id , int $other_id , callable $callback)
    {
        if (!config('app.enable_app_push')) {
            return ;
        }
        $deny_platform_for_push = config('business.deny_platform_for_push');
        if (in_array($platform , $deny_platform_for_push)) {
            return ;
        }
        // 检查全局推送是否开启
        $chat_id = ChatUtil::chatId($user_id , $other_id);
        $other = UserData::findByIdentifierAndId($identifier , $other_id);
        if (empty($other)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户不存在 [friend_id: {$other_id}]");
            return ;
        }
        $session_id = ChatUtil::sessionId('private' , $chat_id);
        // 检查用户是否在和你的会话中
        if (SessionRedis::existSessionMember($other->identifier , $session_id , $other->id)) {
            return ;
        }
        if (empty($other->user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户信息不完整（请在 rtc_user_option 中为用户新增记录） [friend_id: {$other_id}]");
            return ;
        }
        if ($other->user_option->private_notification == 0) {
            // 用户关闭了私聊消息通知
            return ;
        }
        // 开启了全局推送
        $relation = FriendData::findByIdentifierAndUserIdAndFriendId($identifier , $other_id , $user_id);
        if (!empty($relation)) {
            // 如果是好友的话，那么检查对方是由开启免打扰
//            ProgramErrorLogModel::u_insertGetId("Bug: 好友关系不完整（请在 rtc_friend 中为用户新增记录） [chat_id: {$chat_id}; user_id: {$other_id}; friend_id: {$user_id}]");
            if ($relation->can_notice == 0) {
                return ;
            }
        }
        call_user_func($callback);
    }


    /**
     * 群聊-推送检查
     */
    public static function pushCheckForGroup(string $platform , int $user_id , int $group_id , callable $callback)
    {
        if (!config('app.enable_app_push')) {
            return ;
        }
        $deny_platform_for_push = config('business.deny_platform_for_push');
        if (in_array($platform , $deny_platform_for_push)) {
            return ;
        }
        $user = UserModel::findById($user_id);
        // 检查用户是否在绘画里面
        $session_id = ChatUtil::sessionId('group' , $group_id);
        if (SessionRedis::existSessionMember($user->identifier , $session_id , $user->id)) {
            // 用户在房间里面，不做推送
            return ;
        }
        if (empty($user->user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户选项信息不完善（请在 rtc_user_option 中为用户新增记录） [user_id: {$user_id}]");
            return ;
        }
        if ($user->user_option->group_notification == 0) {
            return ;
        }
        // 开启了全局推送
        $member = GroupMemberModel::findByUserIdAndGroupId($user_id , $group_id);
        if (empty($member)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户不在群里面 [group_id: {$group_id}；user_id: {$user_id}]");
            return ;
        }
        if ($member->can_notice != 1) {
            return ;
        }
        // 检查用户是否在会话里面
        call_user_func($callback);
    }

    /**
     * 用户-推送检查
     */
    public static function pushCheckForUser(string $platform , int $user_id , callable $callback)
    {
        if (!config('app.enable_app_push')) {
            return ;
        }
        $deny_platform_for_push = config('business.deny_platform_for_push');
        if (in_array($platform , $deny_platform_for_push)) {
            return ;
        }
        // 检查全局推送是否开启
        $user = UserModel::findById($user_id);
        if (empty($user)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户不存在 [user_id: {$user_id}]");
            return ;
        }
        if (empty($user->user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户信息不完整（请在 rtc_user_option 中为用户新增记录） [user_id: {$user_id}]");
            return ;
        }
        call_user_func($callback);
    }

    /**
     * ********************
     * 私聊-系统新消息推送检查
     * ********************
     */
    public static function pushCheckWithNewForOther(string $identifier , int $user_id , int $other_id , callable $callback)
    {
        // 检查全局推送是否开启
        $chat_id = ChatUtil::chatId($user_id , $other_id);
        $other = UserModel::findById($other_id);
        if (empty($other)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户不存在 [friend_id: {$other_id}]");
            return ;
        }
        $session_id = ChatUtil::sessionId('private' , $chat_id);
        // 检查用户是否在和你的会话中
        if (SessionRedis::existSessionMember($other->identifier , $session_id , $other->id)) {
            return ;
        }
        if (empty($other->user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户信息不完整（请在 rtc_user_option 中为用户新增记录） [friend_id: {$other_id}]");
            return ;
        }
        if ($other->user_option->private_notification == 0) {
            // 用户关闭了私聊消息通知
            return ;
        }
        // 开启了全局推送
        $relation = FriendData::findByIdentifierAndUserIdAndFriendId($identifier , $other_id , $user_id);
        if (!empty($relation)) {
            if ($relation->can_notice == 0) {
                // 好友，且对方开启免打扰
                return ;
            }
        }
        call_user_func($callback);
    }

    /**
     * ********************
     * 群聊-系统新消息推送检查
     * ********************
     */
    public static function pushCheckWithNewForGroup(int $user_id , int $group_id , callable $callback)
    {
        $user = UserModel::findById($user_id);
        // 检查用户是否在绘画里面
        $session_id = ChatUtil::sessionId('group' , $group_id);
        if (SessionRedis::existSessionMember($user->identifier , $session_id , $user->id)) {
            // 用户在房间里面，不做推送
            return ;
        }
        if (empty($user->user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户选项信息不完善（请在 rtc_user_option 中为用户新增记录） [user_id: {$user_id}]");
            return ;
        }
        if ($user->user_option->group_notification == 0) {
            return ;
        }
        // 开启了全局推送
        $member = GroupMemberModel::findByUserIdAndGroupId($user_id , $group_id);
        if (empty($member)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户不在群里面 [group_id: {$group_id}；user_id: {$user_id}]");
            return ;
        }
        if ($member->can_notice == 0) {
            // 群成员，且开启了消息免打扰
            return ;
        }
        // 检查用户是否在会话里面
        call_user_func($callback);
    }

    /**
     * ********************
     * 用户-系统新消息推送检查
     * ********************
     */
    public static function pushCheckWithNewForUser(int $user_id , callable $callback)
    {
        // 检查全局推送是否开启
        $user = UserModel::findById($user_id);
        if (empty($user)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户不存在 [user_id: {$user_id}]");
            return ;
        }
        if (empty($user->user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户信息不完整（请在 rtc_user_option 中为用户新增记录） [user_id: {$user_id}]");
            return ;
        }
        call_user_func($callback);
    }
}