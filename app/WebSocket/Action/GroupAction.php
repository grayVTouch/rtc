<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 17:21
 */

namespace App\WebSocket\Action;


use App\Model\Group;
use App\Model\GroupMember;
use App\Model\User;
use App\WebSocket\Auth;
use Core\Lib\Throwable;
use Core\Lib\Validator;
use App\Model\Application;
use Exception;
use function extra\array_unit;
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
        $group = Group::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到对应群信息' , 404);
        }
        // 检查是否开启进群认证
        if ($group->enable_auth == 'y') {
            // 开启了进群认证
            $param['type']      = 'group';
            $param['op_type']   = 'add';
            $param['user_id']   = $group->user_id;
            $param['relation_user_id'] = $auth->user->id;
            $param['status']    = 'wait';
            $id = Application::insertGetId(array_unit($param , [
                'type' ,
                'op_type' ,
                'user_id' ,
                'group_id' ,
                'relation_user_id' ,
                'status' ,
                'remark' ,
            ]));
            // todo 看情况是否要加一个推送
            return self::success($id);
        }
        // 未开启进群认证
        GroupMember::u_insertGetId($auth->user->id , $group->id);
        return self::success();
    }

    // 邀请进群
    public static function inviteJoinGroup(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'group_id' => 'required' ,
            'relation_user_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $group = Group::findById($param['group_id']);
        if (empty($group)) {
            return self::error('未找到对应群信息' , 404);
        }
        $relation_user_id = json_decode($param['relation_user_id'] , true);
        if (!User::allExist($relation_user_id)) {
            // 检查用户是否存在（批量检测）
            return self::error('用户信息包含不支持的用户' , 403);
        }
        try {
            DB::beginTransaction();
            if ($group->enable_auth == 'y') {
                // 开启了进群认证
                $param['type'] = 'group';
                $param['op_type'] = 'invite';
                $param['user_id'] = $group->user_id;
                $param['status'] = 'wait';
                $id = Application::insertGetId(array_unit($param , [
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
            foreach ($relation_user_id as $v)
            {
                GroupMember::u_insertGetId($v , $group->id);
            }
            DB::commit();
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
        $app = Application::findById($param['application_id']);
        if (empty($app)) {
            return self::error('未找到对应的申请记录' , 404);
        }
        if ($app->type != 'group') {
            return self::error('该申请记录类型不是 群！禁止操作' , 403);
        }
        try {
            DB::beginTransaction();
            Application::updateById($app->id , [
               'status' => $param['status']
            ]);
            if ($param['status'] == 'approve') {
                // 同意进群
                $relation_user_id = json_decode($app->relation_user_id , true);
                foreach ($relation_user_id as $v)
                {
                    GroupMember::u_insertGetId($v , $app->group_id);
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
        $group = Group::findById($param['group_id']);
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
                GroupMember::delByUserIdAndGroupId($v , $group->id);
            }
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            return self::error((new Throwable())->exceptionJsonHandlerInDev($e , true));
        }
    }

}