<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 14:36
 */

namespace App\WebSocket;


use App\WebSocket\Action\FriendAction;

class Friend extends Auth
{
    // 申请成为朋友
    public function appJoinFriend(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $param['remark'] = $param['remark'] ?? '';
        $res = FriendAction::appJoinFriend($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 请求处理
     *
     * @param array $param
     * @return mixed
     */
    public function decideApp(array $param)
    {
        $param['application_id'] = $param['application_id'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $res = FriendAction::decideApp($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 删除好友
     *
     * @param array $param
     * @return mixed
     */
    public function delFriend(array $param)
    {
        $param['friend_id'] = $param['friend_id'] ?? '';
        $res = FriendAction::delFriend($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

}