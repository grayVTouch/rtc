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
        $param['user_id'] = $param['user_id'] ?? '';
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
        $res = UserAction::editUserOption($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 添加到黑名单
    public function blockUser(array $param)
    {
        $param['user_id']  = $param['user_id'] ?? '';
        $res = UserAction::blockUser($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 取消黑名单
    public function unblockUser(array $param)
    {
        $param['user_id']  = $param['user_id'] ?? '';
        $res = UserAction::unblockUser($this , $param);
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

}