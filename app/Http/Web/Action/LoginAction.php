<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 9:50
 */

namespace App\Http\Web\Action;

use App\Http\Web\Base;
use App\Lib\SMS\Zz253;
use App\Model\FriendModel;
use App\Model\JoinFriendMethodModel;
use App\Model\ProjectModel;
use App\Model\SessionModel;
use App\Model\SmsCodeModel;
use App\Model\UserJoinFriendOptionModel;
use App\Model\UserModel;
use App\Model\UserInfoModel;
use App\Model\UserOptionModel;
use App\Model\UserTokenModel;
use App\Redis\UserRedis;
use App\Util\MiscUtil;
use App\Util\PushUtil;
use App\Util\SessionUtil;
use function core\array_unit;
use Core\Lib\Hash;
use Core\Lib\Validator;
use function core\random;
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

    // 注册短信验证码
    public static function smsCode(Base $base , $type , array $param)
    {
        $validator = Validator::make($param , [
            'area_code' => 'required' ,
            'phone'     => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $param['identifier'] = $base->identifier;
        $param['code'] = random(4 , 'number' , true);
        $param['type'] = $type;
        // 检查短信验证码
        $sms_code = SmsCodeModel::findByIdentifierAndAreaCodeAndPhoneAndType($base->identifier , $param['area_code'] , $param['phone'] , $type);
        if (!empty($sms_code)) {
            if (strtotime($sms_code->update_time) + config('app.sms_code_wait_time') > time()) {
                return self::error('发送的频率过高，请等待1分钟后再发送短信验证码' , 401);
            }
            $res = Zz253::send($param['area_code'] , $param['phone'] , $param['code']);
            if ($res['code'] != 200) {
                return self::error(sprintf('Line: %s; 短信平台远程接口错误：%s' , __LINE__ , $res['data']));
            }
            SmsCodeModel::updateById($sms_code->id , [
                'code' => $param['code']
            ]);
        } else {
            $res = Zz253::send($param['area_code'] , $param['phone'] , $param['code']);
            if ($res['code'] != 200) {
                return self::error(sprintf('Line: %s; 短信平台远程接口错误：%s' , __LINE__ , $res['data']));
            }
            SmsCodeModel::insertGetId(array_unit($param , [
                'area_code' ,
                'identifier' ,
                'phone' ,
                'code' ,
                'type' ,
            ]));
        }
        return self::success($param['code']);
    }

    public static function test()
    {
        return self::success('one' , 200);
    }

    public static function registerForShare(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'role'      => 'required' ,
            'area_code' => 'required' ,
            'phone'     => 'required' ,
            'nickname'  => 'required' ,
            'sms_code'  => 'required' ,
            'invite_code'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $role_range = config('business.role');
        if (!in_array($param['role'] , $role_range)) {
            return self::error('不支持得角色类型，当前受支持的角色类型有' . implode(',' , $role_range));
        }
        // 检查短信验证码
        $sms_code = SmsCodeModel::findByIdentifierAndAreaCodeAndPhoneAndType($base->identifier , $param['area_code'] , $param['phone'] , 1);
        if (empty($sms_code)) {
            return self::error('请先发送短信验证码');
        }
        if (strtotime($sms_code->update_time) + config('app.code_duration') < time()) {
            return self::error('验证码已经过期');
        }
        if ($sms_code->code != $param['sms_code']) {
            return self::error('短信验证码不正确');
        }
        // 检查手机号码是否被使用过
        $user = UserModel::findByIdentifierAndAreaCodeAndPhone($base->identifier , $param['area_code'] , $param['phone']);
        if (!empty($user)) {
            return self::error('该手机号码已经注册，请直接登录');
        }
        $referrer = null;
        if (empty($param['invite_code'])) {
            return self::error('invite_code is required');
        }
        $referrer = UserModel::findByIdentifierAndInviteCode($base->identifier , $param['invite_code']);
        if (empty($referrer)) {
            return self::error('邀请码错误，未找到该邀请码对应的用户');
        }
        $param['p_id'] = $referrer->id;
        $param['invite_code_copy'] = $param['invite_code'];
        $param['invite_code'] = md5($param['phone']);
        $param['unique_code'] = MiscUtil::uniqueCode();
        $param['full_phone'] = sprintf('%s%s' , $param['area_code'] , $param['phone']);
        $param['identifier'] = $base->identifier;
        $param['aes_key'] = random(16 , 'mixed' , true);
        try {
            DB::beginTransaction();
            $id = UserModel::insertGetId(array_unit($param , [
                'area_code' ,
                'phone' ,
                'p_id' ,
                'invite_code' ,
                'unique_code' ,
                'full_phone' ,
                'identifier' ,
                'nickname' ,
                'aes_key' ,
            ]));
            UserOptionModel::insertGetId([
                'user_id' => $id ,
            ]);
            // 短信验证码标记为已经使用
            SmsCodeModel::updateById($sms_code->id , [
                'used' => 1
            ]);
            FriendModel::u_insertGetId($id , $param['p_id']);
            FriendModel::u_insertGetId($param['p_id'] , $id);
            // 自动添加客服为好友（这边默认每个项目仅会有一个客服）
            $system_user = UserModel::systemUser($base->identifier);
            FriendModel::u_insertGetId($id , $system_user->id);
            FriendModel::u_insertGetId($system_user->id , $id);
            // 新增用户添加方式选项
            $join_friend_method = JoinFriendMethodModel::getAll();
            foreach ($join_friend_method as $v)
            {
                UserJoinFriendOptionModel::insertGetId([
                    'join_friend_method_id' => $v->id ,
                    'user_id' => $id ,
                    'enable' => 1 ,
                ]);
            }
            DB::commit();
            PushUtil::multiple($base->identifier , [$id , $param['p_id']] , 'refresh_friend');
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}