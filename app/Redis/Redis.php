<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/11
 * Time: 17:43
 */
namespace App\Redis;

class Redis
{
    // 命名规则 大类别 + 项目Id + id
    // user_id 映射的 fd
    protected static $fdKey = 'fd_%s_%s';
    // 接待数量
    protected static $numberOfReceptionsForWaiter = 'number_of_receptions_%s_%s';
    // fd 映射的 user_id
    protected static $fdMappingUserIdKey = 'fd_mapping_user_id_%s_%s';
    // fd 映射的 identifier
    protected static $fdMappingIdentifier = 'fd_mapping_identifier_%s';
    // 未处理的消息
    protected static $unhandleMsg = 'unhandle_msg_%s';
    // 群组+服务员（活跃）
    protected static $groupActiveWaiter = 'group_active_waiter_%s_%s';
    // 群-no_waiter 提醒
    protected static $noWaiterForGroup = 'no_waiter_for_group_%s_%s';
}