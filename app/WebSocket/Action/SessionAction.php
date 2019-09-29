<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 11:52
 */

namespace App\WebSocket\Action;


use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\MessageModel;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\Util\MiscUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use function core\obj_to_array;

class SessionAction extends Action
{

    public static function session(Auth $auth , array $param)
    {
        $session = [];
        // 群聊
        $group = GroupMemberModel::getByUserId($auth->user->id);
        foreach ($group as $v)
        {
            $recent_message = GroupMessageModel::recentMessage($v->group_id , 'none');
            if (empty($recent_message)) {
                continue ;
            }
            $v->recent_message = $recent_message;
            // 群消息处理
            MessageUtil::handleGroupMessage($v->recent_message);
            if ($auth->user->role == 'user' && $v->group->is_service == 1) {
                // 用户使用的平台
                $v->group->name = '平台咨询';
            }
            $v->unread = GroupMessageReadStatusModel::countByUserIdAndGroupId($auth->user->id , $v->group_id , 0);
            $v->type = 'group';
            // 会话id仅是用于同意管理会话用的
            $v->session_id = MiscUtil::sessionId('group' , $v->group_id);
            // 群成员：只给最多 9 个
            $v->member = GroupMemberModel::getByGroupId($v->group_id , 9);
            $session[] = $v;
        }
        $friend = FriendModel::getByUserId($auth->user->id);
        foreach ($friend as $v)
        {
            $chat_id = ChatUtil::chatId($v->user_id , $v->friend_id);
            $recent_message = MessageModel::recentMessage($chat_id);
            if (empty($recent_message)) {
                continue ;
            }
            // 私聊消息处理
            MessageUtil::handleMessage($recent_message , $v->user_id , $v->friend_id);
            $v->recent_message = $recent_message;
            $v->unread = MessageModel::countByChatIdAndUserIdAndIsRead($chat_id , $v->user_id , 0);
            $v->type = 'private';
            $v->session_id = MiscUtil::sessionId('private' , $chat_id);
            $session[] = $v;
        }
        $session = obj_to_array($session);
        usort($session , function($a , $b){
            if (empty($a['recent_message'])) {
                return 0;
            }
            if ($a['recent_message']['create_time'] == $b['recent_message']['create_time']) {
                return 0;
            }
            return $a['recent_message']['create_time'] > $b['recent_message']['create_time'] ? -1 : 1;
        });
        return self::success($session);
    }
}