<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 23:57
 */

namespace App\Http;

use App\Model\Project;
use App\Model\User;
use App\Model\UserToken;
use App\Util\Misc;
use function core\array_unit;
use Core\Lib\Validator;
use function core\ssl_random;

class Login extends Base
{
    // 注册
    public function register()
    {
        $param = $this->request->post;
        $param['identifier'] = $param['identifier'] ?? '';
        $param['username'] = $param['username'] ?? '';
        $param['nickname'] = $param['nickname'] ?? '';
        $param['avatar'] = $param['nickname'] ?? '';
        $param['role'] = $param['role'] ?? '';

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
            return $this->error($validator->error());
        }
        $role_range = array_keys(config('business.role'));
        if (!in_array($param['role'] , $role_range)) {
            return $this->error([
                'role' => '不支持的角色类型，当前支持的有：' . implode(',' , $role_range) ,
            ]);
        }
        // 检查 identfier 是否存在
        $project = Project::findByIdentifier($param['identifier']);
        if (empty($project)) {
            return $this->error([
                'identifier' => '项目标识符错误' ,
            ]);
        }
        $unique_code = Misc::uniqueCode();
        $param['unique_code'] = $unique_code;
        User::insert(array_unit($param , [
            'identifier' ,
            'username' ,
            'nickname' ,
            'avatar' ,
            'role' ,
            'unique'
        ]));
        return $this->success($unique_code);
    }

    // 登录
    public function login()
    {
        $param = $this->request->post;
        $param['unique_code'] = $param['unique_code'] ?? '';

        $validator = Validator::make($param , [
            'unique_code'    => 'required' ,
        ] , [
            'unique_code.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return $this->error($validator->error());
        }
        $user = User::findByUniqueCode($param['unique_code']);
        if (empty($user)) {
            return $this->error([
                'unique_code' => '未找到当前提供的 unique_code 对应的用户' ,
            ]);
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->user_id;
        $param['token']  = ssl_random(256);
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserToken::insert(array_unit($param , [
            'token' ,
            'expire' ,
            'user_id' ,
            'identifier' ,
        ]));
        return $this->success($param['token']);
    }
}