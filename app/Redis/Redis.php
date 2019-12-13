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
     * @param string $identifier
     * @param int $fd
     *
     * @var string
     */
    protected static $userIdMappingFd = '%s_user_id_mapping_fd_%s';

    /**
     * 接待数量
     *
     * @param string $identifier
     * @param int $user_id
     *
     * @var string
     */
    protected static $numberOfReceptionsForWaiter = '%s_number_of_receptions_%s';


    /**
     *  fd 映射的 user_id
     *
     * @param string $identifier
     * @param int $fd
     *
     * @var string
     */
    protected static $fdMappingUserId = '%s_fd_mapping_user_id_%s';


    /**
     *  fd 映射的 identifier
     *
     * @param int $fd
     *
     * @var string
     */
    protected static $fdMappingIdentifier = 'fd_mapping_identifier_%s';


    /**
     * 未处理的消息
     *
     * @param string $identifier
     * @param string $identifier
     *
     * @var hash
     */
    protected static $unhandleMsg = '%s_unhandle_msg_%s';

    /**
     * 群组+服务员（活跃）
     *
     * @param string $identifier
     * @param int $group_id
     *
     * @var string
     */
    protected static $groupActiveWaiter = '%s_group_active_waiter_%s';


    /**
     * 群-no_waiter 提醒
     *
     * @param string $identifier
     * @param int $group_id
     *
     * @var string
     */
    protected static $noWaiterForGroup = '%s_no_waiter_for_group_%s';


    /**
     * 清理临时群定时器
     *
     * @var string
     */
    protected static $onceForClearTmpGroupTimer = 'once_for_clear_tmp_group_timer';

    /**
     * 会话成员
     *
     * @param string $identifier
     * @param string $session_id (ChatUtil::sessionId 生成的 session_id)
     *
     * @var string
     */
    protected static $sessionMember = '%s_session_member_%s';

    /**
     * 客户端连接所绑定的平台
     *
     * @param string $identifier
     * @param int $fd
     *
     * @var string
     */
    protected static $fdMappingPlatform = '%_fd_mapping_platform_%s';

    /**
     * 用户最近一次下线时间
     *
     * @var string
     */
    protected static $userRecentOnlineTimestamp = '%s_user_recent_online_timestamp_%s';

    /**
     * 私聊消息已读|未读
     *
     * @param string $identifier
     * @param int $user_id
     * @param int $message_id
     *
     * @var string
     */
    protected static $messageReadStatus = '%s_message_read_status_%s_%s';

    /**
     * 群聊消息已读|未读
     *
     * @param string $identifier
     * @param int $user_id
     * @param int $group_message_id
     *
     * @var string
     */
    protected static $groupMessageReadStatus = '%_group_message_read_status_%s_%s';


    /**
     * 私聊未读消息数量
     *
     * @param string $identifier
     * @param int $user_id
     * @param int $other_id
     *
     * @var string
     */
    protected static $unreadForPrivate = '%s_unread_for_private_%s_%s';

    /**
     * 群聊未读消息数量
     *
     * @param string $identifier
     * @param int    $user_id
     * @param int    $group_id
     *
     * @var string
     */
    protected static $unreadForGroup = '%s_unread_for_group_%s_%s';

    /**
     * rtc_user 表信息缓存
     *
     * @param string $identifier
     * @param int $user_id
     *
     * @var string
     */
    protected static $user = '%s_user_%s';

    /**
     * rtc_user_option 表缓存信息
     *
     * @param string $identifier
     * @param int $user_id
     *
     * @var string
     */
    protected static $userOption = '%s_user_option_%s';

}