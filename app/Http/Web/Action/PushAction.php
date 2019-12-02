<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 17:33
 */

namespace App\Http\Action;


use App\Http\Base;
//use App\Http\Util\PushUtil;
use App\Model\PushModel as PushModel;
use App\Model\PushReadStatusModel;
use App\Model\SessionModel;
use App\Model\UserModel;
use App\Http\Auth;
use App\Util\SessionUtil;
use function core\array_unit;
use Core\Lib\Validator;
use App\Util\PushUtil;
use Exception;
use Illuminate\Support\Facades\DB;

class PushAction extends Action
{
    public static function push(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'push_type' => 'required' ,
            'type' => 'required' ,
            'title' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 推送必须是后台工作人员才能够发送
        if ($auth->user->role != 'admin') {
            return self::error('当前用户并非工作人员，禁止推送' , 403);
        }
        $push_type_for_user = config('business.push_type_for_user');
        if (!in_array($param['push_type'] , $push_type_for_user)) {
            return self::error('不支持的 push_type，当前受支持的 push_type 有：' . implode(',' , $push_type_for_user));
        }
        if ($param['push_type'] == 'multiple') {
            $role_for_push = config('business.role_for_push');
            if (!in_array($param['role'] , $role_for_push)) {
                return self::error('不支持的 role ，当前受支持的 role 有：' . implode(',' , $role_for_push));
            }
            if ($param['role'] == 'desiganation') {
                if (empty($param['user_id'])) {
                    return self::error('user_id is required');
                }
                $user_ids = json_decode($param['user_id'] , true);
                if (empty($user_ids)) {
                    return self::error('请提供目标用户');
                }
                // 检查推送的用户是否是平台存在的用户
                foreach ($user_ids as $v)
                {
                    $user = UserModel::findById($v);
                    if (empty($user)) {
                        return self::error('目标用户中包含平台不存在的用户' , 404);
                    }
                }
            }
        } else {
            if (empty($param['user_id'])) {
                return self::error('user_id is required');
            }
            $user = UserModel::findById($param['user_id']);
            if (empty($user)) {
                return self::error('用户不存在' , 404);
            }
        }
        $push_type_for_push = config('business.push_type_for_push');
        if (!in_array($param['type'] , $push_type_for_push)) {
            return self::error('不支持的 type，当前受支持的 type 有：' . implode(',' , $push_type_for_push));
        }
        try {
            DB::beginTransaction();
            // 最终确定的推送目标群体
            $user_ids = [];
            if ($param['push_type'] == 'single') {
                // 单人推送
                $user_ids = [$param['user_id']];
            } else {
                // 多人推送
                if ($param['role'] == 'designation') {
                    // 指定用户推送
                    $user_ids = json_decode($param['user_id'] , true);
                    if (empty($user_ids)) {
                        DB::rollBack();
                        return self::error('请提供推送的用户');
                    }
                } else {
                    // 按照角色
                    switch($param['role'])
                    {
                        case 'admin': case 'user':
                            $user_ids = UserModel::getIdByIdentifierAndRole($auth->identifier , $param['role']);
                            break;
                        case 'all':
                            $user_ids = UserModel::getIdByIdentifierAndRole($auth->identifier , null);
                            break;
                        default:
                            throw new Exception("不支持的 role 类型，请提供该 role[{$param['role']}] 的处理逻辑");
                    }
                }
            }
            $id = PushModel::insertGetId(array_unit($param , [
                'push_type' ,
                'type' ,
                'user_id' ,
                'role' ,
                'title' ,
                'desc' ,
                'content' ,
            ]));
            // 未读消息状态
            $push = PushModel::findById($id);
            foreach ($user_ids as $v)
            {
                switch ($param['type'])
                {
                    case 'system':
                        // 创建会话
                        SessionUtil::createOrUpdate($v , 'system');
                        break;
                }
                // 设置未读消息数量
                PushReadStatusModel::u_insertGetId($v , $id , $param['type'] , 0);
            }
            DB::commit();
            // 刷新会话列表
            PushUtil::multiple($auth->identifier , $user_ids , 'refresh_session');
            return self::success($push);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}