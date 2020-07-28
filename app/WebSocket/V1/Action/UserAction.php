<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:54
 */

namespace App\WebSocket\V1\Action;


use App\WebSocket\V1\Data\UserData;
use App\WebSocket\V1\Data\UserJoinFriendOptionData;
use App\WebSocket\V1\Data\UserOptionData;
use App\Lib\Push\AppPush;
use App\WebSocket\V1\Model\ApplicationModel;
use App\WebSocket\V1\Model\BlacklistModel;
use App\WebSocket\V1\Model\FriendModel;
use App\WebSocket\V1\Model\GroupModel;
use App\WebSocket\V1\Model\JoinFriendMethodModel;
use App\WebSocket\V1\Model\SessionModel;
use App\WebSocket\V1\Model\SmsCodeModel;
use App\WebSocket\V1\Model\SystemParamModel;
use App\WebSocket\V1\Model\UserJoinFriendOptionModel;
use App\WebSocket\V1\Model\UserModel;
use App\WebSocket\V1\Model\UserOptionModel;
use App\WebSocket\V1\Model\UserTokenModel;
use App\WebSocket\V1\Redis\UserRedis;
use App\WebSocket\V1\Util\ChatUtil;
use App\WebSocket\V1\Util\GroupUtil;
use App\WebSocket\V1\Util\MiscUtil;
use App\WebSocket\V1\Util\PageUtil;
use App\WebSocket\V1\Util\SessionUtil;
use App\WebSocket\V1\Controller\Auth;
use App\WebSocket\V1\Util\UserActivityLogUtil;
use App\WebSocket\V1\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Hash;
use Core\Lib\Validator;
use Engine\Facade\WebSocket;
use Exception;
use Illuminate\Support\Facades\DB;


class UserAction extends Action
{
    // 咨询通道绑定的群信息
    public static function groupForAdvoise(Auth $auth , array $param)
    {
        $group = GroupModel::advoiseGroupByUserId($auth->user->id);
        return self::success($group);
    }

    // 申请记录
    public static function app(Auth $auth , array $param)
    {
        $param['page'] = empty($param['page']) ? config('app.page') : $param['page'];
        $param['limit'] = empty($param['limit']) ? config('app.limit') : $param['limit'];
        $total = ApplicationModel::countByUserId($auth->user->id);
        $page = PageUtil::deal($total , $param['page'] , $param['limit']);
        $res = ApplicationModel::listByUserId($auth->user->id , $page['offset'] , $param['limit']);

        foreach ($res as $v)
        {
            UserUtil::handle($v->user);
            if ($v->type == 'group') {
                // 群聊
                $v->group = GroupModel::findById($v->group_id);
                GroupUtil::handle($v->group , $auth->user->id);
            }
            if ($v->type == 'private') {
                // 私聊
                $v->relation_user = UserModel::findById($v->relation_user_id);
                UserUtil::handle($v->relation_user);
            }
        }
        $res = PageUtil::data($page , $res);
        return self::success($res);
    }

