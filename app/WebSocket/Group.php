<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 17:10
 */

namespace App\WebSocket;


use App\WebSocket\Action\GroupAction;

class Group extends Auth
{
    /**
     * 自身申请入群
     *
     * @param  array $param
     * @return mixed
     */
    public function appJoinGroup(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['remark'] = $param['remark'] ?? '';
        $res = GroupAction::appJoinGroup($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 邀请入群
     *
     * @param array $param
     * @return mixed
     */
    public function inviteJoinGroup(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['relation_user_id'] = $param['relation_user_id'] ?? '';
        $param['remark'] = $param['remark'] ?? '';
        $res = GroupAction::inviteJoinGroup($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }


    /**
     * 群主决定决定申请
     *
     * @param array $param
     * @return mixed
     */
    public function decideApp(array $param)
    {
        $param['application_id'] = $param['application_id'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $res = GroupAction::decideApp($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 群主 T 人
     *
     * @param array $param
     * @return mixed
     */
    public function kickMember(array $param)
    {
        $param['user_id'] = $param['user_id'] ?? '';
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupAction::kickMember($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}