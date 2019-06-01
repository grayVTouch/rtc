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
use App\Model\User;
use App\Util\Misc;
use App\WebSocket\Auth;
use App\WebSocket\Base;
use function core\convert_obj;
use Core\Lib\Validator;
use function core\obj_to_array;
use function core\random;

class MessageAction extends Action
{

    public static function groupHistory(Auth $app , array $param)
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
            $v->session_id = Misc::sessionId('group' , $v->group_id);
            if ($v->user->role == 'admin' && $v->group->is_service == 'y') {
                $v->user->username = '客服 ' . $v->user->username;
                $v->user->nickname = '客服 ' . $v->user->nickname;
            }
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

    public static function groupRecent(Auth $app , array $param)
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
            $v->session_id = Misc::sessionId('group' , $v->group_id);
            if ($v->user->role == 'admin' && $v->group->is_service == 'y') {
                $v->user->username = '客服 ' . $v->user->username;
                $v->user->nickname = '客服 ' . $v->user->nickname;
            }
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

    public static function session(User $user)
    {
        // 群聊
        $joined_group = GroupMember::getByUserId($user->id);
        foreach ($joined_group as $v)
        {
//            var_dump("user_id: {$user->id}; group_id: {$v->id}; role: none");
            $recent_message = GroupMessage::recentMessage($v->group_id , 'none');
            $v->recent_message = empty($recent_message) ? [] : $recent_message;
            if ($user->role == 'user' && $v->group->is_service == 'y') {
                // 用户使用的平台
                $v->group->name = '平台咨询';
            }
            $v->unread = GroupMessageReadStatus::unreadCountByUserIdAndGroupId($user->id , $v->group_id);
            $v->type = 'group';
            $v->session_id = Misc::sessionId('group' , $v->group_id);
        }
        $joined_group = obj_to_array($joined_group);

        // todo 私聊
        $session = array_merge($joined_group);
        usort($session , function($a , $b){
            if ($a['recent_message']['create_time'] == $b['recent_message']['create_time']) {
                return 0;
            }
            return $a['recent_message']['create_time'] > $b['recent_message']['create_time'] ? -1 : 1;
        });
        return self::success($session);
    }

    public static function unreadCount(Auth $app)
    {
        $res = UserAction::util_unreadCount($app->user->id);
        return self::success($res);
    }

    // 设置未读消息数量
    public static function resetGroupUnread(Auth $app , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = GroupMessageReadStatus::updateStatus($app->user->id , $param['group_id'] , 'y');
        // 通知用户刷新会话列表
        $app->push($app->user->id , 'refresh_session');
        return self::success($res);
    }
}