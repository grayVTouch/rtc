<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/9/19
 * Time: 11:10
 */

return [
    // 验证码类有效时间
    'code_duration' => 30 * 24 * 3600 ,
    // 短信验证码 发送频率，单位：s
    'sms_code_wait_time' => 60 ,
    // 每页显示记录数
    'limit' => 20 ,
    // 群人数限制
    'group_member_limit' => 200 ,
    // 默认群名称
    'group_name' => '群聊' ,
    // 默认个人
    // 是否开启app推送（使用了极光推送等平台）
    'enable_app_push' => false ,
];
