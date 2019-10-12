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
    // 项目标识符
    // 命名规则 项目id + keyName + id

    /**
     * user_id 映射的 fd
     *
     * @var string
     */
    protected static $userIdMappingFd = '%s_user_id_mapping_fd_%s';

    /**
     * 接待数量
     *
     * @var string
     */
    protected static $numberOfReceptionsForWaiter = '%s_number_of_receptions_%s';


    /**
     *  fd 映射的 user_id
     *
     * @var string
     */
    protected static $fdMappingUserId = '%s_fd_mapping_user_id_%s';


    /**
     *  fd 映射的 identifier
     *
     * @var string
     */
    protected static $fdMappingIdentifier = 'fd_mapping_identifier_%s';


    /**
     *  未处理的消息
     *
     * @var hash
     */
    protected static $unhandleMsg = '%s_unhandle_msg_%s';

    /**
     *  群组+服务员（活跃）
     *
     * @var string
     */
    protected static $groupActiveWaiter = '%s_group_active_waiter_%s';


    /**
     *  群-no_waiter 提醒
     *
     * @var string
     */
    protected static $noWaiterForGroup = '%s_no_waiter_for_group_%s';


    /**
     *  清理临时群定时器
     *
     * @var string
     */
    protected static $onceForClearTmpGroupTimer = 'once_for_clear_tmp_group_timer';

}