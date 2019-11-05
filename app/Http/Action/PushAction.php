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
    public static function single(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'user_id'   => 'required' ,
            'type'      => 'required' ,
            'data'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $param['push_type'] = 'single';
        $param['role'] = 'user';
        $param['identifier'] = $auth->identifier;
        try {
            DB::begintransaction();
            $id = PushModel::u_insertGetId($param['identifier'] , $param['push_type'] , $param['type'] , $param['data'] , $param['role'] , $param['user_id']);
            PushReadStatusModel::initByPushId($id , [$param['user_id']]);
            $push = PushModel::findById($id);
            PushUtil::single($auth->identifier , $param['user_id'] , $param['type'] , $push);
            // 让他更新未读消息数量
            DB::commit();
            return self::success($push);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function multiple(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'type' => 'required' ,
            'title' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $param['push_type'] = 'multiple';
        $param['role']      = in_array($param['role'] , config('business.push_role')) ? $param['role'] : 'all';
        $param['user_id']   = (int) ($param['user_id'] ?? '');
        try {
            DB::beginTransaction();
            if ($param['role'] == 'designation') {
                // 指定用户
                $user_ids = json_decode($param['user_id'] , true);
                if (empty($user_ids)) {
                    DB::rollBack();
                    return self::error('请提供推送的用户');
                }
            } else {
                // 按照角色
                $user_ids = in_array($param['role'] , ['admin' , 'user']) ?
                    UserModel::getIdByIdentifierAndRole($auth->identifier , $param['role']) :
                    UserModel::getIdByIdentifierAndRole($auth->identifier , null);
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
//            PushReadStatusModel::initByPushId($id , $user_ids);
            $push = PushModel::findById($id);
            foreach ($user_ids as $v)
            {
                if ($param['type'] == 'system') {
                    // 创建 或 更新 会话
                    SessionUtil::createOrUpdate($v , $param['type'] , $param['user_id']);
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

    // 设置：读取状态
    public static function readStatus(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'push_id'      => 'required' ,
            'is_read'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查是否已经存在
        $param['user_id'] = $auth->user->id;
        $res = PushReadStatusModel::findByUserIdAndPushId($auth->user->id , $param['push_id']);
        if (empty($res)) {
            $id = PushReadStatusModel::u_insertGetId($param['user_id'] , $param['push_id'] , $param['is_read']);
        } else {
            PushReadStatusModel::updateById($res->id , array_unit($param , [
                'is_read'
            ]));
            $id = $res->id;
        }
        return self::success($id);
    }

    public static function system(Auth $auth , array $param)
    {
        $param['type'] = 'system';
        return self::multiple($auth , $param);
    }
}