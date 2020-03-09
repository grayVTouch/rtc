<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/10/17
 * Time: 10:26
 */

namespace App\WebSocket\V1\Util;


use App\WebSocket\V1\Data\GroupData;
use App\WebSocket\V1\Data\GroupMemberData;
use App\WebSocket\V1\Model\DeleteMessageForGroupModel;
use App\WebSocket\V1\Model\DeleteMessageForPrivateModel;
use App\WebSocket\V1\Model\DeleteMessageModel;
use App\WebSocket\V1\Model\GroupMessageModel;
use App\WebSocket\V1\Model\GroupMessageReadStatusModel;
use App\WebSocket\V1\Model\MessageModel;
use App\WebSocket\V1\Model\MessageReadStatusModel;
use App\WebSocket\V1\Model\SessionModel;
use App\WebSocket\V1\Model\UserModel;
use function core\array_unit;

class SessionUtil extends Util
{
    // 创建 或 更新会话
    public static function createOrUpdate(string $identifier , int $user_id , string $type , $target_id = '')
    {
        // 检查 type 是否正确
        $type_range = config('business.session_type');
        if (!in_array($type , $type_range)) {
            return self::error('不支持的 type，当前受支持的 type 有' . implode(' , ' , $type_range));
        }
        switch ($type)
        {
            case 'private':
                $user_ids = ChatUtil::userIds($target_id);
                $users = UserModel::getByIds($user_ids);
                if (count($users) != 2) {
                    return self::error('创建会话失败！存在不存在的用户' , 404);
                }
                break;
            case 'group':
                $group = GroupData::findByIdentifierAndId($identifier , $target_id);
                if (empty($group)) {
                    return self::error('创建会话失败！群不存在' , 404);
                }
                $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($identifier , $target_id  , $user_id);
                if (empty($member)) {
                    return self::error('创建会话失败！您不是群成员' , 403);
                }
                break;
            case 'system':
                break;
            default:
                return self::error('不支持的会话类型');
        }
        $session_id = '';
        if ($type == 'system') {
            $session = SessionModel::findByUserIdAndType($user_id , $type);
        } else {
            $session_id = ChatUtil::sessionId($type , $target_id);
            $session = SessionModel::findByUserIdAndTypeAndTargetId($user_id , $type , $target_id);
        }
        // 检查会话是否存在
        if (empty($session)) {
            $id = SessionModel::insertGetId([
                'identifier' => $identifier ,
                'user_id'   => $user_id ,
                'type'      => $type ,
                'target_id' => $target_id ,
                'session_id' => $session_id ,
            ]);
        } else {
            SessionModel::updateById($session->id , [
                'update_time' => date('Y-m-d H:i:s') ,
            ]);
        }
        return self::success();
    }

    // 删除会话
    public static function delById(int $session_id)
    {
        $session = SessionModel::findById($session_id);
        if (empty($session)) {
            return self::error('会话不存在' , 404);
        }
        SessionModel::delById($session_id);
        if ($session->type == 'private') {
            $mesages = MessageModel::getByChatId($session->target_id);
            foreach ($mesages as $v)
            {
                MessageUtil::shield($session->identifier , $session->user_id , $session->target_id , $v->id);
            }
            return self::success();
        }
        if ($session->type == 'group') {
            $mesages = GroupMessageModel::getByGroupId($session->target_id);
            foreach ($mesages as $v)
            {
                GroupMessageUtil::shield($session->identifier , $session->user_id , $session->target_id , $v->id);
            }
        }
        return self::success();
    }

    public static function delByUserIdAndTypeAndTargetId(int $user_id , string $type , $target_id)
    {
        $session = SessionModel::findByUserIdAndTypeAndTargetId($user_id , $type , $target_id);
        if (empty($session)) {
            return self::error('未找到用户会话信息' , 404);
        }
        self::delById($session->id);
        return self::success();
    }

    // 删除会话
    public static function emptyHistory(string $type , $target_id)
    {
        $type_range = ['private' , 'group'];
        if (!in_array($type , $type_range)) {
            return self::error('' , 403);
        }
        if ($type == 'private') {
            // 删除屏蔽的消息
//            DeleteMessageForPrivateModel::delByChatId($target_id);
            // 删除未读状态
//            MessageReadStatusModel::delByChatId($target_id);
            // 删除私聊消息
//            MessageModel::delByChatId($target_id);
            $res = MessageModel::getByChatId($target_id);
            foreach ($res as $v)
            {
                MessageUtil::delete($v->id);
            }
            return self::success();
        }
        $res = GroupMessageModel::getByGroupId($target_id);
        foreach ($res as $v)
        {
            GroupMessageUtil::delete($v->id);
        }
        // 群聊
//        DeleteMessageForGroupModel::delByGroupId($target_id);
//        GroupMessageReadStatusModel::delByGroupId($target_id);
//        GroupMessageModel::delByGroupId($target_id);
        return self::success();
    }

    //
    public static function emptyGroupHistory(string $type , $target_id)
    {
        $type_range = ['private' , 'group'];
        if (!in_array($type , $type_range)) {
            return self::error('' , 403);
        }
        if ($type == 'private') {
            // 删除屏蔽的消息
            DeleteMessageForPrivateModel::delByChatId($target_id);
            // 删除未读状态
            MessageReadStatusModel::delByChatId($target_id);
            // 删除私聊消息
            MessageModel::delByChatId($target_id);
            return self::success();
        }
        // 群聊
        DeleteMessageForGroupModel::delByGroupId($target_id);
        GroupMessageReadStatusModel::delByGroupId($target_id);
        GroupMessageModel::delByGroupId($target_id);
        return self::success();
    }
}