<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 11:52
 */

namespace App\WebSocket\Action;


use App\Data\FriendData;
use App\Data\GroupMemberData;
use App\Model\FriendModel;
use App\Model\GroupMemberModel;
use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\GroupModel;
use App\Model\MessageModel;
use App\Model\MessageReadStatusModel;
use App\Model\PushModel;
use App\Model\SessionModel;
use App\Model\UserModel;
use App\Redis\SessionRedis;
use App\Util\ChatUtil;
use App\Util\GroupMessageUtil;
use App\Util\GroupUtil;
use App\Util\MiscUtil;
use App\Util\PageUtil;
use App\Util\SessionUtil;
use App\Util\UserUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use function core\array_unit;
use Core\Lib\Validator;
use function core\obj_to_array;
use Exception;
use Illuminate\Support\Facades\DB;

class SessionAction extends Action
{

    public static function session(Auth $auth , array $param)
    {
        $top_session = [];
        $general_session = [];
        $sessions = SessionModel::getByUserId($auth->user->id);
        foreach ($sessions as $v)
        {
            if ($v->type == 'private') {
                // 私聊
                $other_id = ChatUtil::otherId($v->target_id , $auth->user->id);
                $v->other = UserModel::findById($other_id);
                UserUtil::handle($v->other , $auth->user->id);
                $recent_message = MessageModel::recentMessage($auth->user->id , $v->target_id);
                MessageUtil::handleMessage($recent_message , $v->user_id , $other_id);
                // 私聊消息处理
                $v->recent_message = $recent_message;
                $v->unread = MessageReadStatusModel::unreadCountByUserIdAndChatId($v->user_id , $v->target_id);
                $v->top = empty($v->other) ? 0 : $v->other->top;
                $v->can_notice = empty($v->other) ? 1 : $v->other->can_notice;
                if ($v->top == 1) {
                    // 置顶群聊
                    $top_session[] = $v;
                    continue ;
                }
                $general_session[] = $v;
                continue ;
            }

            if ($v->type == 'group') {
                // 群聊
                $v->group = GroupModel::findById($v->target_id);
                GroupUtil::handle($v->group , $auth->user->id);
                $recent_message = GroupMessageModel::recentMessage($auth->user->id , $v->target_id , 'none');
                // 群处理
                $v->recent_message = $recent_message;
                // 群消息处理
                MessageUtil::handleGroupMessage($v->recent_message);
                if ($auth->user->role == 'user' && $v->group->is_service == 1) {
                    // 用户使用的平台
                    $v->group->name = '平台咨询';
                }
                $v->unread = GroupMessageReadStatusModel::unreadCountByUserIdAndGroupId($auth->user->id , $v->target_id);
                $v->top = empty($v->group) ? 0 : $v->group->top;
                $v->can_notice = empty($v->group) ? 1 : $v->group->can_notice;
                if ($v->top == 1) {
                    $top_session[] = $v;
                    continue ;
                }
                $general_session[] = $v;
            }

            if ($v->type == 'system') {
                // 公告
                $v->recent = PushModel::recentByUserIdAndType($auth->user->id , 'system');
                // 未读消息数量
                $v->unread = PushModel::unreadCountByUserIdAndTypeAndIsRead($auth->user->id , 'system' , 0);
                if ($v->top == 1) {
                    $top_session[] = $v;
                    continue ;
                }
                $general_session[] = $v;
                continue ;
            }

            // .... 其他类型请另外再增加
        }
        usort($general_session , function($a , $b){
            if (!isset($a->recent_message) || !isset($b->recent_message)) {
                return 0;
            }
            if ($a->recent_message->create_time == $b->recent_message->create_time) {
                return 0;
            }
            return $a->recent_message->create_time > $b->recent_message->create_time ? -1 : 1;
        });
        $res = array_merge($top_session , $general_session);
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
        try {
            DB::beginTransaction();
            // 新增会话
            SessionUtil::createOrUpdate($auth->identifier , $auth->user->id , $param['type'] , $param['target_id']);
            switch ($param['type'])
            {
                case 'private':
                    $other_id = ChatUtil::otherId($param['target_id'] , $auth->user->id);
                    // todo 非好友设置置顶会出现问题
                    FriendData::updateByIdentifierAndUserIdAndFriendIdAndData($auth->identifier , $auth->user->id , $other_id , array_unit($param , [
                        'top'
                    ]));
                    break;
                case 'group':
                    GroupMemberData::updateByIdentifierAndGroupIdAndUserIdAndData($auth->identifier , $param['target_id'] , $auth->user->id , array_unit($param , [
                        'top'
                    ]));
                    break;
            }
            DB::commit();
            // 刷新会话
            $auth->push($auth->user->id , 'refresh_session');
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function createOrUpdate(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'      => 'required' ,
            'target_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $param['user_id'] = $auth->user->id;
        $res = SessionUtil::createOrUpdate($auth->identifier , $auth->user->id , $param['type'] , $param['target_id']);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        $auth->push($auth->user->id , 'refresh_session');
        return self::success();
    }

    // 删除会话
    public static function delete(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'id_list'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $id_list = json_decode($param['id_list'] , true);
        if (empty($id_list)) {
            return self::error('请提供待删除的会话');
        }
        // 检查所有的会话是否包含他人的会话
        if (SessionModel::existOtherByIds($auth->user->id , $id_list)) {
            return self::error('包含他人的会话，禁止操作' , 403);
        }
        try {
            DB::beginTransaction();
            foreach ($id_list as $v)
            {
                $res = SessionUtil::delById($v);
                if ($res['code'] != 200) {
                    DB::rollBack();
                    return self::error($res['data'] , $res['code']);
                }
            }
            DB::commit();
            $auth->push($auth->user->id , 'refresh_session');
            $auth->push($auth->user->id , 'refresh_unread_count');
            $auth->push($auth->user->id , 'refresh_session_unread_count');
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 会话处理
    public static function sessionProcess(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'      => 'required' ,
            'target_id' => 'required' ,
            'status'    => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.session_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的会话类型' . implode(' , ' , $type_range));
        }
        $session_process_status = config('business.session_process_status');
        if (!in_array($param['status'] , $session_process_status)) {
            return self::error('不支持的会话处理状态' . implode(' , ' , $type_range));
        }
        $session_id = ChatUtil::sessionId($param['type'] , $param['target_id']);
        switch ($param['status'])
        {
            case 'join':
                SessionRedis::sessionMember($auth->identifier , $session_id , $auth->user->id);
                break;
            case 'leave':
                SessionRedis::delSessionMember($auth->identifier , $session_id , $auth->user->id);
                break;
        }
        return self::success();
    }

    // 私聊：会话清理（彻底删除消息记录）
    public static function emptyPrivateHistory(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'chat_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = SessionUtil::emptyHistory('private' , $param['chat_id']);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        // 推送
        $other_id = ChatUtil::otherId($param['chat_id'] , $auth->user->id);
        $user_ids = ChatUtil::userIds($param['chat_id']);
        // 发送一个通知
        ChatUtil::send($auth , [
            'user_id' => $auth->user->id ,
            'other_id' => $other_id ,
            'type' => 'notification' ,
            'message' => sprintf('%s：撤回了所有消息' , UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username)) ,
            'old' => 1 ,
        ] , true);
        $auth->pushAll($user_ids , 'sync_private_session' , $param['chat_id']);
        return self::success();
    }

    // 群聊：会话清理（彻底删除消息记录）
    public static function emptyGroupHistory(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        try {
            DB::beginTransaction();
            $group_message = GroupMessageModel::getByGroupIdAndUserId($param['group_id'] , $auth->user->id);
            foreach ($group_message as $v)
            {
                GroupMessageUtil::delete($v->id);
            }
            DB::commit();
            $user_ids = GroupMemberModel::getUserIdByGroupId($param['group_id']);
            // 发送一个通知
            ChatUtil::groupSend($auth , [
                'group_id' => $param['group_id'] ,
                'type' => 'notification' ,
                'user_id' => $auth->user->id ,
                'message' => sprintf('%s：撤回了所有消息' , UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username)) ,
                'old' => 1 ,
            ] , true);
            $auth->pushAll($user_ids , 'sync_group_session' , $param['group_id']);
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 设置会话背景
    public static function setSessionBackground(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'          => 'required' ,
            'target_id'     => 'required' ,
//            'background'    => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $chat_type = config('business.chat_type');
        if (!in_array($param['type'] , $chat_type)) {
            return self::error('不支持的类型，当前受支持的类型有' . implode(' , ' , $chat_type));
        }
        switch ($param['type'])
        {
            case 'private':
                $friend_id = ChatUtil::otherId($param['target_id'] , $auth->user->id);
                FriendData::updateByIdentifierAndUserIdAndFriendIdAndData($auth->identifier , $auth->user->id , $friend_id , [
                    'background' => $param['background']
                ]);
                break;
            case 'group':
                GroupMemberData::updateByIdentifierAndGroupIdAndUserIdAndData($auth->identifier , $param['target_id'] , $auth->user->id , array_unit($param , [
                    'background'
                ]));
                break;
        }
        return self::success();
    }
}