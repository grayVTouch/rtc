<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/19
 * Time: 21:54
 */

namespace App\WebSocket\Action;


use App\Lib\Push\AppPush;
use App\Model\ApplicationModel;
use App\Model\BlacklistModel;
use App\Model\FriendModel;
use App\Model\GroupModel;
use App\Model\SmsCodeModel;
use App\Model\UserModel;
use App\Model\UserOptionModel;
use App\Redis\UserRedis;
use App\Util\ChatUtil;
use App\Util\PageUtil;
use App\WebSocket\Auth;
use App\Util\UserUtil;
use function core\array_unit;
use Core\Lib\Validator;
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
        UserModel::updateById($auth->user->id , array_unit($param , [
            'avatar' ,
            'sex' ,
            'birthday' ,
            'nickname' ,
            'signature' ,
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
//        print_r($param);
        UserOptionModel::updateById($user_option->id , array_unit($param , [
            'private_notification' ,
            'group_notification' ,
            'write_status' ,
            'friend_auth' ,
        ]));
        return self::success();
    }

    public static function blockUser(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $block_user = UserModel::findById($param['user_id']);
        if (empty($block_user)) {
            return self::error('未找到用户信息' , 404);
        }
        if ($auth->user->id == $block_user->id) {
            return self::error('不能添加自身到黑名单');
        }
        BlacklistModel::u_insertGetId($auth->user->id , $block_user->id);
        return self::success();
    }

    public static function unblockUser(Auth $auth , array $param)
    {
        $validator = Validator::make($param , [
            'user_id' => 'required'
        ]);
        if ($validator->fails()) {
            return self::error($validator->message());
        }
        $unblock_user = UserModel::findById($param['user_id']);
        if (empty($unblock_user)) {
            return self::error('未找到用户信息' , 404);
        }
        if ($auth->user->id == $unblock_user->id) {
            return self::error('不能从黑名单中删除自身');
        }
        $res = BlacklistModel::unblockUser($auth->user->id , $unblock_user->id);
        if ($res == 0) {
            return self::error('您并没有将对方加入黑名单');
        }
        return self::success('操作成功');
    }

    public static function blacklist(Auth $auth , array $param)
    {
        $total = BlacklistModel::countByUserId($auth->user->id);
        $limit = empty($param['limit']) ? config('app.limit') : $param['limit'];
        $page = PageUtil::deal($total , $param['page'] , $limit);
        $res = BlacklistModel::listByUserId($auth->user->id , $page['offset'] , $page['limit']);
        foreach ($res as $v)
        {
            UserUtil::handle($v->block_user , $auth->user->id);
        }
        $res = PageUtil::data($page , $res);
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
        $download = config('app.download');
        $data = [
            'type'  => 'user' ,
            'id'    => $user->id ,
        ];
        $base64 = base64_encode(json_encode($data));
        $link = sprintf('%s?data=%s' , $download , $base64);
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
        $res = AppPush::sync($auth->user->id , $param['rid']);
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
            UserModel::updateById($auth->user->id , array_unit($param , [
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
        if (!FriendModel::isFriend($auth->user->id , $param['friend_id'])) {
            return self::error('你们还不是好友' , 403);
        }
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
}