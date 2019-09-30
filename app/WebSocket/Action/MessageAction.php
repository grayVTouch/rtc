<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:34
 */

namespace App\WebSocket\Action;


use App\Model\DeleteMessageModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Util\ChatUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use App\WebSocket\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use function WebSocket\ws_config;

class MessageAction extends Action
{
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
        $res = MessageModel::history($auth->user->id , $chat_id , $limit_id , $limit);
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

    // 删除消息记录
    public static function delete(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $message_id = json_decode($param['message_id'] , true);
        if (empty($message_id)) {
            return self::error('请提供待删除的消息');
        }
        try {
            DB::beginTransaction();
            foreach ($message_id as $v)
            {
                $data = [
                    'user_id'   => $auth->user->id ,
                    'type'      => 'private' ,
                    'message_id' => $v
                ];
                DeleteMessageModel::insert($data);
            }
            DB::commit();
            $auth->push($auth->user->id , 'refresh_session');
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



}