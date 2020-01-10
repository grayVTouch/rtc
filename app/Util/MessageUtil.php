<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/11
 * Time: 17:14
 */

namespace App\Util;


use App\Model\DeleteMessageForPrivateModel;
use App\Model\DeleteMessageModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;

class MessageUtil extends Util
{
    public static function delete(int $message_id)
    {
        $message = MessageModel::findById($message_id);
        // 删除未读消息状态
        MessageReadStatusModel::delByMessageId($message_id);
        // 从删除消息列表中删除指定类型和消息id 的记录
        DeleteMessageForPrivateModel::delByMessageId($message_id);
        // 删除消息
        MessageModel::delById($message_id);
        // todo 新增：内容，删除亚马逊云存储上的文件
        $message_type_for_oss = config('app.message_type_for_oss');
//        print_r($message_type_for_oss);
//        var_dump($message->type);
        if (in_array($message->type , $message_type_for_oss)) {
            $iv = config('app.aes_vi');
            $msg = $message->old < 1 ? AesUtil::decrypt($message->message , $message->aes_key , $iv) : $message->message;
            OssUtil::delAll([$msg]);
        }


    }

    public static function delOssFile($message)
    {
        $iv = config('app.aes_vi');
        $msg = $message->old < 1 ? AesUtil::decrypt($message->message , $message->aes_key , $iv) : $message->message;
        OssUtil::delAll([$msg]);
    }

    // 屏蔽消息
    public static function shield(string $identifier , int $user_id , string $chat_id , int $message_id)
    {
        $count = DeleteMessageForPrivateModel::countByChatIdAndMessageId($chat_id , $message_id);
        if ($count + 1 >= 2) {
            // 计数超过两个，将该消息彻底删除
            self::delete($message_id);
            return ;
        }
        DeleteMessageForPrivateModel::u_insertGetId($identifier , $user_id , $message_id , $chat_id);
    }
}