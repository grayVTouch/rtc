<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 17:33
 */

namespace App\Http\Action;


use App\Http\Base;
use App\Model\Push as PushModel;
use App\Model\User;
use function core\array_unit;
use Core\Lib\Validator;
use App\Util\Push;

class PushAction extends Action
{
    public static function single(Base $app , array $param)
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
        $param['identifier'] = $app->identifier;
        $id = PushModel::insertGetId(array_unit($param , [
            'identifier' ,
            'push_type' ,
            'user_id' ,
            'role' ,
            'type' ,
            'data' ,
        ]));
        $res = Push::single($app->identifier , $param['user_id'] , $param['type'] , [
            'push_id'   => $id ,
            'push_data' => $param['data']
        ]);
        return self::success([
            'push_id' => $id ,
            'result' => $res
        ]);
    }

    public static function multiple(Base $app , array $param)
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
        $param['identifier'] = $app->identifier;
        $id = PushModel::insertGetId(array_unit($param , [
            'identifier' ,
            'push_type' ,
            'user_id' ,
            'role' ,
            'type' ,
            'data' ,
        ]));
        $role_range = array_keys(config('business.role'));
        $user_ids = in_array($param['role'] , $role_range) ? User::getIdByRole($param['role']) : User::getIdByRole();
        $res = Push::multiple($app->identifier , $user_ids , $param['type'] , [
            'push_id'   => $id ,
            'push_data' => $param['data']
        ]);
        return self::success([
            'push_id' => $id ,
            'result' => $res
        ]);
    }
}