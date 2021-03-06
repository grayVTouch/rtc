<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:39
 */

namespace App\WebSocket\Action;

use App\Lib\SMS\Zz253;
use App\Model\GroupMemberModel;
use App\Model\GroupModel;
use App\Model\JoinFriendMethodModel;
use App\Model\SmsCodeModel;
use App\Model\UserInfoModel;
use App\Model\UserJoinFriendOptionModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use App\Model\UserTokenModel;
use App\Redis\UserRedis;
use App\Util\MiscUtil;
use App\WebSocket\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Hash;
use Core\Lib\Validator;
use App\WebSocket\Base;
use function core\random;
use Exception;
use Illuminate\Support\Facades\DB;

use App\Util\UserUtil as BaseUserUtil;

class LoginAction extends Action
{
    // 远程登录
    public static function loginUseUniqueCode(Base $base , array $param)
    {
        if (!config('app.enable_guest')) {
            // 未启用旅客模式
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
                return self::error('未找到当前提供的 unique_code 对应的用户' , 404);
            }
        } else {
            // 旅客模式
            $user = UserModel::findByUniqueCode($param['unique_code']);
            if (empty($user)) {
                // 自动分配用户
                $user = UserUtil::createTempUser($base->identifier);
                if (empty($user)) {
                    return self::error('创建访客账号失败' , 500);
                }
            }
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserTokenModel::u_insertGetId($param['identifier'] , $param['user_id'] , $param['token'] , $param['expire']);

        // 绑定 user_id <=> fd
//        var_dump('当前登录的客户端链接 fd：' . $base->fd);
        UserRedis::userIdMappingFd($base->identifier , $user->id , $base->fd);
        UserRedis::fdMappingUserId($base->identifier , $base->fd , $user->id);
        if ($user->role == 'admin') {
            // 工作人员
            // 登录成功后消费未读游客消息（平台咨询）队列
            UserUtil::consumeUnhandleMsg($user);
        } else {
            /*
             * todo 由用户手动发起平台资讯
            // 平台用户
            // 初始化咨询通道
            UserUtil::initAdvoiseGroup($user->id);
            // 自动分配客服
            $group = Group::advoiseGroupByUserId($user->id);
            // 检查是否分配过客服
            $bind_waiter = UserRedis::groupBindWaiter($base->identifier , $group->id);
            if (empty($bind_waiter)) {
                $res = UserUtil::allocateWaiter($user->id);
                if ($res['code'] != 200) {
                    var_dump($res['data']);
                    UserUtil::noWaiterTip($base->identifier , $user->id , $group->id);
                }
            }
            */
        }
        // 推送一条未读消息数量
        return self::success($param['token']);
    }

