<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 9:50
 */

namespace App\Http\Action;

use App\Http\Base;
use App\Model\ProjectModel;
use App\Model\UserModel;
use App\Model\UserInfoModel;
use App\Model\UserTokenModel;
use App\Redis\UserRedis;
use App\Util\MiscUtil;
use function core\array_unit;
use Core\Lib\Hash;
use Core\Lib\Validator;
use function core\ssl_random;
use Exception;
use Illuminate\Support\Facades\DB;

class LoginAction extends Action
{
    public static function register(array $param)
    {
        $validator = Validator::make($param , [
            'identifier'    => 'required' ,
            'username'      => 'required' ,
            'password'      => 'required' ,
            'role'          => 'required' ,
        ] , [
            'identifier.required' => '必须' ,
            'username.required' => '必须' ,
            'password.required' => '必须' ,
            'role.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $role_range = config('business.role');
        if (!in_array($param['role'] , $role_range)) {
            return self::error([
                'role' => '不支持的角色类型，当前支持的有：' . implode(',' , $role_range) ,
            ]);
        }
        // 检查 identifier 是否存在
        if ($param['']) {

        }
        $project = ProjectModel::findByIdentifier($param['identifier']);
        if (empty($project)) {
            return self::error([
                'identifier' => '项目标识符错误' ,
            ]);
        }
        // 检查用户名是否重复
        $user = UserModel::findByUsername($param['username']);
        if (!empty($user)) {
            return self::error('用户名已经被使用');
        }
        $unique_code = MiscUtil::uniqueCode();
        $param['unique_code'] = $unique_code;
        $param['password'] = Hash::make($param['password']);
        try {
            DB::beginTransaction();
            $id = UserModel::insertGetId(array_unit($param , [
                'identifier' ,
                'username' ,
                'password' ,
                'role' ,
                'unique_code' ,
            ]));
            UserInfoModel::insert([
                'user_id'   => $id ,
                'avatar'    => $param['avatar'] ,
                'nickname'  => $param['nickname'] ,
            ]);
            DB::commit();
            return self::success($unique_code);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 远程登录
    public static function remoteLogin(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'unique_code'    => 'required' ,
        ] , [
            'unique_code.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = UserModel::findByUniqueCode($param['unique_code']);
        if (empty($user)) {
            return self::error([
                'unique_code' => '未找到当前提供的 unique_code 对应的用户' ,
            ]);
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserTokenModel::insert(array_unit($param , [
            'token' ,
            'expire' ,
            'user_id' ,
            'identifier' ,
        ]));
        return self::success($param['token']);
    }

    // 常规登录
    public static function login(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'username'    => 'required' ,
            'password'    => 'required' ,
        ] , [
            'username.required' => '必须' ,
            'password.required' => '必须' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = UserModel::findByUsername($param['username']);
        if (empty($user)) {
            return self::error([
                'username' => '未找到用户' ,
            ]);
        }
        if (!Hash::check($param['password'] , $user->password)) {
            return self::error([
                'password' => '密码错误'
            ]);
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserTokenModel::insert(array_unit($param , [
            'token' ,
            'expire' ,
            'user_id' ,
            'identifier' ,
        ]));
        return self::success($param['token']);
    }
}