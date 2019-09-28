<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:34
 */

namespace App\WebSocket\Action;


use App\Model\MessageModel;
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
        $param['limit'] = empty($param['limit']) ? ws_config('app.limit') : $param['limit'];
        $param['chat_id'] = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        $order = parse_order($param['order']);
        $res = MessageModel::history($param , $order , $param['limit']);
        foreach ($res as $v)
        {
            MessageUtil::handleMessage($v , $auth->user->id , $param['friend_id']);
        }
        return self::success($res);
    }


}