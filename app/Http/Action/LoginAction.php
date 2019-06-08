<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 9:50
 */

namespace App\Http\Action;

use App\Http\Base;
use App\Model\Project;
use App\Model\User;
use App\Model\UserToken;
use App\Util\Misc;
use function core\array_unit;
use Core\Lib\Validator;
use function core\ssl_random;

class LoginAction extends Action
{
    public static function register(array $param)
    {
        $validator = Validator::make($param , [
            'identifier'    => 'required' ,
            'username'      => 'required' ,
            'role'          => 'required' ,
        ] , [
            'identifier.required' => '必须' ,
            'username.required' => '必须' ,
            'role.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->error());
        }
        $role_range = array_keys(config('business.role'));
        if (!in_array($param['role'] , $role_range)) {
            return self::error([
                'role' => '不支持的角色类型，当前支持的有：' . implode(',' , $role_range) ,
            ]);
        }
        // 检查 identfier 是否存在
        $project = Project::findByIdentifier($param['identifier']);
        if (empty($project)) {
            return self::error([
                'identifier' => '项目标识符错误' ,
            ]);
        }
        // 检查用户名是否重复
        $user = User::findByUsername($param['username']);
        if (!empty($user)) {
            return self::error('用户名已经被使用');
        }
        $unique_code = Misc::uniqueCode();
        $param['unique_code'] = $unique_code;
        User::insert(array_unit($param , [
            'identifier' ,
            'username' ,
            'nickname' ,
            'avatar' ,
            'role' ,
            'unique_code'
        ]));
        return self::success($unique_code);
    }

    public static function login(array $param)
    {

        $validator = Validator::make($param , [
            'unique_code'    => 'required' ,
        ] , [
            'unique_code.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->error());
        }
        $user = User::findByUniqueCode($param['unique_code']);
        if (empty($user)) {
            return self::error([
                'unique_code' => '未找到当前提供的 unique_code 对应的用户' ,
            ]);
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserToken::insert(array_unit($param , [
            'token' ,
            'expire' ,
            'user_id' ,
            'identifier' ,
        ]));
        return self::success($param['token']);
    }
}