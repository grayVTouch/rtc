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
use App\Model\TopSessionModel;
use App\Model\UserModel;
use App\Util\ChatUtil;
use App\Util\GroupUtil;
use App\Util\MiscUtil;
use App\Util\UserUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use function core\array_unit;
use Core\Lib\Validator;
use function core\obj_to_array;

class SessionAction extends Action
{

    public static function session(Auth $auth , array $param)
    {
        $top_session = [];
        $session = [];
        // 群聊
        $group = GroupMemberModel::getByUserId($auth->user->id);
        foreach ($group as $v)
        {
            $recent_message = GroupMessageModel::recentMessage($auth->user->id , $v->group_id , 'none');
            if (empty($recent_message)) {
                continue ;
            }
            // 群处理
            GroupUtil::handle($v->group);
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
            $top = TopSessionModel::findByUserIdAndTypeAndTargetIdAndTop($auth->user->id , 'group' , $v->group_id , 1);
            if (!empty($top)) {
                $v->top = $top;
                $top_session[] = $v;
                continue ;
            }
            $session[] = $v;
        }
        $friend = FriendModel::getByUserId($auth->user->id);
//        print_r($friend);
        foreach ($friend as $v)
        {
            $chat_id = ChatUtil::chatId($v->user_id , $v->friend_id);
            $recent_message = MessageModel::recentMessage($auth->user->id , $chat_id);
            print_r($recent_message);
            var_dump($recent_message);
            if (empty($recent_message)) {
                continue ;
            }
            UserUtil::handle($v->user);
            UserUtil::handle($v->friend);
            // 私聊消息处理
            MessageUtil::handleMessage($recent_message , $v->user_id , $v->friend_id);
            $v->recent_message = $recent_message;
            $v->unread = MessageModel::countByChatIdAndUserIdAndIsRead($chat_id , $v->user_id , 0);
            $v->type = 'private';
            $v->session_id = MiscUtil::sessionId('private' , $chat_id);
            $top = TopSessionModel::findByUserIdAndTypeAndTargetIdAndTop($auth->user->id , 'private' , $chat_id , 1);
            if (!empty($top)) {
                $v->top = $top;
                $top_session[] = $v;
                continue ;
            }
            $session[] = $v;
        }

        // 置顶会话排序
        $top_session = obj_to_array($top_session);
        usort($top_session , function($a , $b){
            if ($a['top']['update_time'] == $b['top']['update_time']) {
                return 0;
            }
            return $a['top']['update_time'] > $b['top']['update_time'] ? -1 : 1;
        });
        // 非置顶会话排序
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
        $res = array_merge($top_session , $session);
        return self::success($res);
    }

    public static function top(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'      => 'required' ,
            'top'       => 'required' ,
            'target_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $exist = TopSessionModel::exist($auth->user->id , $param['type'] , $param['target_id']);
        if ($exist) {
            // 更改类型
            TopSessionModel::updateByUserIdAndTypeAndTargetId($auth->user->id , $param['type'] , $param['target_id'] , $param['top']);
        } else {
            $param['user_id'] = $auth->user->id;
            $id = TopSessionModel::insertGetId(array_unit($param , [
                'user_id' ,
                'type' ,
                'target_id' ,
                'top' ,
            ]));
            // var_dump($id);
        }
        // 刷新会话
        $auth->push($auth->user->id , 'refresh_session');
        return self::success();
    }
}