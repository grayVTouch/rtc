<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 9:39
 */

namespace App\WebSocket\V1\Action;

use App\Lib\Push\AppPush;
use App\WebSocket\V1\Data\UserData;
use App\Lib\SMS\Zz253;
use App\WebSocket\V1\Model\BindDeviceModel;
use App\WebSocket\V1\Model\FriendModel;
use App\WebSocket\V1\Model\GroupMemberModel;
use App\WebSocket\V1\Model\GroupModel;
use App\WebSocket\V1\Model\JoinFriendMethodModel;
use App\WebSocket\V1\Model\PushModel;
use App\WebSocket\V1\Model\PushReadStatusModel;
use App\WebSocket\V1\Model\SmsCodeModel;
use App\WebSocket\V1\Model\SystemParamModel;
use App\WebSocket\V1\Model\UserInfoModel;
use App\WebSocket\V1\Model\UserJoinFriendOptionModel;
use App\WebSocket\V1\Model\UserActivityLogModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Model\UserOptionModel;
use App\WebSocket\V1\Model\UserTokenModel;
use App\WebSocket\V1\Redis\CacheRedis;
use App\WebSocket\V1\Redis\UserRedis;
use App\WebSocket\V1\Util\MiscUtil;
use App\WebSocket\V1\Util\SessionUtil;
use App\WebSocket\V1\Util\CaptchaUtil;
use App\WebSocket\V1\Util\UserActivityLogUtil;
use App\WebSocket\V1\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Hash;
use Core\Lib\Validator;
use App\WebSocket\V1\Controller\Base;
use function core\random;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Exception;
use function extra\check_phone;
use GeetestLib;
use Illuminate\Support\Facades\DB;

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
        UserTokenModel::u_insertGetId($base->identifier , $param['user_id'] , $param['token'] , $param['expire'] , $base->platform);

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
                if ($res['code'] != 0) {
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
        UserTokenModel::u_insertGetId($base->identifier , $param['user_id'] , $param['token'] , $param['expire'] , $base->platform);

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
                if ($res['code'] != 0) {
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
//            'verify_code'    => 'required' ,
//            'verify_code_key'    => 'required' ,
            // 设备验证
            'device_code'    => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = UserModel::findByIdentifierAndAreaCodeAndPhone($base->identifier , $param['area_code'] , $param['phone']);
        if (empty($user)) {
            return self::error('手机号未注册');
        }
        /**
         * ***********************************
         * 极限验证
         * ***********************************
         */
        if ($user->is_system != 1) {
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
        }
        var_dump('获取到的 device_code: ' . $param['device_code'] . '; 获取到的 gt_verify: ' . $param['gt_verify']);
        if (config('app.enable_gt')) {
            // 开启了极验验证
            $support_gt_platform = config('business.support_gt_platform');
            if (in_array($base->platform , $support_gt_platform)) {
                // 没有提供 challenge，检查用户是否已经绑定过设备
                if ($param['gt_verify'] == '') {
                    // 验证设备是否绑定该账号
                    $bind_device = BindDeviceModel::findByUserIdAndDevice($user->id , $param['device_code']);
                    if (empty($bind_device)) {
                        return self::error('未绑定设备标识符,device_code:' . $param['device_code'] , 800);
                    }
                } else {
                    if ($param['gt_verify'] != 1) {
                        return self::error('请先通过极验验证');
                    }
                }

//                if (empty($param['challenge'])) {
//
//                } else {
                // 如果提供了 challenge，检查 极验验证结果是否正确
//                    $gt_check_key = 'gt_check_' . $param['challenge'];
//                    $cache = CacheRedis::value($gt_check_key);
//                    if (empty($cache)) {
//                        return self::error('请先创建极验验证' , 700);
//                    }
//                    if ($cache == 'error') {
//                        return self::error('请先通过极验验证' , 700);
//                    }
//                    CacheRedis::del($gt_check_key);
//                }
            }
        } else {
            // 没有开启极验验证 只进行普通的图形验证码 验证
            $res = CaptchaUtil::check($param['verify_code'] , $param['verify_code_key']);
            if ($res['code'] != 0) {
                return self::error($res['data']);
            }
        }

        // 新增验证码
        // 登录成功
        $param['identifier'] = $base->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
//        $deny_platform_for_push = config('business.deny_platform_for_push');
//        if (!in_array($base->platform , $deny_platform_for_push)) {
//            // app 推送绑定
//            $res = AppPush::sync($base->platform , $user->id , $param['device_code']);
//            if ($res['code'] != 200) {
//                return self::error($res['data'] , 500);
//            }
//        }
        try {
            DB::beginTransaction();
            // 先检查当前登录平台是否是非 pc 浏览器
            // 如果时非 pc 浏览器，那么将其他有效的 token 删除
            // 或让其等价于 无效
            // 这是为了保证 同一平台仅 允许 单个设备登录
            $single_device_for_platform = config('business.single_device_for_platform');
            if (in_array($base->platform , $single_device_for_platform)) {
                // 删除掉其他 token
                UserTokenModel::delByUserIdAndPlatform($user->id , $base->platform);
            }
            UserRedis::fdMappingPlatform($base->identifier , $base->fd , $base->platform);
            UserTokenModel::u_insertGetId($base->identifier , $param['user_id'] , $param['token'] , $param['expire'] , $base->platform);
            // 上线通知
            $online = UserRedis::isOnline($base->identifier , $user->id);
            UserUtil::mapping($base->identifier , $user->id , $base->fd);
            $date = date('Y-m-d');
            if (!$online) {
                // 之前如果不在线，现在上线，那么推送更新
                UserUtil::onlineStatusChange($base->identifier , $param['user_id'] , 'online');
                // 新增在线次数
                UserActivityLogUtil::createOrUpdateCountByIdentifierAndUserIdAndDateAndData($base->identifier , $user->id , $date , [
                    'online_count' => 'inc'
                ]);
            }
            if ($user->role == 'admin') {
                // 工作人员登陆后，消费未读消息
//                UserUtil::consumeUnhandleMsg($user);
            }
            // 短信验证码标记为已经使用
            if ($user->is_system != 1) {
                SmsCodeModel::updateById($sms_code->id , [
                    'used' => 1
                ]);
            }
            // 新增登录次数
            UserActivityLogUtil::createOrUpdateCountByIdentifierAndUserIdAndDateAndData($base->identifier , $user->id , $date , [
                'login_count' => 'inc'
            ]);
            if (config('app.enable_gt')) {
                $bind_device = BindDeviceModel::findByUserIdAndDevice($user->id , $param['device_code']);
                if (empty($bind_device)) {
                    // 如果启用了极验验证
                    BindDeviceModel::insertGetId([
                        'user_id' => $user->id ,
                        'device_code' => $param['device_code'] ,
                        'platform' => $base->platform ,
                        'identifier' => $base->identifier ,
                    ]);
                }
            }
            DB::commit();
            if (in_array($base->platform , $single_device_for_platform)) {
                // 通知其他客户端你已经被迫下线
                $clients = UserRedis::userIdMappingFd($base->identifier , $user->id);
                $exclude = [];
                $extranet_ip = config('app.extranet_ip');
                foreach ($clients as $v)
                {
                    // 检查平台
                    $platform = UserRedis::fdMappingPlatform($base->identifier , $v['client_id']);
                    if (!in_array($platform , $single_device_for_platform)) {
                        $exclude[] = $v;
                        continue ;
                    }
                    if ($v['extranet_ip'] == $extranet_ip && $v['client_id'] == $base->fd) {
                        $exclude[] = $v;
                        continue ;
                    }
                }
                $base->push($user->id , 'forced_offline' , '' , $exclude);
            }
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

    public static function loginUseUsernameV1(Base $base , array $param)
    {
        // 未启用旅客模式
        $validator = Validator::make($param , [
            'username'    => 'required' ,
            'password'    => 'required' ,
//            'verify_code'    => 'required' ,
//            'verify_code_key'    => 'required' ,

            'device_code' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }

        $user = UserModel::findByIdentifierAndUsername($base->identifier , $param['username']);
        if (empty($user)) {
            return self::error('用户名未注册');
        }
        if (!Hash::check($param['password'] , $user->password)) {
            return self::error('密码错误');
        }
        var_dump('获取到的 device_code: ' . $param['device_code'] . '; 获取到的 gt_verify: ' . $param['gt_verify']);
        if (config('app.enable_gt')) {
            // 开启了极验验证
            $support_gt_platform = config('business.support_gt_platform');
            if (in_array($base->platform , $support_gt_platform)) {
                if ($param['gt_verify'] == '') {
                    // 验证设备是否绑定该账号
                    $bind_device = BindDeviceModel::findByUserIdAndDevice($user->id , $param['device_code']);
                    if (empty($bind_device)) {
                        return self::error('未绑定设备标识符,device_code:' . $param['device_code'] , 800);
                    }
                } else {
                    if ($param['gt_verify'] != 1) {
                        return self::error('请先通过极验验证');
                    }
                }
//                if (empty($param['challenge'])) {
//
//                } else {
                // 如果提供了 challenge，检查 极验验证结果是否正确
//                    $gt_check_key = 'gt_check_' . $param['challenge'];
//                    $cache = CacheRedis::value($gt_check_key);
//                    if (empty($cache)) {
//                        return self::error('请先创建极验验证' , 700);
//                    }
//                    if ($cache == 'error') {
//                        return self::error('请先通过极验验证' , 700);
//                    }
//                    CacheRedis::del($gt_check_key);
//                }
            }
        } else {
            // 没有开启极验验证 只进行普通的图形验证码 验证
            $res = CaptchaUtil::check($param['verify_code'] , $param['verify_code_key']);
            if ($res['code'] != 0) {
                return self::error($res['data']);
            }
        }

        // 登录成功
        $param['identifier'] = $base->identifier;
        $param['user_id'] = $user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
//        $deny_platform_for_push = config('business.deny_platform_for_push');
//        if (!in_array($base->platform , $deny_platform_for_push)) {
//            // app 推送绑定
//            $res = AppPush::sync($base->platform , $user->id , $param['device_code']);
//            if ($res['code'] != 200) {
//                return self::error($res['data'] , 500);
//            }
//        }
        try {
            DB::beginTransaction();
            // 先检查当前登录平台是否是非 pc 浏览器
            // 如果时非 pc 浏览器，那么将其他有效的 token 删除
            // 或让其等价于 无效
            // 这是为了保证 同一平台仅 允许 单个设备登录
            $single_device_for_platform = config('business.single_device_for_platform');
            if (in_array($base->platform , $single_device_for_platform)) {
                // 删除掉其他 token
                UserTokenModel::delByUserIdAndPlatform($user->id , $base->platform);
            }
            UserRedis::fdMappingPlatform($base->identifier , $base->fd , $base->platform);
            UserTokenModel::u_insertGetId($base->identifier , $param['user_id'] , $param['token'] , $param['expire'] , $base->platform);
            // 上线通知
            $online = UserRedis::isOnline($base->identifier , $user->id);
            UserUtil::mapping($base->identifier , $user->id , $base->fd);
            $date = date('Y-m-d');
            if (!$online) {
                // 之前如果不在线，现在上线，那么推送更新
                UserUtil::onlineStatusChange($base->identifier , $param['user_id'] , 'online');
                // 新增在线次数
                UserActivityLogUtil::createOrUpdateCountByIdentifierAndUserIdAndDateAndData($base->identifier , $user->id , $date , [
                    'online_count' => 'inc'
                ]);
            }
            if ($user->role == 'admin') {
                // 工作人员登陆后，消费未读消息
//                UserUtil::consumeUnhandleMsg($user);
            }
            // 新增在线次数
            UserActivityLogUtil::createOrUpdateCountByIdentifierAndUserIdAndDateAndData($base->identifier , $user->id , $date , [
                'login_count' => 'inc'
            ]);
            if (config('app.enable_gt')) {
                $bind_device = BindDeviceModel::findByUserIdAndDevice($user->id , $param['device_code']);
                if (empty($bind_device)) {
                    // 如果启用了极验验证
                    BindDeviceModel::insertGetId([
                        'user_id' => $user->id ,
                        'device_code' => $param['device_code'] ,
                        'platform' => $base->platform ,
                        'identifier' => $base->identifier ,
                    ]);
                }
            }
            DB::commit();
            if (in_array($base->platform , $single_device_for_platform)) {
                // 通知其他客户端你已经被迫下线
                $clients = UserRedis::userIdMappingFd($base->identifier , $user->id);
                $exclude = [];
                $extranet_ip = config('app.extranet_ip');
                foreach ($clients as $v)
                {
                    // 检查平台
                    $platform = UserRedis::fdMappingPlatform($base->identifier , $v['client_id']);
                    if (!in_array($platform , $single_device_for_platform)) {
                        $exclude[] = $v;
                        continue ;
                    }
                    if ($v['extranet_ip'] == $extranet_ip && $v['client_id'] == $base->fd) {
                        $exclude[] = $v;
                        continue ;
                    }
                }
                $base->push($user->id , 'forced_offline' , '' , $exclude);
            }
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
                'sms_code' => '短信验证码已经过期' ,
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
            // 注册成功
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
            'sms_code'  => 'required' ,
            'nickname'  => 'required' ,
            'verify_code'  => 'required' ,
            'verify_code_key'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查图形验证码是否正确
        $res = CaptchaUtil::check($param['verify_code'] , $param['verify_code_key']);
        if ($res['code'] != 0) {
            return self::error($res['data']);
        }
        // 检查昵称是否正确
//        $reg_for_nickname = "/[\ud83c\udc00-\ud83c\udfff]|[\ud83d\udc00-\ud83d\udfff]|[\u2600-\u27ff]+/";
//        if (preg_match($reg_for_nickname , $param['nickname']) > 0) {
//            return self::error('昵称不符合格式，请提供正确的昵称');
//        }
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
            // 自动添加客服为好友（这边默认每个项目仅会有一个客服）
            $system_user = UserModel::systemUser($base->identifier);
            FriendModel::u_insertGetId($base->identifier , $id , $system_user->id);
            FriendModel::u_insertGetId($base->identifier , $system_user->id , $id);
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
            // 新用户注册推送
            $new_user_notification = config('app.new_user_notification');
            $system = 'system';
            $push_id = PushModel::u_insertGetId($base->identifier , 'single' , $system , '' , $id , $new_user_notification , $new_user_notification , $new_user_notification , 0);
            PushReadStatusModel::u_insertGetId($base->identifier , $id , $push_id , $system , 0);
            SessionUtil::createOrUpdate($base->identifier , $id , $system , '');
            DB::commit();

            return self::success();
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function registerUseUsername(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'role'      => 'required' ,
            'username'     => 'required' ,
            'password'     => 'required' ,
            'confirm_password' => 'required' ,
            'nickname'  => 'required' ,
            'verify_code'  => 'required' ,
            'verify_code_key'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查用户名
        if (preg_match('/[A-z]\w{5,}/' , $param['username']) < 1) {
            return self::error('账号错误（字母开头，长度不能低于6位）');
        }
        // 昵称要过滤掉表情
        $reg_for_nickname = "//";
        // 检查图形验证码是否正确
        $res = CaptchaUtil::check($param['verify_code'] , $param['verify_code_key']);
        if ($res['code'] != 0) {
            return self::error($res['data']);
        }
        $role_range = config('business.role');
        if (!in_array($param['role'] , $role_range)) {
            return self::error('不支持得角色类型，当前受支持的角色类型有' . implode(',' , $role_range));
        }
        // 检查手机号码是否被使用过
        $user = UserModel::findByIdentifierAndUsername($base->identifier , $param['username']);
        if (!empty($user)) {
            return self::error('该用户名已经被注册，请直接登录');
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
        $param['invite_code'] = md5($param['username']);
        $param['unique_code'] = MiscUtil::uniqueCode();
        $param['identifier'] = $base->identifier;
        $param['aes_key'] = random(16 , 'mixed' , true);
        $param['password'] = Hash::make($param['password']);
        try {
            DB::beginTransaction();
            $id = UserModel::insertGetId(array_unit($param , [
                'area_code' ,
                'p_id' ,
                'invite_code' ,
                'unique_code' ,
                'full_phone' ,
                'identifier' ,
                'nickname' ,
                'aes_key' ,
                'username' ,
                'password' ,
            ]));
            UserOptionModel::insertGetId([
                'user_id' => $id ,
            ]);
            // 自动添加客服为好友（这边默认每个项目仅会有一个客服）
            $system_user = UserModel::systemUser($base->identifier);
            FriendModel::u_insertGetId($base->identifier , $id , $system_user->id);
            FriendModel::u_insertGetId($base->identifier , $system_user->id , $id);
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
            // 新用户注册推送
            $new_user_notification = config('app.new_user_notification');
            $system = 'system';
            $push_id = PushModel::u_insertGetId($base->identifier , 'single' , $system , '' , $id , $new_user_notification , $new_user_notification , $new_user_notification , 0);
            PushReadStatusModel::u_insertGetId($base->identifier , $id , $push_id , $system , 0);
            SessionUtil::createOrUpdate($base->identifier , $id , $system , '');
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
        // 对手机号码格式做一个判断
        if (!check_phone($param['phone'] , false)) {
            return self::error("请提供正确的手机号码（11位数字）");
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

    public static function captcha(Base $base , array $param)
    {
        $res = CaptchaUtil::make();
        if ($res['code'] != 0) {
            return self::error($res['data']);
        }
        return self::success($res['data']);
    }

    // 忘记密码
    public static function forgetPassword(Base $base , array $param)
    {
        $validator = Validator::make($param , [
            'area_code'     => 'required' ,
            'phone'         => 'required' ,
            'password'     => 'required' ,
            'confirm_password' => 'required' ,
            'verify_code'  => 'required' ,
            'verify_code_key'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查图形验证码是否正确
        $res = CaptchaUtil::check($param['verify_code'] , $param['verify_code_key']);
        if ($res['code'] != 0) {
            return self::error($res['data']);
        }
        // 检查短信验证码
        $sms_code = SmsCodeModel::findByIdentifierAndAreaCodeAndPhoneAndType($base->identifier , $param['area_code'] , $param['phone'] , 5);
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
            return self::error('账号不存在');
        }
        $password = Hash::make($param['password']);
        UserData::updateByIdentifierAndIdAndData($base->identifier , $user->id , [
            'password' => $password
        ]);
        return self::success();
    }

    public static function registerValidateSession(Base $base , array $param)
    {
        // 生成一个网站用户id
        $user_id_for_gt = random(12 , 'mixed' , true);
        $client_ip = '127.0.0.1';
        $GtSdk = new GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $data = [
            // 网站用户id
            "user_id"       => $user_id_for_gt ,
            // web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "client_type"   => "web" ,
            // 请在此处传输用户请求验证时所携带的IP
            "ip_address"    => $client_ip
        ];
        $status = $GtSdk->pre_process($data, 1);
        $res = $GtSdk->get_response();
        $data = [
            // 用户id 用于二次认证的时候确认是同一个用户用
            'user_id_for_gt' => $user_id_for_gt ,
            'ip_for_gt' => '127.0.0.1' ,
            'gt_server_status' => $status ,

            /**
             * 以下参数是第三方极限验证提供的相应参数
             */
            'gt' => $res['gt'] ,
            'challenge' => $res['challenge'] ,
            'new_captcha' => $res['new_captcha'] ,
            'success' => $res['success'] ,
        ];
        return self::success($data);
    }

    public static function validateSession(Base $base , array $param)
    {

    }

    // 二维码数据
    public static function loginQRCode(Base $base , array $param)
    {
        $download = config('app.app_download');
        $data = [
            // web 端授权登录
            'type'  => 'web_login' ,
            'data'  => [
                'identifier'    => $base->identifier ,
                'extranet_ip'   => config('app.extranet_ip') ,
                'client_id'     => $base->fd
            ] ,
        ];
        $base64 = base64_encode(json_encode($data));
        $qrcode_data = sprintf('%s?identity=%s&data=%s' , $download , $base->identifier , $base64);

        $qrCode = new QrCode($qrcode_data);
        $qrCode->setSize(430);
        $qrCode->setWriterByName('png');

//        $qrCode->setMargin(10);
//        $qrCode->setEncoding('UTF-8');
//        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
//        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
//        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
//        $qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
//        $qrCode->setLogoSize(150, 0);
//        $qrCode->setRoundBlockSize(true);
//        $qrCode->setValidateResult(false);
//        $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);

        $image_data = $qrCode->writeString();
        $image_base64 = base64_encode($image_data);
        $image_base64 = sprintf("data:image/png;base64,%s" , $image_base64);
        return self::success($image_base64);
    }

    // 二维码数据
    public static function loginQRCodeForTest(Base $base , array $param)
    {
        $qrcode_data = 'http://websocket.hichatvip.com:10010/Web/Authorization/auth?identifier=nimo&client_id=' . $base->fd;
        $qr_code = new QrCode($qrcode_data);
        $qr_code->setSize(430);
        $qr_code->setWriterByName('png');

//        $qrCode->setMargin(10);
//        $qrCode->setEncoding('UTF-8');
//        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
//        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
//        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
//        $qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
//        $qrCode->setLogoSize(150, 0);
//        $qrCode->setRoundBlockSize(true);
//        $qrCode->setValidateResult(false);
//        $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);

        $image_data = $qr_code->writeString();
        $image_base64 = base64_encode($image_data);
        $image_base64 = sprintf("data:image/png;base64,%s" , $image_base64);
        return self::success($image_base64);
    }


}