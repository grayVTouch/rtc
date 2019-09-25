<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 11:19
 */

namespace App\Util;


use App\WebSocket\Base;

class ChatUtil
{
    /**
     * 生成会话id
     *
     * @param int $sender 发送者id
     * @param int $receiver 接收者id
     * @return string
     */
    public static function chatId(int $sender , int $receiver): string
    {
        $min = min($sender , $receiver);
        $max = max($sender , $receiver);
        return sprintf('%d_%d' , $min , $max);
    }

    /**
     * 私聊消息发送
     */
    public static function send(Base $base , $user_id , $friend_id , $type , $message = '' , $extra = '' , $flag = 'burn')
    {
        try {
            DB::beginTransaction();
            $id = MessageModel::insertGetId(array_unit($param , [
                'user_id' ,
                'chat_id' ,
                'message' ,
                'type' ,
                'flag' ,
            ]));
            MessageReadStatusModel::initByMessageId($id , $param['user_id'] , $param['friend_id']);
            $msg = MessageModel::findById($id);
            MessageUtil::handleMessage($msg , $param['user_id'] , $param['friend_id']);
            DB::commit();
            $user_ids = [$param['user_id'] , $param['friend_id']];
            $auth->sendAll($user_ids , 'private_message' , $msg);
            $auth->pushAll($user_ids , 'refresh_session');
            $auth->pushAll($user_ids , 'refresh_unread_count');
            if (ws_config('app.enable_app_push')) {
                // todo app 推送
            }
            return self::success($msg);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 群聊消息发送
     */
    public static function groupSend()
    {

    }
}