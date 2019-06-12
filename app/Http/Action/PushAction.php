<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 17:33
 */

namespace App\Http\Action;


use App\Http\Base;
use App\Http\Util\PushUtil;
use App\Model\Push as PushModel;
use App\Model\PushReadStatus;
use App\Model\User;
use App\Http\Auth;
use function core\array_unit;
use Core\Lib\Validator;
use App\Util\Push;
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
            return self::error($validator->error());
        }
        $param['push_type'] = 'single';
        $param['role'] = 'user';
        $param['identifier'] = $auth->identifier;
        try {
            DB::begintransaction();
            $id = PushModel::u_insertGetId($param['identifier'] , $param['push_type'] , $param['type'] , $param['data'] , $param['role'] , $param['user_id']);
            PushReadStatus::initByPushId($id , [$param['user_id']]);
            $push = PushModel::findById($id);
            Push::single($auth->identifier , $param['user_id'] , $param['type'] , $push);
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
            'type'      => 'required' ,
            'data'      => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->error());
        }
        $param['push_type'] = 'multiple';
        $param['role'] = in_array($param['role'] , config('business.push_role')) ? $param['role'] : 'all';
        $param['identifier'] = $auth->identifier;
        try {
            if ($param['role'] == 'designation') {
                // 指定用户
                $user_ids = json_decode($param['user_id'] , true);
                if (empty($user_ids)) {
                    return self::error('请提供推送的用户');
                }
            } else {
                // 按照角色
                $user_ids = in_array($param['role'] , ['admin' , 'user']) ?
                    User::getIdByIdentifierAndRole($auth->identifier , $param['role']) :
                    User::getIdByIdentifierAndRole($auth->identifier , null);
            }
            $id = PushModel::u_insertGetId($param['identifier'] , $param['push_type'] , $param['type'] , $param['data'] , $param['role']);
            // 未读消息状态
            PushReadStatus::initByPushId($id , $user_ids);
            $push = PushModel::findById($id);
            foreach ($user_ids as $v)
            {
                Push::single($auth->identifier , $v , $param['type'] , $push);
            }
            DB::commit();
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
            return self::error($validator->error());
        }
        // 检查是否已经存在
        $param['user_id'] = $auth->user->id;
        $res = PushReadStatus::findByUserIdAndPushId($auth->user->id , $param['push_id']);
        if (empty($res)) {
            $id = PushReadStatus::u_insertGetId($param['user_id'] , $param['push_id'] , $param['is_read']);
        } else {
            PushReadStatus::updateById($res->id , array_unit($param , [
                'is_read'
            ]));
            $id = $res->id;
        }
        return self::success($id);
    }
}