<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/20
 * Time: 10:52
 */

namespace App\WebSocket\V1\Action;

use App\WebSocket\V1\Data\GroupMemberData;
use App\WebSocket\V1\Data\GroupMessageReadStatusData;
use App\WebSocket\V1\Model\DeleteMessageForGroupModel;
use App\WebSocket\V1\Model\DeleteMessageModel;
use App\WebSocket\V1\Model\FriendModel;
use App\WebSocket\V1\Model\GroupModel;
use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Model\GroupMessageModel;
use App\WebSocket\V1\Model\GroupMessageReadStatusModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Util\AesUtil;
use App\WebSocket\V1\Util\ChatUtil;
use App\WebSocket\V1\Util\GroupUtil;
use App\WebSocket\V1\Util\MiscUtil;
use App\WebSocket\V1\Util\OssUtil;
use App\WebSocket\V1\Util\UserUtil;
use App\WebSocket\V1\Controller\Auth;
use function core\array_unit;
use Core\Lib\Validator;
use App\WebSocket\V1\Util\MessageUtil;


use function core\convert_obj;
use function core\obj_to_array;
use Exception;
use Illuminate\Support\Facades\DB;



class GroupMessageAction extends Action
{

    public static function history(Auth $auth , array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群对应信息' , 404);
        }
        $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $group->id , $auth->user->id);
        if (empty($member)) {
            return self::error('您不是该群的成员，禁止操作' , 403);
        }
        $limit_id = empty($param['limit_id']) ? 0 : $param['limit_id'];
        $limit = empty($param['limit']) ? config('app.limit') : $param['limit'];
        $res = GroupMessageModel::history($auth->user->id , $group->id , $member->create_time ,  $limit_id , $limit);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v , $auth->user->id);
            GroupUtil::handle($v->group , $auth->user->id);
            UserUtil::handle($v->user , $auth->user->id);
            $member_for_message = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $group->id , $v->user_id);
            if (!empty($member_for_message)) {
                $v->user = $v->user ?? new class() {};
                $v->user->nickname = empty($member_for_message->alias) ? $v->user->nickname : $member_for_message->alias;
            }
        }
        return self::success($res);
    }

    public static function lastest(Auth $auth , array $param)
    {
        // 获取群聊数据
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群对应信息' , 404);
        }
        $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $group->id , $auth->user->id);
        if (empty($member)) {
            return self::error('您不是该群的成员，禁止操作' , 403);
        }
        $limit_id = empty($param['limit_id']) ? 0 : $param['limit_id'];
        $res = GroupMessageModel::lastest($auth->user->id , $group->id , $member->create_time , $limit_id);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v , $auth->user->id);
            GroupUtil::handle($v->group , $auth->user->id);
            UserUtil::handle($v->user , $auth->user->id);
            $member_for_message = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $group->id , $v->user_id);
            if (!empty($member_for_message)) {
                $v->user = $v->user ?? new class() {};
                $v->user->nickname = empty($member_for_message->alias) ? $v->user->nickname : $member_for_message->alias;
            }
        }
        return self::success($res);
    }

    // 设置未读消息数量
    public static function resetUnread(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        try {
            $res = GroupMessageReadStatusModel::unreadByUserIdAndGroupIdExcludeVoice($auth->user->id , $param['group_id']);
            foreach ($res as $v)
            {
                if (!empty(GroupMessageReadStatusModel::findByUserIdAndGroupMessageId($auth->user->id , $v->id))) {
                    continue ;
                }
                GroupMessageReadStatusData::insertGetId($auth->identifier , $auth->user->id , $v->id ,  $v->group_id , 1);
            }
            // 通知用户刷新会话列表
            $auth->push($auth->user->id , 'refresh_session');
            $auth->push($auth->user->id , 'refresh_unread_count');
            $auth->push($auth->user->id , 'refresh_session_unread_count');
            return self::success();
        } catch(Exception $e) {
            throw $e;
        }
    }

    // 设置单条消息为已读
    public static function readed(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $message = GroupMessageModel::findById($param['group_message_id']);
        if (empty($message)) {
            return self::error('未找到消息id对应的记录' , 404);
        }
//        if ($message->user_id != $auth->user->id) {
//            return self::error('您无法更改他人得消息读取状态' , 403);
//        }
        $res = GroupMessageReadStatusModel::findByUserIdAndGroupMessageId($auth->user->id , $message->id);
        if (!empty($res)) {
            return self::success('操作失败！该条消息已经是已读状态');
        }
        GroupMessageReadStatusData::insertGetId($auth->identifier , $auth->user->id , $message->id , $message->group_id ,1);
        $auth->push($auth->user->id , 'refresh_session');
        $auth->push($auth->user->id , 'refresh_unread_count');
        $auth->push($auth->user->id , 'refresh_session_unread_count');
        return self::success();
    }

    // 删除消息记录
    public static function delete(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group_message_id = json_decode($param['group_message_id'] , true);
        if (empty($group_message_id)) {
            return self::error('请提供待删除的消息');
        }
        try {
            DB::beginTransaction();
            foreach ($group_message_id as $v)
            {
                $group_message = GroupMessageModel::findById($v);
                if (empty($group_message)) {
                    DB::rollBack();
                    return self::error('包含不存在的消息' , 404);
                }
                DeleteMessageForGroupModel::u_insertGetId($auth->identifier , $auth->user->id , $v , $group_message->group_id);
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

    // 消息撤回
    public static function withdraw(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_message_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = GroupMessageModel::findById($param['group_message_id']);
        if (empty($res)) {
            return self::error('未找到对应的消息' , 404);
        }
        $deny_withdraw_message_type = config('business.deny_withdraw_message_type');
        if (in_array($res->type , $deny_withdraw_message_type)) {
            return self::error('该消息类型不支持撤回' , 403);
        }
        if ($res->user_id != $auth->user->id) {
            return self::error('您无权限撤回他人消息' , 403);
        }
//        $withdraw_duration = config('app.withdraw_duration');
//        if ($withdraw_duration < time() - strtotime($res->create_time)) {
//            return self::error(sprintf('超过%s秒，不允许操作' , $withdraw_duration) , 403);
//        }
        $res_type_range = config('business.res_type_for_message');
        if (in_array($res->type , $res_type_range)) {
            // 删除 oss 上存放的资源
            $aes_vi = config('app.aes_vi');
            $oss_file = $res->old < 1 ? AesUtil::decrypt($res->message , $res->aes_key , $aes_vi) : $res->message;
            $del_res = OssUtil::delAll([$oss_file]);
            if ($del_res['code'] != 0) {
                return self::error('消息撤回失败：删除 oss 对应的资源文件失败' , 500);
            }
        }
        $message = sprintf('"%s" 撤回了消息' , $res->user->nickname);
        GroupMessageModel::updateById($param['group_message_id'] , [
            'type' => 'withdraw' ,
            'message' => $res->old == 1 ?
                $message :
                AesUtil::encrypt($message , $res->aes_key , config('app.aes_vi')) ,
        ]);
        $res = GroupMessageModel::findById($param['group_message_id']);
        MessageUtil::handleGroupMessage($res , $auth->user->id);
        $user_ids = GroupMemberModel::getUserIdByGroupId($res->group_id);
        $auth->pushAll($user_ids , 'refresh_session');
        $auth->sendAll($user_ids , 'refresh_group_message' , $res);
        return self::success($res);
    }


    // 逐条转发
    public static function serialForward(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'       => 'required' ,
            'message_id' => 'required' ,
            'target_id'  => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.forward_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的转发类型，当前受支持的转发类型：' . implode(',' , $type_range));
        }
        if ($param['type'] == 'private') {
            // 转发到私聊群里面
            $friend = UserModel::findById($param['target_id']);
            if (empty($friend)) {
                return self::error('未找到用户信息' , 404);
            }
            if (!FriendModel::isFriend($auth->user->id , $friend->id)) {
                return self::error('你们还不是好友' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $res = [
                // 失败转发数量
                'error'     => [] ,
                // 成功转发数量
                'success'   => 0
            ];
            foreach ($msgs as $v)
            {
                $forward = ChatUtil::send($auth , [
                    'user_id' => $auth->user->id ,
                    'other_id' => $friend->id ,
                    'type' => $v->type ,
                    'message' => $v->message ,
                    'extra' => $v->extra ,
                    'old' => $v->old ,
                    'aes_key' => $v->aes_key ,
                ] , true);
                if ($forward['code'] != 0) {
                    $res['error'][] = [
                        'code' => $forward['code'] ,
                        'data' => $forward['data'] ,
                    ];
                    continue ;
                }
                $res['success']++;
            }
            return self::success($res);
            // 转发到私聊群
        } else if ($param['type'] == 'group') {
            // 转发到群聊群
            // 转发到私聊群里面
            $group = GroupModel::findById($param['target_id']);
            if (empty($group)) {
                return self::error('未找到群组信息' , 404);
            }
            if (!GroupMemberModel::exist($auth->user->id , $group->id)) {
                return self::error('您还不在这个群里面' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $res = [
                // 失败转发数量
                'error'     => [] ,
                // 成功转发数量
                'success'   => 0
            ];
            foreach ($msgs as $v)
            {
                $forward = ChatUtil::groupSend($auth , [
                    'user_id' => $auth->user->id ,
                    'group_id' => $group->id ,
                    'type' => $v->type ,
                    'message' => $v->message ,
                    'extra' => $v->extra ,
                    'old' => $v->old ,
                    'aes_key' => $v->aes_key ,
                ] , true);
                if ($forward['code'] != 0) {
                    $res['error'][] = [
                        'code' => $forward['code'] ,
                        'data' => $forward['data'] ,
                    ];
                    continue ;
                }
                $res['success']++;
            }
            return self::success($res);
        } else {
            // 待扩充
        }


    }

    // 消息合并转发
    public static function mergeForward(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type'       => 'required' ,
            'message_id' => 'required' ,
            'target_id'  => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $type_range = config('business.forward_type');
        if (!in_array($param['type'] , $type_range)) {
            return self::error('不支持的转发类型，当前受支持的转发类型：' . implode(',' , $type_range));
        }
        if ($param['type'] == 'private') {
            // 转发到私聊群里面
            $friend = UserModel::findById($param['target_id']);
            if (empty($friend)) {
                return self::error('未找到用户信息' , 404);
            }
            if (!FriendModel::isFriend($auth->user->id , $friend->id)) {
                return self::error('你们还不是好友' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $message = json_encode($message_id);
            $old = 1;
            if (config('app.enable_encrypt')) {
                $old = 0;
                $message =  AesUtil::encrypt($message , $auth->user->aes_key , config('app.aes_vi'));
            }
            $res = ChatUtil::send($auth , [
                'user_id' => $auth->user->id ,
                'other_id' => $friend->id ,
                'type' => 'message_set' ,
                'message' => $message ,
                'extra' => 'group' ,
                'old'   => $old ,
            ] , true);
            if ($res['code'] != 0) {
                return self::error('合并转发失败：' . $res['data'] , $res['code']);
            }
            return self::success();
            // 转发到私聊群
        } else if ($param['type'] == 'group') {
            // 转发到群聊群
            // 转发到私聊群里面
            $group = GroupModel::findById($param['target_id']);
            if (empty($group)) {
                return self::error('未找到群组信息' , 404);
            }
            if (!GroupMemberModel::exist($auth->user->id , $group->id)) {
                return self::error('您还不在这个群里面' , 403);
            }
            $message_id = json_decode($param['message_id'] , true);
            if (empty($message_id)) {
                return self::error('请选择要转发的消息');
            }
            $deny_forward_message_type = config('business.deny_forward_message_type');
            $msgs = [];
            foreach ($message_id as $v)
            {
                $msg = GroupMessageModel::findById($v);
                if (in_array($msg->type , $deny_forward_message_type)) {
                    return self::error('包含不支持转发的消息类型' , 403);
                }
                $msgs[] = $msg;
            }
            $message = json_encode($message_id);
            $old = 1;
            if (config('app.enable_encrypt')) {
                $old = 0;
                $message =  AesUtil::encrypt($message , $auth->user->aes_key , config('app.aes_vi'));
            }
            $res = ChatUtil::groupSend($auth , [
                'user_id' => $auth->user->id ,
                'group_id' => $group->id ,
                'type' => 'message_set' ,
                'message' => $message ,
                'extra' => 'group' ,
                'old'   => $old
            ] , true);
            if ($res['code'] != 0) {
                return self::error('合并转发失败：' . $res['data'] , $res['code']);
            }
            return self::success();
        } else {
            // 待扩充
        }
    }

    public static function sync(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'id_list' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $id_list = json_decode($param['id_list'] , true);
        $res = GroupMessageModel::getByUserIdAndIdsExcludeDeleted($auth->user->id , $id_list);
        foreach ($res as $v)
        {
            MessageUtil::handleGroupMessage($v);
            GroupUtil::handle($v->group , $auth->user->id);
            UserUtil::handle($v->user , $auth->user->id);
            $member = GroupMemberData::findByIdentifierAndGroupIdAndUserId($auth->identifier , $v->group_id , $v->user_id);
            if (!empty($member)) {
                $v->user = $v->user ?? new class() {};
                $v->user->nickname = empty($member->alias) ? $v->user->nickname : $member->alias;
            }
        }
        return self::success($res);
    }
}