    public static function editUserInfo(Auth $auth , array $param)
    {
        $param['avatar'] = $param['avatar'] === '' ? $auth->user->avatar : $param['avatar'];
        $param['sex'] = $param['sex'] === '' ? $auth->user->sex : $param['sex'];
        $param['birthday'] = $param['birthday'] === '' ? $auth->user->birthday : $param['birthday'];
        $param['nickname'] = $param['nickname'] === '' ? $auth->user->nickname : $param['nickname'];
        $param['signature'] = $param['signature'] === '' ? $auth->user->signature : $param['signature'];
//        $param['friend_circle_background'] = $param['friend_circle_background'] === '' ? $auth->user->signature : $param['signature'];
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , array_unit($param , [
            'avatar' ,
            'sex' ,
            'birthday' ,
            'nickname' ,
            'signature' ,
//            'friend_circle_background' ,
        ]));
        return self::success();
    }

    // 搜索好友
    public static function search(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'keyword' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = [];
        if ($user_use_id = UserModel::findById($param['keyword'])) {
            UserUtil::handle($user_use_id);
            $res[] = $user_use_id;
        }
        if ($user_use_nickname = UserModel::findByIdentifierAndNickname($auth->identifier , $param['keyword'])) {
            UserUtil::handle($user_use_nickname);
            $res[] = $user_use_nickname;
        }
        if ($user_use_phone = UserModel::findByIdentifierAndPhone($auth->identifier , $param['keyword'])) {
            UserUtil::handle($user_use_phone);
            $res[] = $user_use_phone;
        }
        return self::success($res);
    }

    public static function mapping(Auth $auth , array $param)
    {
        UserRedis::userIdMappingFd($auth->identifier , $auth->user->id , $auth->fd);
        UserRedis::fdMappingUserId($auth->identifier , $auth->fd , $auth->user->id);
        return self::success();
    }

    public static function self(Auth $auth , array $param)
    {
        return self::success($auth->user);
    }

    // 他人信息
    public static function other(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'other_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $other = UserModel::findById($param['other_id']);
        if (empty($other)) {
            return self::error('未找到用户' , 404);
        }
        UserUtil::handle($other , $auth->user->id);
        return self::success($other);
    }

    // 修改用户选项信息
    public static function editUserOption(Auth $auth , array $param)
    {
        $user_option = $auth->user->user_option;
        $param['private_notification']  = $param['private_notification'] === '' ? $user_option->private_notification : $param['private_notification'];
        $param['group_notification']    = $param['group_notification'] === '' ? $user_option->group_notification : $param['group_notification'];
        $param['write_status']          = $param['write_status'] === '' ? $user_option->write_status : $param['write_status'];
        $param['friend_auth']           = $param['friend_auth'] === '' ? $user_option->friend_auth : $param['friend_auth'];
        $param['clear_timer_for_private']           = $param['clear_timer_for_private'] === '' ? $user_option->clear_timer_for_private : $param['clear_timer_for_private'];
        $param['clear_timer_for_group']           = $param['clear_timer_for_group'] === '' ? $user_option->clear_timer_for_group : $param['clear_timer_for_group'];
        $param['friend_circle_background']           = $param['friend_circle_background'] === '' ? $user_option->friend_circle_background : $param['friend_circle_background'];
        UserOptionData::updateByIdentifierAndUserIdAndData($auth->identifier , $auth->user->id , array_unit($param , [
            'private_notification' ,
            'group_notification' ,
            'write_status' ,
            'friend_auth' ,
            'clear_timer_for_private' ,
            'clear_timer_for_group' ,
            'friend_circle_background' ,
        ]));
        return self::success();
    }

    public static function blockUser(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
            'blocked' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = UserModel::findById($param['user_id']);
        if (empty($user)) {
            return self::error('未找到用户信息' , 404);
        }
        if ($auth->user->id == $user->id) {
            return self::error('设置的用户主体不能是自身');
        }
        $bool_for_int = config('business.bool_for_int');
        if (!in_array($param['blocked'] , $bool_for_int)) {
            return self::error('不支持的 blocked 值，当前受支持的值有 ' . implode(' , ' , $bool_for_int));
        }
        switch ($param['blocked'])
        {
            case 0:
                BlacklistModel::unblockUser($auth->user->id , $user->id);
                break;
            case 1:
                // 检查是否已经在黑名单列表
                if (BlacklistModel::exist($auth->user->id , $user->id)) {
                    return self::error('已经在黑名单列表' , 403);
                }
                BlacklistModel::u_insertGetId($auth->identifier , $auth->user->id , $user->id);
                break;
        }
        // 刷新好友列表
        $auth->push($auth->user->id , 'refresh_friend');
        $auth->push($auth->user->id , 'refresh_blacklist');
        return self::success();
    }

    public static function blacklist(Auth $auth , array $param)
    {
//        $total = BlacklistModel::countByUserId($auth->user->id);
//        $limit = empty($param['limit']) ? config('app.limit') : $param['limit'];
//        $page = PageUtil::deal($total , $param['page'] , $limit);
//        $res = BlacklistModel::listByUserId($auth->user->id , $page['offset'] , $page['limit']);
        $res = BlacklistModel::getByUserId($auth->user->id);
        foreach ($res as $v)
        {
            UserUtil::handle($v->block_user , $auth->user->id);
        }
//        $res = PageUtil::data($page , $res);
        return self::success($res);
    }

    // 个人二维码
    public static function QRCodeData(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $user = UserModel::findById($param['user_id']);
        if (empty($user)) {
            return self::error('未找到用户信息' , 404);
        }
        $download = SystemParamModel::getValueByKey('app_download');
        $data = [
            'type'  => 'user' ,
            'data'  => [
                'id' => $user->id
            ]
        ];
        $base64 = base64_encode(json_encode($data));
        $link = sprintf('%s?identity=%s&data=%s' , $download , $auth->identifier , $base64);
        return self::success($link);
    }

    public static function sync(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'rid' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $res = AppPush::sync($auth->platform , $auth->user->id , $param['rid']);
        if ($res['code'] != 200) {
            return self::error($res['data'] , 500);
        }
        return self::success();
    }

    public static function changePhone(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'area_code' => 'required' ,
            'phone'     => 'required' ,
            'sms_code'  => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 新手机号码是否和旧手机号码一致
        if (UserUtil::isSamePhoneWithAreaCode($param['area_code'] , $param['phone'] , $auth->user->area_code , $auth->user->phone)) {
            return self::error('新旧手机号码一致，请检查手机号码' , 403);
        }
        // 检查当前提供的手机号码是否已经被注册
        $user = UserModel::findByIdentifierAndAreaCodeAndPhone($auth->identifier , $param['area_code'] , $param['phone']);
        if (!empty($user)) {
            return self::error('该手机号码已经被绑定，请重新输入' , 403);
        }
        // 检查短信验证码
        $sms_code = SmsCodeModel::findByIdentifierAndAreaCodeAndPhoneAndType($auth->identifier , $param['area_code'] , $param['phone'] , 4);
        if (empty($sms_code)) {
            return self::error('请先发送短信验证码');
        }
        if (strtotime($sms_code->update_time) + config('app.code_duration') < time()) {
            return self::error('验证码已经过期');
        }
        if ($sms_code->code != $param['sms_code']) {
            return self::error('短信验证码不正确');
        }
        try {
            DB::begintransaction();
            UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , array_unit($param , [
                'area_code' ,
                'phone' ,
            ]));
            // 短信验证码标记为已经使用
            SmsCodeModel::updateById($sms_code->id , [
                'used' => 1
            ]);
            DB::commit();
            return self::success('操作成功');
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 写入状态变更
    public static function writeStatusChange(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'friend_id' => 'required' ,
            'status'     => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查是否是好友
//        if (!FriendModel::isFriend($auth->user->id , $param['friend_id'])) {
//            return self::error('你们还不是好友' , 403);
//        }
        // 检查用户是否开启了输入状态
        if ($auth->user->user_option->write_status == 0) {
            return self::error('您尚未开启输入状态展示，禁止操作' , 403);
        }
        $write_status = config('business.write_status');
        if (!in_array($param['status'] , $write_status)) {
            return self::error('不支持的写入状态，当前受支持的写入状态有' . implode(',' , $write_status));
        }
        $chat_id = ChatUtil::chatId($auth->user->id , $param['friend_id']);
        $auth->push($param['friend_id'] , 'write_status' , [
            'chat_id'       => $chat_id ,
            'friend_id'     => $param['friend_id'] ,
            'status'  => $param['status'] ,
        ]);
        return self::success();
    }

    // 删除申请
    public static function deleteApp(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'application_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $id_list = json_decode($param['application_id'] , true);
        if (empty($id_list)) {
            return self::error('请提供待删除的信息');
        }
        // 检查是否存在不属于自己的验证信息
        if (ApplicationModel::hasOther($auth->user->id , $id_list)) {
            return self::error('包含他人的申请信息，禁止操作' , 403);
        }
        ApplicationModel::delByIds($id_list);
        $auth->push($auth->user->id , 'refresh_application');
        return self::success();
    }

    // 清空申请
    public static function emptyApp(Auth $auth , array $param)
    {
        ApplicationModel::delByUserId($auth->user->id);
        $auth->push($auth->user->id , 'refresh_unread_count');
        $auth->push($auth->user->id , 'refresh_app_unread_count');
        return self::success();
    }

    public static function joinFriendMethod(Auth $auth , array $param)
    {
        $res = JoinFriendMethodModel::getAll();
        foreach ($res as $v)
        {
            $v->enable = UserJoinFriendOptionModel::enable($auth->user->id , $v->id);
        }
        return self::success($res);
    }

    public static function setJoinFriendMethod(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'join_friend_method_id' => 'required' ,
            'enable' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $bool_for_int = config('business.bool_for_int');
        if (!in_array($param['enable'] , $bool_for_int)) {
            return self::error('不支持的 enable 值，当前受支持的值有 ' . implode(',' , $bool_for_int) , 403);
        }
//        UserJoinFriendOptionModel::updateByUserIdAndJoinFriendMethodIdAndEnable($auth->user->id , $param['join_friend_method_id'] , $param['enable']);
        UserJoinFriendOptionData::updateByIdentifierAndUserIdAndJoinFriendMethodIdAndData($auth->identifier , $auth->user->id , $param['join_friend_method_id'] , array_unit($param , [
            'enable' ,
        ]));
        return self::success();
    }

    public static function initDestroyPassword(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'destroy_password' => 'required' ,
            'confirm_destroy_password' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查当前是否设置了销毁密码
        if ($auth->user->is_init_destroy_password == 1) {
            return self::error('你已经初始化过销毁密码，禁止再次操作' , 403);
        }
        if ($param['destroy_password'] != $param['confirm_destroy_password']) {
            return self::error('两次输入的密码不一致');
        }
        $destroy_password = Hash::make($param['destroy_password']);
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'destroy_password' => $destroy_password ,
            'is_init_destroy_password' => 1 ,
        ]);
        return self::success();
    }

    public static function initPassword(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'password' => 'required' ,
            'confirm_password' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查当前是否设置了销毁密码
        if ($auth->user->is_init_password == 1) {
            return self::error('你已经初始化过登录密码，禁止再次操作' , 403);
        }
        if ($param['password'] != $param['confirm_password']) {
            return self::error('两次输入的密码不一致');
        }
        $password = Hash::make($param['password']);
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'password' => $password ,
            'is_init_password' => 1 ,
        ]);
        return self::success();
    }

    public static function initPayPassword(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'pay_password' => 'required' ,
            'confirm_pay_password' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查当前是否设置了销毁密码
        if ($auth->user->is_init_pay_password == 1) {
            return self::error('你已经初始化过支付密码，禁止再次操作' , 403);
        }
        if ($param['pay_password'] != $param['confirm_pay_password']) {
            return self::error('两次输入的密码不一致');
        }
        $pay_password = Hash::make($param['pay_password']);
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'pay_password' => $pay_password ,
            'is_init_pay_password' => 1 ,
        ]);
        return self::success();
    }

    public static function setDestroyPassword(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'origin_destroy_password' => 'required' ,
            'destroy_password' => 'required' ,
            'confirm_destroy_password' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查当前是否设置了销毁密码
        if ($auth->user->is_init_destroy_password != 1) {
            return self::error('您尚未初始化销毁密码，请先初始化后再操作' , 403);
        }
        if (!Hash::check($param['origin_destroy_password'] , $auth->user->destroy_password)) {
            return self::error('旧销毁密码有误，请重新输入');
        }
        if ($param['destroy_password'] != $param['confirm_destroy_password']) {
            return self::error('两次输入的销毁密码不一致');
        }
        $destroy_password = Hash::make($param['destroy_password']);
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'destroy_password' => $destroy_password ,
        ]);
        return self::success();
    }

    public static function setPassword(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'origin_password' => 'required' ,
            'password' => 'required' ,
            'confirm_password' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查当前是否设置了销毁密码
        if ($auth->user->is_init_password != 1) {
            return self::error('您尚未初始化登录密码，请先初始化后再操作' , 403);
        }
        if (!Hash::check($param['origin_password'] , $auth->user->password)) {
            return self::error('旧登录密码有误，请重新输入');
        }
        if ($param['password'] != $param['confirm_password']) {
            return self::error('两次输入的登录密码不一致');
        }
        $password = Hash::make($param['password']);
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'password' => $password ,
        ]);
        return self::success();
    }

    public static function setPayPassword(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'origin_pay_password' => 'required' ,
            'pay_password' => 'required' ,
            'confirm_pay_password' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查当前是否设置了销毁密码
        if ($auth->user->is_init_pay_password != 1) {
            return self::error('您尚未初始化支付密码，请先初始化后再操作' , 403);
        }
        if (!Hash::check($param['origin_pay_password'] , $auth->user->pay_password)) {
            return self::error('旧支付密码有误，请重新输入');
        }
        if ($param['pay_password'] != $param['confirm_pay_password']) {
            return self::error('两次输入的支付密码不一致');
        }
        $pay_password = Hash::make($param['pay_password']);
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'pay_password' => $pay_password ,
        ]);
        return self::success();
    }

    public static function setEnableDestroyPassword(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'enable_destroy_password' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $bool_for_int = config('business.bool_for_int');
        if (!in_array($param['enable_destroy_password'] , $bool_for_int)) {
            return self::error('enable_destroy_password 的值超出受支持的范围，当前支持的值有：' . implode(',' , $bool_for_int));
        }
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , array_unit($param , [
            'enable_destroy_password'
        ]));
        return self::success();
    }

    // 销毁用户
    public static function destroy(Auth $auth , array $param)
    {
        if ($auth->user->is_init_destroy_password == 1) {
            // 已经初始化密码
            if ($auth->user->enable_destroy_password == 1) {
                // 启用了销毁密码
                $validator = Validator::make($param , [
                    'destroy_password' => 'required' ,
                ]);
                if ($validator->fails()) {
                    return self::error($validator->message());
                }
                if (!Hash::check($param['destroy_password'] , $auth->user->destroy_password)) {
                    return self::error('销毁密码错误' , 403);
                }
            }
        }
        UserUtil::delete($auth->identifier , $auth->user->id);
        return self::success();
    }

    // 分享注册二维码
    public static function shareRegisterQRCode(Auth $auth , array $param)
    {
        $app_download = SystemParamModel::getValueByKey('app_download');
        $app_download = sprintf('%s?invite_code=%s' , $app_download , $auth->user->invite_code);
        return self::success($app_download);
    }

    //
    public static function updateKey(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'key' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        // 检查 key 长度
        if (strlen($param['key']) != 16) {
            return self::error('key 的长度强制要求为 16 位单字节字符');
        }
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'aes_key' => $param['key']
        ]);
        return self::success();
    }

    public static function emptyMessage(Auth $auth , array $param)
    {
        try {
            DB::beginTransaction();
            // 获取用户的会话列表
            $sessions = SessionModel::getByUserId($auth->user->id);
            foreach ($sessions as $v)
            {
                if ($v->type == 'system') {
                    continue ;
                }
                $res = SessionUtil::delById($v->id);
                if ($res['code'] != 0) {
                    DB::rollBack();
                    return self::error($res['data'] , $res['code']);
                }
            }
            DB::commit();
            $auth->push($auth->user->id , 'refresh_session');
            $auth->push($auth->user->id , 'refresh_unread_count');
            $auth->push($auth->user->id , 'refresh_session_unread_count');
            return self::success();
        } catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    // 退出登录
    public static function logout(Auth $auth , array $param)
    {
        $deny_platform = ['web' , 'pc'];
	    echo "成功退出1";
	    if (!in_array($auth->platform , $deny_platform)) {
            // 解绑用户id 和 极光推送的绑定关系
            $res = AppPush::sync($auth->platform , $auth->user->id , 1);
            if ($res['code'] != 200) {
                return self::error($res['data'] , 500);
            }
        }
	    echo "成功退出2";
	    UserActivityLogUtil::createOrUpdateCountByIdentifierAndUserIdAndDateAndData($auth->identifier , $auth->user->id , date('Y-m-d') , [
            'logout_count' => 'inc'
        ]);
        // 解绑用户id 和 连接id
	    echo "成功退出3";
	    UserRedis::delUserIdMappingFd($auth->identifier , $auth->user->id , $auth->fd);
        // 删除 客户端连接 id 映射的用户id
        UserRedis::delFdMappingUserId($auth->identifier , $auth->fd);
	    echo "成功退出";
	    // 删除 token
//        UserTokenModel::delByToken($auth->token);
        return self::success();
    }

    public static function avatar(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'identifier'    => 'required' ,
            'extranet_ip'   => 'required' ,
            'client_id'     => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        if ($param['identifier'] != $auth->identifier) {
            return self::error("web 和 当前 app 非同一项目！禁止操作");
        }
        // 检查给定的客户端连接是否存在
        $auth->clientPush([
            'extranet_ip' => $param['extranet_ip'] ,
            'client_id'   => $param['client_id']
        ] , 'avatar' , $auth->user->avatar);
        return self::success();
    }

    // todo 新增版本二支持
    // pc 端授权登录
    public static function authPc(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'identifier' => 'required' ,
            'extranet_ip' => 'required' ,
            'client_id' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        if ($param['identifier'] != $auth->identifier) {
            return self::error("web 和 当前 app 非同一项目！禁止操作");
        }
        $param['platform'] = 'web';
        $param['identifier'] = $auth->identifier;
        $param['user_id'] = $auth->user->id;
        $param['token']  = MiscUtil::token();
        $param['expire'] = date('Y-m-d H:i:s' , time() + config('app.timeout'));
        try {
            DB::beginTransaction();
            // 先检查当前登录平台是否是非 pc 浏览器
            // 如果时非 pc 浏览器，那么将其他有效的 token 删除
            // 或让其等价于 无效
            // 这是为了保证 同一平台仅 允许 单个设备登录
            $single_device_for_platform = config('business.single_device_for_platform');
            if (in_array($param['platform'], $single_device_for_platform)) {
                // 删除掉其他 token
                UserTokenModel::delByUserIdAndPlatform($auth->user->id, $param['platform']);
            }
            UserRedis::fdMappingPlatformForWeb($param['identifier'], $param['extranet_ip'] , $param['client_id'], $param['platform']);
            UserTokenModel::u_insertGetId($param['identifier'], $param['user_id'], $param['token'], $param['expire'], $param['platform']);
            // 上线通知
            $online = UserRedis::isOnline($param['identifier'], $auth->user->id);
            UserUtil::mapping($param['identifier'], $auth->user->id, $param['client_id']);
            if (!$online) {
                // 之前如果不在线，现在上线，那么推送更新
                UserUtil::onlineStatusChange($param['identifier'], $param['user_id'], 'online');
            }
            DB::commit();
            if (in_array($param['platform'] , $single_device_for_platform)) {
                // 通知其他客户端你已经被迫下线
                $clients = UserRedis::userIdMappingFd($param['identifier'] , $auth->user->id);
                $exclude = [];
                foreach ($clients as $v)
                {
                    // 检查平台
                    $platform = UserRedis::fdMappingPlatformForWeb($param['identifier'] , $v['extranet_ip'] , $v['client_id']);
                    if (!in_array($platform , $single_device_for_platform)) {
                        $exclude[] = $v;
                        continue ;
                    }
                    if ($v['extranet_ip'] == $param['extranet_ip'] && $v['client_id'] == $param['client_id']) {
                        $exclude[] = $v;
                        continue ;
                    }
                }
                $auth->push($auth->user->id , 'forced_offline' , '' , $exclude);
            }
            $auth->clientPush([
                'extranet_ip'   => $param['extranet_ip'] ,
                'client_id'     => $param['client_id']
            ] , 'logined', [
                'user_id'   => $auth->user->id,
                'token'     => $param['token'],
            ]);
            return self::success('操作成功');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function setLanguage(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'language' => 'required' ,
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $language = config('business.language');
        if (!in_array($param['language'] , $language)) {
            return self::error('不支持的语言');
        }
        UserData::updateByIdentifierAndIdAndData($auth->identifier , $auth->user->id , [
            'language' => $param['language']
        ]);
        return self::success();
    }
}