<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 12:28
 */

namespace App\WebSocket;

use App\WebSocket\Action\UserAction;

class User extends Auth
{
    // 获取平台咨询通道信息
    public function groupForAdvoise(array $param)
    {
        $res = UserAction::groupForAdvoise($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 查看自身用户信息
    public function self(array $param)
    {
        $res = UserAction::self($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 查看好友信息
    public function other(array $param)
    {
        $param['other_id'] = $param['other_id'] ?? '';
        $res = UserAction::other($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 与我相关的申请记录
    public function app(array $param)
    {
        $param['page'] = $param['page'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $res = UserAction::app($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 编辑信息
    public function editUserInfo(array $param)
    {
        $param['nickname']  = $param['nickname'] ?? '';
        $param['avatar']    = $param['avatar'] ?? '';
        $param['sex']       = $param['sex'] ?? '';
        $param['birthday']  = $param['birthday'] ?? '';
        $param['signature'] = $param['signature'] ?? '';
        $res = UserAction::editUserInfo($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 搜索好友
    public function search(array $param)
    {
        $param['keyword'] = $param['keyword'] ?? '';
        $res = UserAction::search($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 重连后：重新绑定映射关系
    public function mapping(array $param)
    {
        $res = UserAction::mapping($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 用户选项修改
    public function editUserOption(array $param)
    {
        $param['private_notification']  = $param['private_notification'] ?? '';
        $param['group_notification']    = $param['group_notification'] ?? '';
        $param['write_status']          = $param['write_status'] ?? '';
        $param['friend_auth']           = $param['friend_auth'] ?? '';
        $param['clear_timer_for_private']           = $param['clear_timer_for_private'] ?? '';
        $param['clear_timer_for_group']           = $param['clear_timer_for_group'] ?? '';
        $res = UserAction::editUserOption($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 黑名单设置
    public function blockUser(array $param)
    {
        $param['user_id']  = $param['user_id'] ?? '';
        $param['blocked']  = $param['blocked'] ?? '';
        $res = UserAction::blockUser($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 黑名单列表
    public function blacklist(array $param)
    {
        $param['limit'] = $param['limit'] ?? '';
        $param['page'] = $param['page'] ?? '';
        $res = UserAction::blacklist($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 个人二维码
     */
    public function QRCodeData(array $param)
    {
        $param['user_id'] = $param['user_id'] ?? '';
        $res = UserAction::QRCodeData($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 推送同步
     */
    public function sync(array $param)
    {
        $param['rid'] = $param['rid'] ?? '';
        $res = UserAction::sync($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 更改手机号码
    public function changePhone(array $param)
    {
        $param['phone'] = $param['phone'] ?? '';
        $param['sms_code'] = $param['sms_code'] ?? '';
        $res = UserAction::changePhone($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 写入状态通知（写入中）
    public function writeStatusChange(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $res = UserAction::writeStatusChange($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 删除申请消息
    public function deleteApp(array $param)
    {
        $param['application_id'] = $param['application_id'] ?? '';
        $res = UserAction::deleteApp($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 删除全部申请消息
    public function emptyApp(array $param)
    {
        $res = UserAction::emptyApp($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 添加好友方式
    public function joinFriendMethod(array $param)
    {
        $res = UserAction::joinFriendMethod($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 设置添加好友的方式
    public function setJoinFriendMethod(array $param)
    {
        $param['join_friend_method_id'] = $param['join_friend_method_id'] ?? '';
        $param['enable'] = $param['enable'] ?? '';
        $res = UserAction::setJoinFriendMethod($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 初始化销毁密码
    public function initDestroyPassword(array $param)
    {
        $param['destroy_password'] = $param['destroy_password'] ?? '';
        $param['confirm_destroy_password'] = $param['confirm_destroy_password'] ?? '';
        $res = UserAction::initDestroyPassword($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 初始化登录密码
    public function initPassword(array $param)
    {
        $param['password'] = $param['password'] ?? '';
        $param['confirm_password'] = $param['confirm_password'] ?? '';
        $res = UserAction::initPassword($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 修改销毁密码
    public function setDestroyPassword(array $param)
    {
        $param['origin_destroy_password'] = $param['origin_destroy_password'] ?? '';
        $param['destroy_password'] = $param['destroy_password'] ?? '';
        $param['confirm_destroy_password'] = $param['confirm_destroy_password'] ?? '';
        $res = UserAction::setDestroyPassword($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 修改登录密码
    public function setPassword(array $param)
    {
        $param['origin_password'] = $param['origin_password'] ?? '';
        $param['password'] = $param['password'] ?? '';
        $param['confirm_password'] = $param['confirm_password'] ?? '';
        $res = UserAction::setPassword($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 启用或禁用销毁密码
    public function setEnableDestroyPassword(array $param)
    {
        $param['enable_destroy_password'] = $param['enable_destroy_password'] ?? '';
        $res = UserAction::setEnableDestroyPassword($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 销毁账号
    public function destroy(array $param)
    {
        $param['destroy_password'] = $param['destroy_password'] ?? '';
        $res = UserAction::destroy($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 分享注册二维码数据
    public function shareRegisterQRCode(array $param)
    {
        $res = UserAction::shareRegisterQRCode($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 更改用户的密钥
    public function updateKey(array $param)
    {
        $param['key'] = $param['key'] ?? '';
        $res = UserAction::updateKey($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}