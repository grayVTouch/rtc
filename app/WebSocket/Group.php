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
        $param['group_id']  = $param['group_id'] ?? '';
        $param['remark']    = $param['remark'] ?? '';
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

    /**
     * 解散群
     *
     * @param array $param
     * @return mixed
     */
    public function disbandGroup(array $param)
    {

        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupAction::disbandGroup($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 创建群组
     *
     * @param array $param
     * @return mixed
     * @throws \Exception
     */
    public function create(array $param)
    {
        $param['name'] = $param['name'] ?? '';
        $param['type'] = $param['type'] ?? '';
        $param['expire'] = $param['expire'] ?? '';
        $param['anonymous'] = $param['anonymous'] ?? '';
        $param['user_ids'] = $param['user_ids'] ?? '';
        $res = GroupAction::create($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 我的群列表
     */
    public function myGroup(array $param)
    {
        $res = GroupAction::myGroup($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 群成员
     */
    public function groupMember(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupAction::groupMember($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 群信息
     */
    public function groupInfo(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupAction::groupInfo($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 群验证
     */
    public function groupAuth(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['auth'] = $param['auth'] ?? '';
        $res = GroupAction::groupAuth($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 群二维码
     */
    public function QRCodeData(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupAction::QRCodeData($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    /**
     * 群成员限制
     */
    public function groupMemberLimit(array $param)
    {
        $res = GroupAction::groupMemberLimit($this , $param);
        if ($res['code'] != 200) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}