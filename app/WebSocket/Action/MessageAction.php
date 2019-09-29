<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:34
 */

namespace App\WebSocket\Action;


use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Util\ChatUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use App\WebSocket\Util\UserUtil;
use Core\Lib\Validator;
use function WebSocket\ws_config;

class MessageAction extends Action
{

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

    // 历史记录
    public static function history(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $limit_id  = empty($param['limit_id']) ? 0 : $param['limit_id'];
        $limit     = empty($param['limit']) ? ws_config('app.limit') : $param['limit'];
        $chat_id = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        $res = MessageModel::history($chat_id , $limit_id , $limit);
        foreach ($res as $v)
        {
            MessageUtil::handleMessage($v , $auth->user->id , $param['friend_id']);
        }
        return self::success($res);
    }

    public static function resetUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $chat_id = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        MessageReadStatusModel::updateReadStatusByUserIdAndChatIdExcludeBurn($auth->user->id , $chat_id , 1);
        // 通知用户刷新会话列表
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        return self::success();
    }

}