<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/6/11
 * Time: 17:10
 */

namespace App\WebSocket\V1\Controller;


use App\WebSocket\V1\Action\GroupAction;

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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        $param['limit_id'] = $param['limit_id'] ?? '';
        $param['limit'] = $param['limit'] ?? '';
        $param['once'] = $param['once'] ?? '';
        $res = GroupAction::groupMember($this , $param);
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
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
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 设置群名称
    public function setGroupName(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['name']      = $param['name'] ?? '';
        $res = GroupAction::setGroupName($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 设置我在群里面的别名
    public function setAlias(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['alias']      = $param['alias'] ?? '';
        $res = GroupAction::setAlias($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 消息免打扰
    public function setCanNotice(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['can_notice'] = $param['can_notice'] ?? '';
        $res = GroupAction::setCanNotice($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 修改群公告
    public function setAnnouncement(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $param['announcement'] = $param['announcement'] ?? '';
        $res = GroupAction::setAnnouncement($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 群禁言
    public function banned(array $param)
    {
        $param['user_ids']   = $param['user_ids'] ?? '';
        $param['group_id']  = $param['group_id'] ?? '';
        $param['banned']  = $param['banned'] ?? '';
        $res = GroupAction::banned($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 全体禁言
    public function allBanned(array $param)
    {
        $param['group_id']  = $param['group_id'] ?? '';
        $param['banned']  = $param['banned'] ?? '';
        $res = GroupAction::allbanned($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 客服群
    public function customer(array $param)
    {
        $res = GroupAction::customer($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 个人退群
    public function exitGroup(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupAction::exitGroup($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }

    // 判断当前用户是否在某个群里面
    public function isGroupMember(array $param)
    {
        $param['group_id'] = $param['group_id'] ?? '';
        $res = GroupAction::isGroupMember($this , $param);
        if ($res['code'] != 0) {
            return self::error($res['data'] , $res['code']);
        }
        return self::success($res['data']);
    }
}