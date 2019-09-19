<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/12
 * Time: 8:48
 */

return [
    // 角色
    'role' => [
        // '管理员（工作人员）'
        'admin' ,
        // '普通用户'
        'user' ,
    ] ,

    // 预定义动作
    'push' => [
        'response'              => '服务器响应' ,
        'no_waiter'             => '没有客服' ,
        'refresh_token'         => '请更新用户的登录信息' ,
        'refresh_session'       => '指令：请刷新会话列表' ,
        'refresh_group_for_advoise' => '指令：通知客户端，平台咨询时请更新携带的群信息' ,
        'error'                 => 'Swoole 服务器发生异常' ,
        'waiter_leave'          => '客服已经离开' ,
        'unread_count'          => '未读消息数量' ,
        'allocated'             => '加入客服成功，通知信息' ,
        'unique_code'           => '用户标识符' ,
        'refresh_unread_count'  => '刷新未读消息数量（总数量）' ,
        'refresh_application'   => '刷新申请列表' ,
    ] ,

    /**
     * 群推类型
     * admin        后台用户
     * user         前台用户
     * all          所有用户
     * designation  指定用户
     */
    'push_role' => ['admin' , 'user', 'all' , 'designation'] ,

    // 消息
    'message' => [
        'waiter_join' => '系统通知：您好，客服 【%s】 很高兴为您服务' ,
        'waiter_leave' => '系统通知：由于您长时间未回复，客服 【%s】已经离开' ,
        'waiter_close' => '系统通知：客服 【%s】已经离线' ,
        'no_waiter' => '系统通知：暂无客服在线' ,
    ] ,

    // 消息类型
    'message_type' => [
        'text'  => '文本' ,
        'image' => '图片' ,
        'voice' => '语音' ,
        'file' => '文件' ,
        'card' => '名片' ,
        'video_call' => '视频通话' ,
        'voice_call' => '语音通话' ,
        'message_set' => '消息集合' ,
        'write_status' => '输入状态' ,

        // 聊天项目仅负责发送红包消息，跟红包相关的业务逻辑统统丢给使用该项目的人
        'random_red_envelope' => '随机红包' ,
        'password_red_envelope' => '口令红包' ,
    ] ,

    /**
     * 申请状态
     *
     * approve 通过
     * refuse  拒绝
     * wait    等待
     * auto_approve 自动通过
     */
    'application_status' => ['approve' , 'refuse' , 'wait' , 'auto_approve'] ,
    // 用户可选的申请状态
    'application_status_for_user' => ['approve' , 'refuse'] ,

    /**
     * 短信验证码类型
     */
    'sms_code_type' => [
        1 => '注册' ,
        2 => '登录' ,
        3 => '修改密码'
    ] ,

    // 申请类型
    'app_type' => [
        'app_friend'        => '申请成为好友' ,
        'app_group'         => '申请进群' ,
        'invite_into_group' => '邀请好友进群' ,
    ] ,
];