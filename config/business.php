<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 8:48
 */

return [
    // 用户角色
    'role' => [
        // 普通管理员
        'admin' ,
        // 普通用户
        'user' ,
        // 超级管理员
        'super_admin' ,
    ] ,

    // 预定义动作
    'push' => [
        // 服务器响应
        'response' ,
        // 没有客服
        'no_waiter' ,
        // 指令：请更新用户的登录信息
        'refresh_token' ,
        // 指令：请刷新会话列表
        'refresh_session' ,
        // 指令：通知客户端，平台咨询时请更新携带的群信息
        'refresh_group_for_advoise' ,
        // Swoole 服务器发生异常
        'error' ,
        // 客服已经离开
        'waiter_leave' ,
        // 未读消息数量
        'unread_count' ,
        // 加入客服成功，通知信息
        'allocated' ,
        // 用户标识符
        'unique_code' ,
        // 指令：刷新未读消息数量（总数量）
        'refresh_unread_count' ,
        // 指令：刷新会话未读消息数量（私聊 + 群聊 + 公告）
        'refresh_session_unread_count' ,
        // 指令：刷新申请列表未读消息数量
        'refresh_app_unread_count' ,
        // 指令：刷新申请列表
        'refresh_application' ,
        // 私聊信息
        'private_message' ,
        // 群聊消息
        'group_message' ,
        // 指令：刷新群成员列表
        'refresh_group_member' ,
        // 指令：刷新群列表
        'refresh_group' ,
        // 指令：刷新好友列表
        'refresh_friend' ,
        // 指令：更新单条私聊消息
        'refresh_private_message' ,
        // 指令：更新单条群聊消息
        'refresh_group_message' ,
        // 推送：用户上线
        'online' ,
        // 推送：下线
        'offline' ,
        // 新消息
        'new' ,
        // 通知给定的用户删除应用本地数据中的给定群及相关信息（如果有的话）
        'delete_group_from_cache' ,
        // 通知给定的用户删除应用本地数据中的给定多条私聊消息记录（如果有的话）
        'delete_private_message_from_cache' ,
        // 通知给定的用户清空应用本地数据中的给定私聊会话记录
        'empty_private_session_from_cache' ,
        // 通知给定的用户清空应用本地数据中的给定群聊会话记录
        'empty_group_session_from_cache' ,
        // 刷新黑名单列表
        'refresh_blacklist' ,
        // 被迫下线通知
        'forced_offline' ,
        // gt3 极验验证
        'gt' ,
        // pc 端登录成功 ws 推送
        'logined' ,
        // pc 端登录页面使用的用户头像
        'avatar' ,
        // 语音通话-已接听
        'accept_voice_call' ,
        // 语音通话-已挂断
        'close_voice_call' ,
    ] ,

    // 消息
    'message' => [
        'waiter_join' => '系统通知：您好，客服 【%s】 很高兴为您服务' ,
        'waiter_leave' => '系统通知：由于您长时间未回复，客服 【%s】已经离开' ,
        'waiter_close' => '系统通知：客服 【%s】已经离线' ,
        'no_waiter' => '系统通知：暂无客服在线' ,
    ] ,

    // 消息类型
    'message_type' => [
        // 文本
        'text' ,
        // 图片
        'image' ,
        // 语音
        'voice' ,
        // 文件
        'file' ,
        // 多余，后面应该删除
        'chat_file' ,
        // 名片
        'card' ,
        // 视频通话
        'video_call' ,
        // 语音通话
        'voice_call' ,
        // 消息集合
        'message_set' ,
        // 通知
        'notification' ,
        // 红包
        'red_packet' ,
        // 撤回消息
        'withdraw' ,
        // 视频
        'video' ,
    ] ,

    /**
     * 申请状态
     */
    'application_status' => [
        // 通过
        'approve' ,
        // 拒绝
        'refuse' ,
        // 等待
        'wait' ,
        // 自动通过
        'auto_approve'
    ] ,

    // 用户可选的申请状态
    'application_status_for_client' => [
        // 通过
        'approve' ,
        // 拒绝
        'refuse'
    ] ,

    // 拒绝操作的申请记录
    'deny_application_status' => [
        // 同意
        'approve' ,
        // 拒绝
        'refuse' ,
        // 自动通过
        'auto_approve' ,
    ] ,

    /**
     * 短信验证码类型
     */
    'sms_code_type' => [
        1 => '注册' ,
        2 => '登录' ,
        3 => '修改密码' ,
        4 => '修改手机号码' ,
        5 => '忘记密码' ,
    ] ,

    // 申请类型
    'app_type' => [
        'app_friend'        => '申请成为好友' ,
        'app_group'         => '申请进群' ,
        'invite_into_group' => '邀请好友进群' ,
        'kick_group'        => '群主踢人出群' ,
    ] ,

    // 群：申请类型
    'app_type_for_group' => [
        // 申请进群
        'app_group' ,
        // 邀请进群
        'invite_into_group' ,
    ] ,

    // 群类型
    'group_type' => [
        // 1-永久群
        1 ,
        // 2-时效群
        2 ,
    ] ,

    // 删除记录的类型
    'delete_type' => [
        // 私聊
        'private' ,
        // 群聊
        'group'
    ] ,

    // 禁止撤回的消息类型
    'deny_withdraw_message_type' => [
        // 撤回消息
        'withdraw' ,
    ] ,

    // 禁止转发的消息的类型
    'deny_forward_message_type' => [
        'withdraw' ,
    ] ,

    // 私聊会话标志
    'burn_for_friend' => [
        // 正常聊天
        0 ,
        // 阅后即焚
        1
    ] ,

    // 消息转发类型
    'forward_type' => [
        // 私聊
        'private' ,
        // 群聊
        'group' ,
    ] ,

    // 置顶会话的类型
    'session_type' => [
        // 私聊
        'private' ,
        // 群聊
        'group' ,
        // 公告
        'system' ,
    ] ,

    // 群验证
    'group_auth' => [
        // 关闭
        0 ,
        // 开启
        1 ,
    ] ,

    // 平台类型
    'platform' => [
        // app 应用
        'app' ,
        // android
        'android' ,
        // ios
        'ios' ,
        // 桌面端
        'pc' ,
        // 网站应用
        'web' ,
    ] ,

    // 禁止推送的平台
    'deny_platform_for_push' => [
        'web' ,
    ] ,

    // 在线状态
    'online_status' => [
        // 上线
        'online' ,
        // 离线
        'offline' ,
    ] ,

    // 写入状态
    'write_status' => [
        // 写入中
        'writing' ,
        // 写入结束
        'writed' ,
    ] ,

    // 定时清理的时间选择
    'duration_for_regular_clear' => [
        // 每天
        'day' ,
        // 每周
        'week' ,
        // 每月
        'month' ,
        // 取消定时清理
        'none' ,
    ] ,

    // 布尔值
    'bool_for_int' => [
        // 否
        0 ,
        // 是
        1 ,
    ] ,


    'clear_timer_for_private' => [
        // 不做任何设置
        'none' ,
        // 每日清除私聊消息
        'day' ,
        // 每周清除私聊消息
        'week' ,
        // 每月清除私聊消息
        'month' ,
    ] ,

    'clear_timer_for_group' => [
        // 不做任何设置
        'none' ,
        // 每日清除私聊消息
        'day' ,
        // 每周清除私聊消息
        'week' ,
        // 每月清除私聊消息
        'month' ,
    ] ,

    // 会话处理状态，状态处理
    'session_process_status' => [
        // 加入
        'join' ,
        // 离开
        'leave' ,
    ] ,

    // 群聊（非客服）-目标用户
    'group_target_user' => [
        // 程序自动分配
        'auto' ,
        // 用户指定推送
        'designation' ,
        // 所有人
        'all' ,
    ] ,

    // 推送类型
    'push_type_for_push' => [
        // 系统公告
        'system'
    ] ,

    // 群推类型
    'push_type_for_user' => [
        // 系统公告
        'single' ,
        'multiple' ,
    ] ,

    // 推送的目标用户
    'role_for_push' => [
        // 全体用户
        'all' ,
        // 工作人员
        'admin' ,
        // 平台用户
        'user' ,
        // 指定用户
        'desiganation' ,
    ] ,

    // 聊天的会话类型
    'chat_type' => [
        // 私聊
        'private' ,
        // 群聊
        'group' ,
    ] ,

    // 同一平台仅允许单一设备登录，支持该功能的平台范围
    'single_device_for_platform' => [
        'app' ,
        'web' ,
    ] ,

    // 需要极验验证的平台
    'support_gt_platform' => [
        'app' ,
    ] ,

    // 任务投递的类型
    'task_type' => [
        // app 推送
        'app_push' ,
        // 消息发送相关
        'chat' ,
    ] ,

    // 语音通话接听状态
    'voice_call_status' => [
        // 等待接听
        'wait' ,
        // 对方拒绝
        'refuse' ,
        // 接听成功
        'accept' ,
        // 自己挂断
        'hang' ,
    ] ,

    // 语音消息允许操作的状态
    'deny_voice_call_status' => [
        'refuse' ,
        'accept' ,
        'hang' ,
    ] ,

    // 语音通话消息 extra 完整格式
    'extra_for_voice_call' => [
        'channel' => 'xxx' ,
        'status' => 'wait' ,
        'start_time' => '2019-10-11 10:00:00' ,
        'end_time' => '2019-10-11 11:00:00' ,
        'duration' => 3600 ,
    ],

    // 资源消息类型
    'res_type_for_message' => [
        'image' ,
        'voice' ,
        'file' ,
        'video' ,
    ] ,

    // 资金记录类型
    'fund_log_type' => [
        // 红包记录
        'red_packet' ,
    ] ,

    // 红包类型
    'red_packet_type' => [
        // 普通红包
        'common' ,
        // 拼手气红包
        'random' ,
    ] ,
    // 支持的语言
    'language' => [
        // 中文
        'zh-CHS' ,
        // 日文
        'ja' ,
        // 韩文
        'ko' ,
        // 英文
        'en' ,
    ] ,
];