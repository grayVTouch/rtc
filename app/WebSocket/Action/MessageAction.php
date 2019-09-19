<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:34
 */

namespace App\WebSocket\Action;

use App\Model\Group;
use App\Model\GroupMember;
use App\Model\GroupMessage;
use App\Model\GroupMessageReadStatus;
use App\Model\UserModel;
use App\Util\MiscUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use App\WebSocket\Util\UserUtil;
use function core\convert_obj;
use Core\Lib\Validator;
use function core\obj_to_array;

class MessageAction extends Action
{

    public static function groupHistory(Auth $auth , array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = Group::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到 group_id = ' . $param['group_id'] . ' 对应群信息' , 404);
        }
        $res = GroupMessage::history($group->id , $param['group_message_id'] , config('app.limit'));
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v);
        }
        $res = obj_to_array($res);
        usort($res , function($a , $b){
            if ($a['id'] == $b['id']) {
                return 0;
            }
            return $a['id'] > $b['id'] ? 1 : -1;
        });
        return self::success($res);
    }

    public static function groupRecent(Auth $auth , array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = Group::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到 group_id = ' . $param['group_id'] . ' 对应群信息' , 404);
        }
        $res = GroupMessage::history($group->id , 0 , config('app.limit'));
        // 消息模型关联
        $res = convert_obj($res);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v);
        }
        $res = obj_to_array($res);
        usort($res , function($a , $b){
            if ($a['id'] == $b['id']) {
                return 0;
            }
            return $a['id'] > $b['id'] ? 1 : -1;
        });
        return self::success($res);
    }

    public static function session(UserModel $user)
    {
        // 群聊
        $group = GroupMember::getByUserId($user->id);
        foreach ($group as $v)
        {
            $recent_message = GroupMessage::recentMessage($v->group_id , 'none');
            $v->recent_message = empty($recent_message) ? [] : $recent_message;
            if ($user->role == 'user' && $v->group->is_service == 1) {
                // 用户使用的平台
                $v->group->name = '平台咨询';
            }
            $v->unread = GroupMessageReadStatus::unreadCountByUserIdAndGroupId($user->id , $v->group_id);
            $v->type = 'group';
            $v->session_id = MiscUtil::sessionId('group' , $v->group_id);
        }
        $group = obj_to_array($group);

        // todo 私聊
        $session = array_merge($group);
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

    // 未读通信数量（私聊 + 群聊）
    public static function unreadCountForCommunication(Auth $auth , array $param)
    {
        $res = UserUtil::unreadCountForCommunication($auth->user->id);
        return self::success($res);
    }

    // 未读推送数量
    public static function unreadCountForPush(Auth $auth , array $param)
    {
        $res = UserUtil::unreadCountForPush($auth->user->id);
        return self::success($res);
    }

    // 总：未读消息数量
    public static function unreadCount(Auth $auth , array $param)
    {
        $res = UserUtil::unreadCount($auth->user->id);
        return self::success($res);
    }

    // 设置未读消息数量
    public static function resetGroupUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = GroupMessageReadStatus::updateStatus($auth->user->id , $param['group_id'] , 1);
        // 通知用户刷新会话列表
        $auth->push($auth->user->id , 'refresh_session');
        return self::success($res);
    }
}