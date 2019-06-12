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
        'admin' => '管理员（工作人员）' ,
        'user' => '普通用户' ,
    ] ,

    // 预定义动作
    'push' => [
        'response'              => '服务器响应' ,
        'no_waiter'             => '没有客服' ,
        'refresh_token'         => '请更新用户的登录信息' ,
        'refresh_session'       => '指令：请刷新会话列表' ,
        'refresh_group_for_advoise' => '通知客户端，平台咨询时请更新携带的群信息' ,
        'error'                 => 'Swoole 服务器发生异常' ,
        'waiter_leave'          => '客服已经离开' ,
        'unread_count'          => '未读消息数量' ,
        'allocated'             => '加入客服成功，通知信息' ,
        'unique_code'           => '用户标识符' ,
        'refresh_unread_count'  => '刷新未读消息数量' ,
    ] ,

    // 群推类型
    'push_role' => ['admin' , 'user', 'all' , 'designation'] ,

    // 消息
    'message' => [
        'waiter_join' => '系统通知：您好，客服 【%s】 很高兴为您服务' ,
        'waiter_leave' => '系统通知：由于您长时间未回复，客服 【%s】已经离开' ,
        'waiter_close' => '系统通知：客服 【%s】已经离线' ,
        'no_waiter' => '' ,
    ] ,

    // 消息类型
    'message_type' => [
        'text'  => '文本' ,
        'image' => '图片' ,
    ] ,

    /**
     * 申请状态
     *
     * approve 通过
     * refuse  拒绝
     * wait    等待
     */
    'application_status' => ['approve' , 'refuse' , 'wait'] ,
    // 用户可选的申请状态
    'application_status_for_user' => ['approve' , 'refuse'] ,
];