    public static function loginUseUsername(Base $base , array $param)
    {
        if (!config('app.enable_guest')) {
            // 未启用旅客模式
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
            $user = UserModel::findByIdentifierAndUsername($base->identifier , $param['username']);
            if (empty($user)) {
                return self::error([
                    'username' => '未找到当前提供的 username 对应的用户' ,
                ]);
            }
        } else {
            // 旅客模式
            $user = UserModel::findByIdentifierAndUsername($base->identifier , $param['username']);
            if (empty($user)) {
                // 自动分配用户
                $user = UserUtil::createTempUser($base->identifier);
                if (empty($user)) {
                    return self::error('创建访客账号失败' , 500);
                }
            }
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        UserTokenModel::u_insertGetId($param['identifier'] , $param['user_id'] , $param['token'] , $param['expire']);

        // 绑定 user_id <=> fd
//        var_dump('当前登录的客户端链接 fd：' . $base->fd);
        UserRedis::userIdMappingFd($base->identifier , $user->id , $base->fd);
        UserRedis::fdMappingUserId($base->identifier , $base->fd , $user->id);
        if ($user->role == 'admin') {
            // 工作人员
            // 登录成功后消费未读游客消息（平台咨询）队列
            UserUtil::consumeUnhandleMsg($user);
        } else {
            /*
             * todo 由用户手动发起平台资讯
            // 平台用户
            // 初始化咨询通道
            UserUtil::initAdvoiseGroup($user->id);
            // 自动分配客服
            $group = Group::advoiseGroupByUserId($user->id);
            // 检查是否分配过客服
            $bind_waiter = UserRedis::groupBindWaiter($base->identifier , $group->id);
            if (empty($bind_waiter)) {
                $res = UserUtil::allocateWaiter($user->id);
                if ($res['code'] != 200) {
//                    var_dump($res['data']);
                    UserUtil::noWaiterTip($base->identifier , $user->id , $group->id);
                }
            }
            */
        }
        // 推送一条未读消息数量
        return self::success($param['token']);
    }

    public static function loginUsePhone(Base $base , array $param)
    {
        // 未启用旅客模式
        $validator = Validator::make($param , [
            'area_code'    => 'required' ,
            'phone'    => 'required' ,
            'sms_code'    => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查短信验证码
        $sms_code = SmsCodeModel::findByIdentifierAndAreaCodeAndPhoneAndType($base->identifier , $param['area_code'] , $param['phone'] , 2);
        if (empty($sms_code)) {
            return self::error('请先发送短信验证码');
        }
        if (strtotime($sms_code->update_time) + config('app.code_duration') < time()) {
            return self::error('请先发送短信验证码');
        }
        if ($sms_code->code != $param['sms_code']) {
            return self::error('短信验证码不正确');
        }
        $user = UserModel::findByIdentifierAndAreaCodeAndPhone($base->identifier , $param['area_code'] , $param['phone']);
        if (empty($user)) {
            return self::error('手机号未注册');
        }
        // 登录成功
        $param['identifier'] = $user->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        try {
            DB::beginTransaction();
            UserTokenModel::u_insertGetId($param['user_id'] , $param['token'] , $param['expire']);
            // 上线通知
            $online = UserRedis::isOnline($base->identifier , $user->id);
            BaseUserUtil::mapping($base->identifier , $user->id , $base->fd);
            if (!$online) {
                // 之前如果不在线，现在上线，那么推送更新
                BaseUserUtil::onlineStatusChange($base->identifier , $param['user_id'] , 'online');
            }
            if ($user->role == 'admin') {
                // 工作人员登陆后，消费未读消息
                UserUtil::consumeUnhandleMsg($user);
            }
            // 短信验证码标记为已经使用
            SmsCodeModel::updateById($sms_code->id , [
                'used' => 1
            ]);
            DB::commit();
            // 推送一条未读消息数量
            return self::success([
                'user_id'   => $param['user_id'] ,
                'token'     => $param['token']
            ]);
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function registerUsePhone(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'role'      => 'required' ,
            'area_code' => 'required' ,
            'phone'     => 'required' ,
            'password'  => 'required' ,
            'confirm_password'  => 'required' ,
            'sms_code'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $role_range = config('business.role');
        if (!in_array($param['role'] , $role_range)) {
            return self::error([
                'role' => '不支持得角色类型，当前受支持的角色类型有' . implode(',' , $role_range) ,
            ]);
        }
        if ($param['password'] != $param['confirm_password']) {
            return self::error('两次输入的密码不一致' , 401);
        }

        // 检查短信验证码
        $sms_code = SmsCodeModel::findByIdentifierAndAreaCodeAndPhoneAndType($base->identifier , $param['area_code'] , $param['phone'] , 1);
        if (empty($sms_code)) {
            return self::error([
                'sms_code' => '请先发送短信验证码' ,
            ]);
        }
        if (strtotime($sms_code->update_time) + config('app.code_duration') < time()) {
            return self::error([
                'sms_code' => '验证码已经过期' ,
            ]);
        }
        if ($sms_code->code != $param['sms_code']) {
            return self::error([
                'sms_code' => '短信验证码不正确' ,
            ]);
        }
        // 检查手机号码是否被使用过
        $user = UserModel::findByIdentifierAndAreaCodeAndPhone($base->identifier , $param['area_code'] , $param['phone']);
        if (!empty($user)) {
            return self::error([
                'phone' => '该手机号码已经注册，请直接登录' ,
            ]);
        }
        if (!empty($param['invite_code'])) {
            $referrer = UserModel::findByIdentifierAndInviteCode($base->identifier , $param['invite_code']);
            if (empty($referrer)) {
                return self::error([
                    'invite_code' => '邀请码错误，未找到该邀请码对应的用户' ,
                ]);
            }
            $param['p_id'] = $referrer->id;
        } else {
            $param['p_id'] = 0;
        }
        $param['invite_code_copy'] = $param['invite_code'];
        $param['password'] = Hash::make($param['password']);
        $param['invite_code'] = md5($param['phone']);
        $param['unique_code'] = MiscUtil::uniqueCode();
        $param['full_phone'] = sprintf('%s%s' , $param['area_code'] , $param['phone']);
        $param['identifier'] = $base->identifier;
        try {
            DB::beginTransaction();
            $id = UserModel::insertGetId(array_unit($param , [
                'area_code' ,
                'phone' ,
                'password' ,
                'p_id' ,
                'invite_code' ,
                'unique_code' ,
                'full_phone' ,
                'identifier' ,
            ]));
            UserOptionModel::insertGetId([
                'user_id' => $id ,
            ]);
            DB::commit();
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function registerUsePhoneV1(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'role'      => 'required' ,
            'area_code' => 'required' ,
            'phone'     => 'required' ,
            'nickname'  => 'required' ,
            'sms_code'  => 'required' ,
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
        if (!empty($param['invite_code'])) {
            $referrer = UserModel::findByIdentifierAndInviteCode($base->identifier , $param['invite_code']);
            if (empty($referrer)) {
                return self::error('邀请码错误，未找到该邀请码对应的用户');
            }
            $param['p_id'] = $referrer->id;
        } else {
            $param['p_id'] = 0;
        }
        $param['invite_code_copy'] = $param['invite_code'];
        $param['invite_code'] = md5($param['phone']);
        $param['unique_code'] = MiscUtil::uniqueCode();
        $param['full_phone'] = sprintf('%s%s' , $param['area_code'] , $param['phone']);
        $param['identifier'] = $base->identifier;
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
            ]));
            UserOptionModel::insertGetId([
                'user_id' => $id ,
            ]);
            // 短信验证码标记为已经使用
            SmsCodeModel::updateById($sms_code->id , [
                'used' => 1
            ]);
            // 创建平台咨询的群（用户注册后自动创建官方客服群）
            $group_id = GroupModel::insertGetId([
                'user_id' => $id ,
                // 群名称
                'name' => config('app.customer_channel_name') ,
                // 是否是临时群
                'is_temp' => 0 ,
                // 是否是客服群
                'is_service' => 1 ,
            ]);
            // 将系统用户加入该群
            $system_user = UserModel::systemUser($base->identifier);
            GroupMemberModel::u_insertGetId($id , $group_id);
            GroupMemberModel::u_insertGetId($system_user->id , $group_id);

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
            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
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

    // 昵称
    public static function nickname(Base $base , array $param)
    {
        $limit = empty($param['limit']) ? 20 : $param['limit'];
        $res = [];
        for ($i = 1; $i <= $limit; ++$i)
        {
            $res[] = random(11 , 'letter' , true);
        }
        return self::success($res);
    }
}