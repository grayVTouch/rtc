<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/27
 * Time: 11:14
 */

namespace App\Util;


use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupNoticeModel;
use App\Model\ProgramErrorLogModel;
use App\Model\UserOptionModel;
use Exception;
use Push\AppPush;


class AppPushUtil extends Util
{
    public static $module = 'chat';
    
    // 私聊|顶栏推送|单人
    public static function pushForPrivate(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'private' ,
            'data' => $data ,
        ];
        return AppPush::push($user_id , $content , $title , $extra);
    }

    // 群聊|顶栏推送|单人
    public static function pushForGroup(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'group' ,
            'data' => $data ,
        ];
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
        return AppPush::pushAll($user_ids , $content , $title , $extra);
    }

    // 邀请进群|顶栏推送|单人
    public static function pushForInviteGroup(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'invite_into_group' ,
            'data' => $data ,
        ];
        return AppPush::push($user_id , $content , $title , $extra);
    }

    // 个人申请进群|顶栏推送|单人
    public static function pushForAppGroup(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'app_group' ,
            'data' => $data ,
        ];
        return AppPush::push($user_id , $content , $title , $extra);
    }

    // 申请成为好友|顶栏推送|单人
    public static function pushForAppFriend(int $user_id , string $content , string $title = '' , $data = [])
    {
        $extra = [
            'module' => self::$module ,
            'type' => 'app_friend' ,
            'data' => $data ,
        ];
        return AppPush::push($user_id , $content , $title , $extra);
    }

    // 普通通知信息|顶栏推送|单人
    public static function push(int $user_id , string $content , string $title = '')
    {
        return AppPush::push($user_id , $content , $title);
    }

    // 普通通知信息|顶栏推送|单人
    public static function pushAll(array $user_ids , string $content , string $title = '')
    {
        return AppPush::pushAll($user_ids , $content , $title);
    }

    /**
     * 私聊-推送检查
     * @throws \Exception
     */
    public static function pushCheckForFriend(string $platform , int $user_id , int $friend_id , callable $callback)
    {
        if (!config('app.enable_app_push')) {
            return ;
        }
        $deny_platform_for_push = config('business.deny_platform_for_push');
        if (in_array($platform , $deny_platform_for_push)) {
            return ;
        }
        // 检查全局推送是否开启
        $chat_id = ChatUtil::chatId($user_id , $friend_id);
        $user_option = UserOptionModel::findByUserId($friend_id);
        if (empty($user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户信息不完整（请在 rtc_user_option 中为用户新增记录） [friend_id: {$friend_id}]");
            return ;
        }
        if ($user_option->private_notification == 0) {
            return ;
        }
        // 开启了全局推送
        $relation_for_friend = FriendModel::findByUserIdAndFriendId($friend_id , $user_id);
        if (empty($relation_for_friend)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 好友关系不完整（请在 rtc_friend 中为用户新增记录） [chat_id: {$chat_id}; user_id: {$friend_id}; friend_id: {$user_id}]");
            return ;
        }
        if ($relation_for_friend->can_notice != 1) {
            return ;
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
        // 检查全局推送是否开启
        $user_option = UserOptionModel::findByUserId($user_id);
        if (empty($user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户信息不完整（请在 rtc_user_option 中为用户新增记录） [user_id: {$user_id}]");
            return ;
        }
        if ($user_option->group_notification == 0) {
            return ;
        }
        // 开启了全局推送
//        $group_notice = GroupNoticeModel::findByUserIdAndGroupId($user_id , $group_id);
        $member = GroupMemberModel::findByUserIdAndGroupId($user_id , $group_id);
        if (empty($member)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户群信息不完善（请在 rtc_group_member 中为用户新增记录） [group_id: {$group_id}；user_id: {$user_id}]");
            return ;
        }
        if ($member->can_notice != 1) {
            return ;
        }
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
        $user_option = UserOptionModel::findByUserId($user_id);
        if (empty($user_option)) {
            ProgramErrorLogModel::u_insertGetId("Bug: 用户信息不完整（请在 rtc_user_option 中为用户新增记录） [user_id: {$user_id}]");
            return ;
        }
        call_user_func($callback);
    }


}