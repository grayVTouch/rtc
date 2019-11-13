<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 17:21
 */

namespace App\WebSocket\Action;


use App\Model\GroupMessageModel;
use App\Model\GroupMessageReadStatusModel;
use App\Model\GroupModel;
use App\Model\GroupMemberModel;
use App\Model\UserModel;
use App\Util\AppPushUtil;
use App\Util\ChatUtil;
use App\Util\GroupUtil;
use App\Util\PushUtil;
use App\Util\SessionUtil;
use App\Util\UserUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use function core\has_repeat_in_array;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use App\Model\ApplicationModel;
use Exception;
use function core\array_unit;
use function extra\check_datetime;
use Illuminate\Support\Facades\DB;


class GroupAction extends Action
{

    public static function appJoinGroup(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到对应群信息' , 404);
        }
        // 检查是否已经在群里面
        if (GroupMemberModel::exist($auth->user->id , $param['group_id'])) {
            return self::success('你已经是群成员' , 403);
        }
        $param['type']      = 'group';
        $param['op_type']   = 'app_group';
        $param['user_id']   = $group->user_id;
        $param['relation_user_id'] = json_encode([$auth->user->id]);
        $param['log']    = sprintf('"%s" 申请进群' , $auth->user->nickname);
        if ($group->auth == 1) {
            // 开启了进群认证
            $param['status']    = 'wait';
            $id = ApplicationModel::insertGetId(array_unit($param , [
                'type' ,
                'op_type' ,
                'user_id' ,
                'group_id' ,
                'relation_user_id' ,
                'status' ,
                'remark' ,
                'log' ,
            ]));
            $auth->push($group->user_id , 'refresh_application');
            $auth->push($group->user_id , 'refresh_unread_count');
            $auth->push($group->user_id , 'refresh_app_unread_count');

            AppPushUtil::pushCheckForUser($auth->platform , $group->user_id , function() use($param){
                AppPushUtil::pushForAppGroup($param['user_id'] , $param['log'] , '申请进群');
            });
            AppPushUtil::pushCheckWithNewForUser($group->user_id , function() use($param , $auth){
                $auth->push($param['user_id'] , 'new');
            });
            return self::success($id);
        }
        // 未开启进群认证
        try {
            DB::beginTransaction();
            $param['status']    = 'auto_approve';
            $id = ApplicationModel::insertGetId(array_unit($param , [
                'type' ,
                'op_type' ,
                'user_id' ,
                'group_id' ,
                'relation_user_id' ,
                'status' ,
                'remark' ,
                'log' ,
            ]));
            // 未开启进群认证
            GroupMemberModel::u_insertGetId($auth->user->id , $group->id);
            $user_ids = GroupMemberModel::getUserIdByGroupId($group->id);
            DB::commit();
            $auth->pushAll($user_ids , 'refresh_group');
            $auth->pushAll($user_ids , 'refresh_application');
            $auth->pushAll($user_ids , 'refresh_group_member');
            $message = sprintf('"%s" 加入了群聊' , UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username));
            // 发送群通知
            ChatUtil::groupSend($auth , [
                'user_id'   => $group->user_id ,
                'group_id'   => $group->id ,
                'type'      => 'notification' ,
                'message'   => $message ,
            ] , true);
            return self::success($id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 邀请进群
    public static function inviteJoinGroup(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id'          => 'required' ,
            'relation_user_id'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到对应群信息' , 404);
        }
        $relation_user_id = json_decode($param['relation_user_id'] , true);
        if (empty($relation_user_id)) {
            return self::error('请提供邀请的用户');
        }
        if (GroupMemberModel::someoneExist($relation_user_id , $group->id)) {
            // 检查用户是否存在（批量检测）
            return self::error('包含现有群成员，请重新选择' , 403);
        }
        $log = '';
        foreach ($relation_user_id as $v)
        {
            $user = UserModel::findById($v);
            if (empty($user)) {
                return self::error('被邀请人中存在不存在的用户' , 404);
            }
            $log .= $user->nickname . ',';
        }
        $log = mb_substr($log , 0 , mb_strlen($log) - 2);
        $param['type']      = 'group';
        $param['op_type']   = 'invite_into_group';
        $param['log']       = sprintf('"%s" 邀请 "%s" 进群' , $auth->user->nickname , $log);
        $param['user_id']   = $group->user_id;
        $param['invite_user_id'] = $auth->user->id;
        if ($auth->user->id != $group->user_id) {
            // 非群组
            if ($group->auth == 1) {
                // 开启了进群认证
                $param['status']    = 'wait';
                $id = ApplicationModel::insertGetId(array_unit($param , [
                    'type' ,
                    'op_type' ,
                    'user_id' ,
                    'group_id' ,
                    'relation_user_id' ,
                    'status' ,
                    'remark' ,
                    'log' ,
                ]));
                $auth->push($group->user_id , 'refresh_application');
                $auth->push($group->user_id , 'refresh_unread_count');
                $auth->push($group->user_id , 'refresh_app_unread_count');
                AppPushUtil::pushCheckForUser($auth->platform , $group->user_id , function() use($param){
                    AppPushUtil::pushForInviteGroup($param['user_id'] , $param['log'] , '邀请进群');
                });
                AppPushUtil::pushCheckWithNewForUser($group->user_id , function() use($param , $auth){
                    $auth->push($param['user_id'] , 'new');
                });
                return self::success($id);
            }
        }
        // 没有开启进群认证
        try {
            DB::beginTransaction();
            $param['status']    = 'auto_approve';
            $id = ApplicationModel::insertGetId(array_unit($param , [
                'type' ,
                'op_type' ,
                'user_id' ,
                'group_id' ,
                'relation_user_id' ,
                'status' ,
                'remark' ,
                'log' ,
            ]));
            // 未开启进群认证 or 群组操作
            foreach ($relation_user_id as $v)
            {
                GroupMemberModel::u_insertGetId($v , $group->id);
            }
            $user_ids = GroupMemberModel::getUserIdByGroupId($group->id);
            DB::commit();
            $auth->pushAll($user_ids , 'refresh_group');
            $auth->pushAll($user_ids , 'refresh_application');
            $auth->pushAll($user_ids , 'refresh_group_member');
            $message = sprintf('"%s" 邀请了 "%s" 加入了群聊' , UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username) , $log);
            // 发送群通知
            ChatUtil::groupSend($auth , [
                'user_id'   => $group->user_id ,
                'group_id'   => $group->id ,
                'type'      => 'notification' ,
                'message'   => $message ,
            ] , true);
            return self::success($id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function decideApp(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'application_id'    => 'required' ,
            'status'            => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $range = config('business.application_status_for_client');
        if (!in_array($param['status'] , $range)) {
            return self::error('不支持的 status 值，当前受支持的值有 ' . implode(',' , $range));
        }
        $app = ApplicationModel::findById($param['application_id']);
        if (empty($app)) {
            return self::error('未找到对应的申请记录' , 404);
        }
        if ($app->type != 'group') {
            return self::error('该申请记录类型不是 群！禁止操作' , 403);
        }
        $op_type = config('business.app_type_for_group');
        if (!in_array($app->op_type , $op_type)) {
            return self::error('该申请的记录 op_type 类型错误！禁止操作' , 403);
        }
        $deny_application_status = config('business.deny_application_status');
        if (!in_array($app->status , $deny_application_status)) {
            return self::error('当前申请记录的状态禁止操作');
        }
        $relation_user_id = json_decode($app->relation_user_id , true);
        foreach ($relation_user_id as $v)
        {
            if (GroupMemberModel::exist($v , $app->group_id)) {
                return self::error('用户列表中包含群成员！禁止操作' , 403);
            }
        }
        try {
            DB::beginTransaction();
            ApplicationModel::updateById($app->id , [
               'status' => $param['status']
            ]);
            if ($param['status'] == 'approve') {
                // 同意进群
                $remark = '';
                foreach ($relation_user_id as $v)
                {
                    GroupMemberModel::u_insertGetId($v , $app->group_id);
                    $user = UserModel::findById($v);
                    if (empty($user)) {
                        DB::rollBack();
                        return self::error('存在无效的用户！禁止操作' , 404);
                    }
                    $remark .= $user->nickname . ',';
                }
                $remark = mb_substr($remark , 0 , mb_strlen($remark) - 2);
            }
            $user_ids = GroupMemberModel::getUserIdByGroupId($app->group_id);
            DB::commit();
            if ($param['status'] == 'approve') {
                // 同意
                $auth->pushAll($user_ids , 'refresh_group');
                $auth->pushAll($user_ids , 'refresh_application');
                $auth->pushAll($user_ids , 'refresh_group_member');
                switch ($app->op_type)
                {
                    case 'app_group':
                        // 个人申请进群
                        $message = sprintf('"%s" 加入了群聊' , $remark);
                        break;
                    case 'invite_into_group':
                        // 邀请进群
                        $invite_user = UserModel::findById($app->invite_user_id);
                        $message = sprintf('"%s" 邀请 "%s" 加入了群聊' , $invite_user ?
                            UserUtil::getNameFromNicknameAndUsername($invite_user->nickname , $invite_user->username) :
                            'null' , $remark);
                        break;
                }
                // 发送群通知
                ChatUtil::groupSend($auth , [
                    'user_id' => $auth->user->id ,
                    'group_id' => $app->group_id ,
                    'type' => 'notification' ,
                    'message' => $message ,
                ] , true);
            } else {
                // 拒绝
                $auth->pushAll($user_ids , 'refresh_application');
                $auth->pushAll($user_ids , 'refresh_unread_count');
                $auth->pushAll($user_ids , 'refresh_app_unread_count');
            }
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function kickMember(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $kick_user_ids = json_decode($param['user_id'] , true);
        if (empty($kick_user_ids)) {
            return self::error('请提供待删除的用户');
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群信息' , 404);
        }
        if ($group->user_id != $auth->user->id) {
            return self::error('您并非群主，禁止操作' , 403);
        }
        if (in_array($group->user_id , $kick_user_ids)) {
            return self::error('群主不能T自己！' , 403);
        }
        if ($group->is_service == 1) {
            return self::error('您不能解散系统内置客服群' , 403);
        }
        try {
            DB::beginTransaction();
            $message = '"';
            foreach ($kick_user_ids as $v)
            {
                GroupMemberModel::delByUserIdAndGroupId($v , $group->id);
                $user = UserModel::findById($v);
                $nickname = UserUtil::getNameFromNicknameAndUsername($user->nickname , $user->username);
                $message .= $nickname . ',';
                // 删除这个人关于该会话的相关消息
                SessionUtil::delByUserIdAndTypeAndTargetId($v , 'group' , $group->id);
            }
            $message = mb_substr($message , 0 , mb_strlen($message) - 1);
            $message .= '" 被移除了群聊';
            DB::commit();
            $group_member_ids = GroupMemberModel::getUserIdByGroupId($group->id);
            $auth->pushAll($group_member_ids , 'refresh_group_member');
            $auth->pushAll($kick_user_ids , 'refresh_group');
            $auth->pushAll($kick_user_ids , 'delete_group_from_cache');
            // 通知被踢出群的那个人，删除本地
            ChatUtil::groupSend($auth , [
                'user_id'   => $group->user_id ,
                'group_id'  => $group->id ,
                'type'      => 'notification' ,
                'message'   => $message
            ] , true);
            foreach ($kick_user_ids as $v)
            {
                // 通知被移除的成员他们已经被移除群聊
                AppPushUtil::pushCheckForUser($auth->platform , $group->user_id , function() use($v , $group){
                    AppPushUtil::push($v , sprintf('你被踢出了群 %s' , $group->name) , '退群通知');
                });
                AppPushUtil::pushCheckWithNewForUser($group->user_id , function() use($v , $auth){
                    $auth->push($v , 'new');
                });
            }
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function create(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group_type_range = config('business.group_type');
        if (!in_array($param['type'] , $group_type_range)) {
            return self::error('不支持的群类型，当前受支持的群类型有' . implode(',' , $group_type_range));
        }
        if ($param['type'] == 2) {
            if (empty($param['expire'])) {
                return self::error('当 type = 2（时效群）时，过期时间必须提供');
            }
            // 检查
            if (!check_datetime($param['expire'] , 'minute')) {
                return self::error('过期时间格式错误');
            }
        }
        $param['user_id'] = $auth->user->id;
        $param['expire'] = empty($param['expire']) ? null : $param['expire'];
        $user_ids = json_decode($param['user_ids'] , true);
        $user_ids = empty($user_ids) ? [] : $user_ids;
        if (in_array($auth->user->id , $user_ids)) {
            $user_ids = array_diff($user_ids , [$auth->user->id]);
        }
        $single   = empty($user_ids) ? true : false;
        $user_ids[] = $auth->user->id;
        $param['anonymous'] = empty($param['anonymous']) ? 0 : $param['anonymous'];
        try {
            DB::beginTransaction();
            $group_id = GroupModel::insertGetId(array_unit($param , [
                'user_id' ,
                'name' ,
                'type' ,
                'expire' ,
                'anonymous' ,
            ]));
            DB::commit();
            if (!$single) {
                // 如果群成员数量超过一个，那么推送建群通知
                $message = '"%s" 邀请了 "%s"加入了群聊';
                $member_string = '';
                // 如果有群成员的话
                foreach ($user_ids as $v)
                {
                    $member = UserModel::findById($v);
                    if (empty($member)) {
                        return self::error('无法创建群！邀请的成员中存在无效的用户' , 404);
                    }
                    GroupMemberModel::u_insertGetId($v , $group_id);
                    if ($v == $auth->user->id) {
                        continue ;
                    }
                    $member_string .= UserUtil::getNameFromNicknameAndUsername($member->nickname , $member->username);
                    $member_string .= ' ,';
                }
                // 群成员数量不只一个人
                // 发送邀请通知
                $group_owner_name = UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username);
                $member_string = mb_substr($member_string , 0 , mb_strlen($member_string) - 2);
                ChatUtil::groupSend($auth , [
                    'user_id'   => $auth->user->id ,
                    'type'      => 'notification' ,
                    'group_id'  => $group_id ,
                    'message'   => sprintf($message , $group_owner_name , $member_string) ,
                    'extra'     => '' ,
                ] , true);
            }
            return self::success($group_id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
     }

    // 解散群
    public static function disbandGroup(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群' , 404);
        }
        if ($group->user_id != $auth->user->id) {
            return self::error('您不是群主，无权限解散群' , 403);
        }
        // 检查是否是客服群
        if ($group->is_service == 1) {
            return self::error('您不能解散系统内置客服群' , 403);
        }
        try {
            DB::beginTransaction();
            // 获取群成员
            $user_ids = GroupMemberModel::getUserIdByGroupId($param['group_id']);
            GroupUtil::delete($param['group_id']);
            DB::commit();
            $auth->pushAll($user_ids , 'refresh_group');
            $auth->pushAll($user_ids , 'refresh_group_member');
            $auth->pushAll($user_ids , 'refresh_session');
            // 解散群后，需要删除群内成员的本地聊天记录
            $auth->pushAll($user_ids , 'empty_group_message' , [$param['group_id']]);
            foreach ($user_ids as $v)
            {
                if ($v == $auth->user->id) {
                    continue ;
                }
                AppPushUtil::pushCheckForUser($auth->platform , $v , function() use($v , $group){
                    AppPushUtil::push($v , '群组解散了群 ' . $group->name);
                });
                AppPushUtil::pushCheckWithNewForUser($v , function() use($v , $auth){
                    $auth->push($v , 'new');
                });
            }
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function myGroup(Auth $auth , array $param)
    {
        $my_group = GroupMemberModel::getByUserId($auth->user->id);
        foreach ($my_group as $v)
        {
            UserUtil::handle($v->user);
            GroupUtil::handle($v->group , $auth->user->id);
        }
        return self::success($my_group);
    }

    public static function groupMember(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群' , 404);
        }
        $member = GroupMemberModel::getByGroupId($group->id);
        foreach ($member as $v)
        {
            UserUtil::handle($v->user , $auth->user->id);
            GroupUtil::handle($v->group , $auth->user->id);
            $v->origin_alias = $v->alias;
            $v->alias = empty($v->alias) ?
                $v->user->nickname :
                $v->alias;
        }
        return self::success($member);
    }

    public static function groupInfo(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群' , 404);
        }
        GroupUtil::handle($group , $auth->user->id);
        return self::success($group);
    }

    public static function groupAuth(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'auth' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group_auth = config('business.group_auth');
        if (!in_array($param['auth'] , $group_auth)) {
            return self::error('auth 字段值不在支持的范围');
        }
        GroupModel::updateById($param['group_id'] , array_unit($param , [
            'auth'
        ]));
        return self::success('操作成功');
    }

    // 群二维码
    public static function QRCodeData(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群信息' , 404);
        }
        $download = config('app.download');
        $data = [
            'type'  => 'group' ,
            'id'    => $param['group_id'] ,
        ];
        $base64 = base64_encode(json_encode($data));
        $link = sprintf('%s?identity=%s&data=%s' , $download , config('app.identity') , $base64);
        return self::success($link);
    }

    public static function groupMemberLimit(Auth $auth , array $param)
    {
        return self::success(config('app.group_member_limit'));
    }

    public static function setGroupName(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'name'     => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在，禁止操作' , 404);
        }
        GroupModel::updateById($group->id , [
            'name' => $param['name'] ,
        ]);
        // 获取用户
        $user_ids = GroupMemberModel::getUserIdByGroupId($group->id);
        // 刷新群信息
        $auth->pushAll($user_ids , 'refresh_group');
        // 发送消息
        ChatUtil::groupSend($auth , [
            'user_id'   => $auth->user->id ,
            'group_id' => $group->id ,
            'type'      => 'notification' ,
            'message'   => sprintf('"%s" 修改群名为 "%s"' , UserUtil::getNameFromNicknameAndUsername($auth->user->nickname , $auth->user->username) , $param['name']) ,
            'extra'     => '' ,
        ] , true);
        return self::success();
    }

    // 设置我再群里面的别名
    public static function setAlias(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'alias'     => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在，禁止操作' , 404);
        }
        if (!GroupMemberModel::exist($auth->user->id , $param['group_id'])) {
            return self::error('您不再该群内，禁止操作' , 403);
        }
        GroupMemberModel::updateByUserIdAndGroupId($auth->user->id , $param['group_id'] , [
            'alias' => $param['alias']
        ]);
        return self::success();
    }

    public static function setCanNotice(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'can_notice' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在，禁止操作' , 404);
        }
        if (!GroupMemberModel::exist($auth->user->id , $param['group_id'])) {
            return self::error('您不再该群内，禁止操作' , 403);
        }
        $bool_for_int = config('business.bool_for_int');
        if (!in_array($param['can_notice'] , $bool_for_int)) {
            return self::error('不支持的 can_notice 值，当前受支持的值类型有' . implode(' , ' , $bool_for_int));
        }
        GroupMemberModel::updateByUserIdAndGroupId($auth->user->id , $param['group_id'] , [
            'can_notice' => $param['can_notice']
        ]);
        // 刷新群会话
        $auth->push($auth->user->id , 'refresh_session');
        return self::success();
    }

    public static function setAnnouncement(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id'      => 'required' ,
            'announcement'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在' , 404);
        }
        if ($group->user_id != $auth->user->id) {
            return self::error('您不是群主' , 403);
        }
        // 修改群公告
        GroupModel::updateById($param['group_id'] , [
            'announcement' => $param['announcement'] ,
        ]);
        $message = sprintf("@所有人\n%s" , $param['announcement']);
        // 发送群消息通知
        ChatUtil::groupSend($auth , [
            'user_id' => $auth->user->id ,
            'group_id' => $group->id ,
            'type' => 'text' ,
            'message' => $message ,
            'extra' => '' ,
        ] , true);
        // 更新群信息
        return self::success();
    }

    public static function banned(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id'  => 'required' ,
            'user_ids'   => 'required' ,
            'banned'   => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查提供的值是否正确
        $bool_for_int = config('business.bool_for_int');
        if (!in_array($param['banned'] , $bool_for_int)) {
            return self::error('不支持的 banned 值，当前受支持的值有 ' . implode(' , ' , $bool_for_int));
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在' , 404);
        }
        if ($group->user_id != $auth->user->id) {
            return self::error('您不是群主' , 403);
        }
        $user_ids = json_decode($param['user_ids'] , true);
        if (empty($user_ids)) {
            return self::error('请提供要禁言的群成员');
        }
        foreach ($user_ids as $v)
        {
            if (!GroupMemberModel::exist($v , $group->id)) {
                return self::error('包含非群成员用户' , 403);
            }
            if ($v == $auth->user->id) {
                return self::error('不能将自身设置禁言' , 403);
            }
        }
        try {
            DB::beginTransaction();
            foreach ($user_ids as $v)
            {
                GroupMemberModel::updateByUserIdAndGroupId($v , $group->id , [
                    'banned' => $param['banned']
                ]);
            }
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function allbanned(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id'  => 'required' ,
            'banned'   => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查提供的值是否正确
        $bool_for_int = config('business.bool_for_int');
        if (!in_array($param['banned'] , $bool_for_int)) {
            return self::error('不支持的 banned 值，当前受支持的值有 ' . implode(' , ' , $bool_for_int));
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('群不存在' , 404);
        }
        if ($group->user_id != $auth->user->id) {
            return self::error('您不是群主' , 403);
        }
        GroupModel::updateById($group->id , [
            'banned' => $param['banned']
        ]);
        return self::success();
    }

    public static function customer(Auth $auth , array $param)
    {
        $customer = GroupMemberModel::customer($auth->user->id);
        GroupUtil::handle($customer);
        return self::success($customer);
    }

}