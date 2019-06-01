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
        'user'  => '普通用户' ,
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
    ] ,

    // 群推类型
    'push_role' => ['admin' , 'user' , 'all'] ,
];