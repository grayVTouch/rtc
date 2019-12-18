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
    'task_worker' => 2 ,
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

    // 默认客服手机号码 区号
    'waiter_area_code' => '00' ,

    // 默认客服手机号码【13 位数字的手机号码，请固定 15 位数字】
    'waiter_phone' => '00000000000' ,

    // 是否开启消息加密
    // 如果开启加密，那么将会使用
    // AES-128-CBC 的加密方式
    // 对聊天消息进行加密
    'enable_encrypt' => true ,

    // aes 加密 vi （加密向量）
    // 要求固定容量：128bit
    // 不然前端无法通过给定的 js 库解密
    // 128bit 所以，这边要求固定 16 个单字节字符
    // 允许数字和英文的混合
    // 这个加密向量前后端要求统一
    // 仅 key 允许不一样
    // key 比较特殊
    // 每个用户为其生成一个长度为 16 位的单字节字符
    // 必须！且必须是位每个注册用户生成
    // 因为，每个用户都可以修改 key
    // 强烈要求：必须是 16 位单字节字符！！
    // 然后允许字母/数字混合
//    'aes_vi' => '1234567890123456' ,
    'aes_vi' => 'ards423j32k4h423' ,

    // 分享注册下载链接
    'share_register_link' => 'http://websocket.gca.cool:10001/share/register.html' ,

    // 系统初始化标志
    'initialized' => __DIR__ . '/../initialized.lock' ,

    // redis 缓存过期时间，单位：s
    'cache_duration' => 8 * 3600 ,

    // 设置队列消费的进程数量
    'consume_queue_process' => 4 ,

    // 极限验证
    'enable_gt3' => true ,


];