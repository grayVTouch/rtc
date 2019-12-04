<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/5/10
 * Time: 16:46
 */

// http 域名
$host = 'http://192.168.145.128';

return [
    // 监听的 ip
    'ip' => '0.0.0.0' ,
    // 监听的端口
    'port' => 10001 ,
    // 重复使用端口【如果 worker != 1，请务必设置端口重用 = true】
    'reuse_port' => true ,
    // 任务进程的数量
    'task_worker' => 1 ,
    // worker 进程的数量
    'worker' => 1 ,
    // 静态文件根目录
    'document_root' => realpath(__DIR__ . '/../public') ,
    // 调试模式
    'debug' => true ,
    // 是否记录错误日志
    'enable_error_log' => true ,
    // 是否记录定时器执行日志
    'enable_timer_log' => true ,
    // redis 默认过期时间（1个月）
    'timeout' => 1 * 30 * 24 * 3600 ,
    // 是否启用访客模式
    'enable_guest' => false ,
    // 单个客服最多接听的访客数量
    'number_of_receptions' => 10 ,
    // 客服最长等待时间 2min
    'waiter_wait_max_duration' => 3 * 60 ,
    // 记录数限制
    'limit' => 20 ,
    // 网站路径
    'web_dir' => realpath(__DIR__ . '/../public') ,
    // 日志目录
    'log_dir' => realpath(__DIR__ . '/../log') ,
    // host
    'host' => $host ,
    // 默认头像
//    'avatar'        => "{$host}/static/image/avatar.png" ,
    'avatar'        => "" ,
    // 默认群头像
    'group_image'   => "" ,
    // 验证码类有效时间
    'code_duration' => 30 * 24 * 3600 ,
    // 短信验证码 发送频率，单位：s
    'sms_code_wait_time' => 60 ,
    // 群人数限制
    'group_member_limit' => 200 ,
    // 默认群名称
    'group_name' => '群聊' ,
    // 默认个人
    // 是否开启app推送（使用了极光推送等平台）
    'enable_app_push' => true ,
    // 默认的昵称
    'nickname' => '未设置昵称' ,
    // 默认页数
    'page' => 1 ,
    // 消息撤回允许的时间范围
    'withdraw_duration' => 2 * 60 ,
    // app 应用市场
    'download' => 'http://www.baidu.com' ,
    // 定时器：私聊记录清理时间点
    'time_point_for_clear_private_message_timer' => '04:00:00' ,
    // 定时器：群聊记录清理时间点
    'time_point_for_clear_group_message_timer' => '04:00:00' ,
    // 定时器：清理临时群 + 临时用户的时间点
    'time_point_for_clear_tmp_group_and_user_timer' => '03:30:00' ,
    // 搜索好友显示

    // 平台标识符（用以二维码数据区分平台使用）
    'identity' => 'test' ,

    // 系统服务员名称
    'system_waiter_name' => '系统客服' ,

    // 客服咨询会话名称
    'customer_channel_name' => '官方客服' ,

    // 客服账号
    'waiter_username' => 'waiter' ,
    // 客服密码
    'waiter_password' => '123456' ,
];