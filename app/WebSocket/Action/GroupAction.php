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
use App\Util\PushUtil;
use App\WebSocket\Auth;
use App\WebSocket\Util\MessageUtil;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use App\Model\ApplicationModel;
use Exception;
use function core\array_unit;
use function extra\check_datetime;
use Illuminate\Support\Facades\DB;
use function WebSocket\ws_config;

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
        if (GroupMemberModel::exist($auth->user->id , $param['group'])) {
            return self::success('你已经是群成员' , 403);
        }
        // 检查是否开启进群认证
        if ($group->enable_auth == 1) {
            // 开启了进群认证
            $param['type']      = 'group';
            $param['op_type']   = 'app_group';
            $param['user_id']   = $group->user_id;
            $param['relation_user_id'] = $auth->user->id;
            $param['status']    = 'wait';
            $id = ApplicationModel::insertGetId(array_unit($param , [
                'type' ,
                'op_type' ,
                'user_id' ,
                'group_id' ,
                'relation_user_id' ,
                'status' ,
                'remark' ,
            ]));
            $auth->push($group->user_id , 'refresh_application');
            $auth->push($group->user_id , 'refresh_unread_count');
            if (ws_config('app.enable_app_push')) {
                // todo 发送 app 推送
            }
            return self::success($id);
        }
        try {
            DB::beginTransaction();
            // 开启了进群认证
            $param['type']      = 'group';
            $param['op_type']   = 'app_group';
            $param['user_id']   = $group->user_id;
            $param['relation_user_id'] = $auth->user->id;
            $param['status']    = 'auto_approve';
            $id = ApplicationModel::insertGetId(array_unit($param , [
                'type' ,
                'op_type' ,
                'user_id' ,
                'group_id' ,
                'relation_user_id' ,
                'status' ,
                'remark' ,
            ]));
            // 未开启进群认证
            GroupMemberModel::u_insertGetId($auth->user->id , $group->id);
            $message = sprintf('"%s" 加入了群聊');
            $group_message_id = GroupMessageModel::u_insertGetId(0 , $group->id , 'notification' , $message);
            GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $group->id , 0);
            $msg = GroupMessageModel::findById($group_message_id);
            MessageUtil::handleGroupMessage($msg);
            $user_ids = GroupMemberModel::getUserIdByGroupId($group->id);
            DB::commit();
            $auth->pushAll($user_ids , 'refresh_session');
            $auth->pushAll($user_ids , 'refresh_group_member');
            // 发送消息（这个可以丢给 ChatAction 去做）
            $auth->pushAll($user_ids , 'group_message' , $msg);
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
        if (!UserModel::allExist($relation_user_id)) {
            // 检查用户是否存在（批量检测）
            return self::error('包含现有群成员，请重新选择' , 403);
        }
        try {
            DB::beginTransaction();
            if ($group->enable_auth == 1) {
                // 开启了进群认证
                $param['type'] = 'group';
                $param['op_type'] = 'invite';
                $param['user_id'] = $group->user_id;
                $param['status'] = 'wait';
                $id = ApplicationModel::insertGetId(array_unit($param , [
                    'type' ,
                    'op_type' ,
                    'user_id' ,
                    'group_id' ,
                    'relation_user_id' ,
                    'status' ,
                    'remark' ,
                ]));
                return self::success($id);
            }
            // 未开启进群认证
            $msg = "";
            foreach ($relation_user_id as $v)
            {
                GroupMemberModel::u_insertGetId($v , $group->id);

            }
            DB::commit();
            // 群通知
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
        }
    }

    public static function decideApp(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'application_id' => 'required' ,
            'status' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $range = config('business.application_status_for_user');
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
        try {
            DB::beginTransaction();
            ApplicationModel::updateById($app->id , [
               'status' => $param['status']
            ]);
            if ($param['status'] == 'approve') {
                // 同意进群
                $relation_user_id = json_decode($app->relation_user_id , true);
                foreach ($relation_user_id as $v)
                {
                    GroupMemberModel::u_insertGetId($v , $app->group_id);
                }
            }
            DB::commit();
            return self::error('你好');
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true));
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
        $user_id = json_decode($param['user_id'] , true);
        if (empty($user_id)) {
            return self::error('请提供待删除的用户');
        }
        $group = GroupModel::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到群信息' , 404);
        }
        if ($group->user_id != $auth->user->id) {
            return self::error('您并非群主，禁止操作' , 403);
        }
        try {
            DB::beginTransaction();
            foreach ($user_id as $v)
            {
                GroupMemberModel::delByUserIdAndGroupId($v , $group->id);
            }
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
//            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true) , 500);
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
        $group_type_range = ws_config('business.group_type');
        if (!in_array($param['type'] , $group_type_range)) {
            return self::error([
                'type' => '不支持的群类型，当前受支持的群类型有' . implode(',' , $group_type_range) ,
            ]);
        }
        if ($param['type'] == 2) {
            if (empty($param['expire'])) {
                return self::error([
                    'expire' => '当 type = 2（时效群）时，过期时间必须提供' ,
                ]);
            }
            // 检查
            if (!check_datetime($param['expire'] , 'minute')) {
                return self::error([
                    'expire' => '过期时间格式错误' ,
                ]);
            }
        }
        $user_ids = json_decode($param['user_ids'] , true);
        $user_ids = empty($user_ids) ? [] : $user_ids;
        $single = empty($user_ids) ? true : false;
        $user_ids[] = $auth->user->id;
        try {
            DB::beginTransaction();
            $group_id = GroupModel::insertGetId(array_unit($param , [
                'name' ,
                'type' ,
                'expire' ,
            ]));
            $message = '""%s"邀请了"%s"加入了群聊';
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
                $member_string .= $member->username . ' ,';
            }
            if (!$single) {
                // 群成员数量不只一个人
                // 发送邀请通知
                $member_string = mb_substr($member_string , 0 , mb_strlen($member_string) - 2);
                $group_message_id = GroupMessageModel::u_insertGetId($auth->user->id , $group_id , 'notification' , sprintf($message , $auth->user->username , $member_string) , json_encode($user_ids));
                GroupMessageReadStatusModel::initByGroupMessageId($group_message_id , $param['group_id'] , $auth->user->id);
                $user_ids = GroupMemberModel::getUserIdByGroupId($group_id);
            }
            DB::commit();
            // 如果群成员数量超过一个，那么推送
            if (!$single) {
                $auth->pushAll($user_ids , 'refresh_group');
                $auth->pushAll($user_ids , 'refresh_session');
                $auth->pushAll($user_ids , 'refresh_unread_count');
            }
            return self::success($group_id);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
     }

}