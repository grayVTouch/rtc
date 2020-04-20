drop table if exists `rtc_project`;
create table if not exists `rtc_project` (
  id int unsigned not null auto_increment ,
  name varchar(255) default '' comment '项目名称' ,
  identifier varchar(255) default '' comment '标识符，唯一' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '项目表';

drop table if exists `rtc_user`;
create table if not exists `rtc_user` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  username varchar(255) default '' comment '用户名' ,
  password varchar(255) default '' comment '登录密码' ,
  pay_password varchar(255) default comment '支付密码' ,
  destroy_password varchar(255) default '' comment '销毁密码：销毁账号的时候要求输入改密码，如果有设置的话' ,
  phone varchar(255) default '' comment '手机号码' ,
  area_code varchar(255) default '' comment '区号' ,
  full_phone varchar(255) default '' comment '完整的手机号码: 区号 + 手机号码' ,
  role varchar(255) default 'user' comment 'admin-后台用户 user-平台用户 super_admin-超级管理员' ,
  unique_code varchar(255) default '' comment '唯一码，同一项目不允许重复！我们系统的唯一标识符' ,
  is_temp tinyint default 0 comment '是否是临时用户: 0-否 1-是' ,
  is_system tinyint default 0 comment '是否是系统用户: 0-否 1-是' ,
  p_id int unsigned default 0 comment '上级用户（推荐人）：rtc_user.id' ,
  invite_code varchar(255) default '' comment '邀请码' ,
  nickname varchar(255) default '' comment '昵称' ,
  avatar varchar(500) default '' comment '头像' ,
  sex tinyint default 0 comment '0-保密 1-男 2-女' ,
  birthday date default null comment '出生日期' ,
  signature varchar(500) default '' comment '个性签名' ,
  enable_destroy_password tinyint default 0 comment '启用销毁密码?：0-禁用 1-启用' ,
  is_init_destroy_password tinyint default 0 comment '是否初始化了销毁密码： 0-否 1-是' ,
  is_init_pay_password tinyint default 0 comment '是否初始化了支付密码： 0-否 1-是' ,
  aes_key varchar(255) default '' comment 'aes 加密的 key，根据需要采用不同的长度；AES-128Bit-CBC加密算法，请提供 16位的单字节字符' ,
  is_test tinyint default 0 comment '是否是测试账号：0-否 1-是' ,
  balance decimal(13 , 2) default 0 comment '用户余额' ,
  `language` varchar(500) default 'zh-cn' comment '语言，可选的值请参考国际语言代码缩写表，访问地址: http://www.rzfanyi.com/ArticleShow.asp?ArtID=969' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户表';

-- 红包表
drop table if exists `rtc_red_packet`;
create table if not exists `rtc_red_packet` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  `type` varchar(500) default '' comment '红包类型：private-个人红包 group-群红包' ,
  sub_type varchar(255) default 0 comment '红包类型：random-拼手气红包 common-普通红包' ,
  coin_id int unsigned default 0 comment 'rtc_coin.id，也有可能是第三方的 coin_id（作为第三方模块嵌入到其他系统的时候一般是第三方 coin_id）' ,
  money decimal(13 , 2) unsigned default 0 comment '红包金额' ,
  order_no varchar(100) default '' comment '订单号' ,
  `number` smallint unsigned default 1 comment '可领取用户数量' ,
  remark varchar(500) default '' comment '备注' ,
  receiver int unsigned default 0 comment 'rtc_user.id，当且仅当 type=private的时候有效' ,
  group_id int unsigned default 0 comment 'rtc_group.id，当且仅当 type=group的时候有效' ,
  message_id int unsigned default 0 comment 'type=private,message_id=rtc_message.id;type=group,message_id=rtc_group_message.id' ,
  received_number smallint unsigned default 0 comment '已领取红包数量' ,
  received_money decimal(13,2) default 0 comment '已领取红包金额' ,
  is_expired tinyint default 0 comment '是否已经过期，超过给定的时间默认为已过期: 0-未过期 1-已经过期' ,
  is_received tinyint default 0 comment '红包是否已经被领取完: 0-已经领取完毕 1-未被领取完' ,
  is_refund tinyint default 0 comment '是否退款：0-否 1-是，仅当红包未被领取完时有效' ,
  received_time datetime default null comment '领取完毕的时间点' ,
  refund_time datetime default null comment '退款发起时间，仅当红包未被领取完成时有效' ,
  refund_money decimal(13,2) default 0 comment '退款金额，仅当红包未被领取完成时有效' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '红包表';

drop table if exists `rtc_red_packet_receive_log`;
create table if not exists `rtc_red_packet_receive_log` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  red_packet_id int unsigned default 0 comment 'rtc_red_packet.id' ,
  coin_id int unsigned default 0 comment 'rtc_coin.id，也有可能是第三方的 coin_id（作为第三方模块嵌入到其他系统的时候一般是第三方 coin_id）' ,
  money decimal(13,2) default 0 comment '领取金额' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '红包领取记录表';

drop table if exists `rtc_fund_log`;
create table if not exists `rtc_fund_log` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  `type` varchar(255) default '' comment '操作类型: red_packet-红包记录；其他看程序字典文件' ,
  `desc` varchar(1000) default '' comment '操作描述' ,
  `order_no` varchar(100) default '' comment '订单号，用在于不同模块之间核对记录用' ,
  coin_id int unsigned default 0 comment 'rtc_coin.id，也有可能是第三方的 coin_id（作为第三方模块嵌入到其他系统的时候一般是第三方 coin_id）' ,
--   `before` decimal(13,2) default 0 comment '金额操作之前余额' ,
--   `after` decimal(13,2) default 0 comment '金额操作之后余额' ,
  `money` decimal(13,2) default 0 comment '变化金额' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '资金记录表';

drop table if exists `rtc_user_option`;
create table if not exists `rtc_user_option` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  private_notification tinyint default 1 comment '私聊通知：0-不允许 1-允许' ,
  group_notification tinyint default 1 comment '群聊通知：0-不允许 1-允许' ,
  write_status tinyint default 1 comment '输入状态开关：0-关闭 1-开启' ,
  friend_auth tinyint default 1 comment '申请我为好友验证：0-关闭 1-开启' ,
  clear_timer_for_private varchar(255) default 'none' comment '自动清理私聊记录时长: none-关闭 day-天 week-周 month-月' ,
  clear_timer_for_group varchar(255) default 'none' comment '自动清理群聊记录时长: none-关闭 day-天 week-周 month-月' ,
  `banned` tinyint(4) DEFAULT '0' COMMENT '全局禁言（仅可后台设置）：0-否 1-是',

      -- 新增朋友圈的相关选项
  -- 允许朋友查看朋友圈的范围
  friend_circle_range tinyint default 0 comment '朋友圈查看范围：0-全部 1-最近三天 2-最近一个月 3-最近半年' ,
  friend_circle_tip tinyint default 0 comment '朋友圈更新提醒：0-不提醒（当好友发布朋友圈的时候，没有红点提醒） 1-当发布朋友圈的时候有红点提醒' ,


  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户设置表';



drop table if exists `rtc_user_token`;
create table if not exists `rtc_user_token` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.user_id' ,
  token varchar(255) default '' comment 'token' ,
  expire datetime default current_timestamp comment '过期时间' ,
  platform varchar(255) default '' comment '平台：pc|mobile|app 等' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户 token 表';

drop table if exists `rtc_group`;
create table if not exists `rtc_group` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment '群主：rtc_user.id' ,
  name varchar(255) default '' comment '群名' ,
  image varchar(500) default '' comment '群图片' ,
  is_temp tinyint default 0 comment '是否是临时群: 0-否 1-是' ,
  is_service tinyint default 0 comment '是否是服务通道' ,
  auth tinyint default 0 comment '进群认证：0-否 1-是' ,
  announcement varchar(5000) default '' comment '群公告' ,
  introduction varchar(5000) default '' comment '群简介' ,
  `type` tinyint default 1 comment '群类型：1-永久群 2-时效群' ,
  `anonymous` tinyint default 0 comment '匿名聊天：0-否 1-是' ,
  `expire` datetime default null comment '当 type = 2时，该字段有效，表示群的过期时间' ,
  banned tinyint default 0 comment '全体禁言，仅群主可设置！是否禁言？0-否 1-是' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群';

drop table if exists `rtc_group_member`;
create table if not exists `rtc_group_member` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  alias varchar(500) default '' comment '我在群里面的别名' ,
  can_notice tinyint default 1 comment '消息免打扰？0-否 1-是' ,
  banned tinyint default 0 comment '成员自身无法设置，仅群主可设置！是否禁言？0-否 1-是' ,
  top tinyint default 0 comment '置顶？：0-否 1-是' ,
  background varchar(500) default '' comment '背景图片' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群成员';

drop table if exists `rtc_friend_group`;
create table if not exists `rtc_friend_group` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  name varchar(255) default '' comment '分组名称' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '好友分组';

drop table if exists `rtc_friend`;
create table if not exists `rtc_friend` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment '我的id: rtc_user.id' ,
  friend_id int unsigned default 0 comment '好友id：rtc_user.id' ,
  friend_group_id int unsigned default 0 comment 'rtc_friend_group.id' ,
  burn tinyint default 0 comment '阅后即焚：0-否 1-是' ,
  alias varchar(255) default '' comment '别名（好友备注）' ,
  can_notice tinyint default 1 comment '消息免打扰：0-否 1-是' ,
  top tinyint default 0 comment '置顶？：0-否 1-是' ,
  background varchar(1000) default '' comment '聊天背景' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`) ,
  key `user_id|friend_id` (`user_id` , `friend_id`) ,
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '我的好友';

drop table if exists `rtc_application`;
create table if not exists `rtc_application` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  type varchar(255) default '' comment '类型：private-私聊；group-群聊' ,
  op_type varchar(255) default '' comment '操作类型：app_friend-申请成为好友；app_group-申请进群；invite_into_group-邀请好友进群；...其他待扩充' ,
  user_id int unsigned default 0 comment '受理方: rtc_user.id' ,
  invite_user_id int unsigned default 0 comment '邀请用户（群用户）：rtc_user.id' ,
  group_id int unsigned default 0 comment '如果 type = group，那么这个字段将会有用' ,
  relation_user_id varchar(255) default '' comment '如果是邀请用户进群，那么这个字段就会有用，关联用户，rtc_user.id 的集合，用,分割；如果是 type=private，那么就是 rtc_user.id' ,
  status varchar(500) default 'wait' comment '申请状态：approve-同意；refuse-拒绝；wait-等待处理' ,
  remark varchar(500) default '' comment '备注信息' ,
  log varchar(500) default '' comment '系统日志' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '申请记录';

drop table if exists `rtc_message`;
create table if not exists `rtc_message` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  chat_id varchar(255) default '' comment '会话id，生成规则：minUserId_maxUserId' ,
  type varchar(255) default 'text' comment '消息类型：text-文本消息 image-图片...等，待扩展' ,
  message text comment '消息' ,
  extra text comment '额外数据' ,
  flag varchar(255) default 'normal' comment '消息标志：burn-阅后即焚消息；normal-正常消息' ,
  blocked tinyint default 0 comment '0-正常消息 1-黑名单消息' ,
  old tinyint default 1 comment '旧消息（兼容字段）：0-否 1-是' ,
  aes_key varchar(255) default 'aes 加密的 key，根据需要采用不同的长度；AES-128Bit-CBC加密算法，请提供 16位的单字节字符' ,
  res_expired tinyint default 0 comment '仅针对资源类型的消息有效，资源是否已经过期：0-未过期 1-已过期' ,
  res_expired_time datetime default null comment '资源过期时间，仅针对资源类型的消息有效' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '私聊消息';

drop table if exists `rtc_group_message`;
create table if not exists `rtc_group_message` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment '群主：rtc_user.id' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  type varchar(255) default 'text' comment '消息类型：text-文本消息 image-图片...等，待扩展' ,
  message text comment '消息' ,
  extra text comment '额外数据' ,
  old tinyint default 1 comment '旧消息（兼容字段）：0-否 1-是' ,
  aes_key varchar(255) default 'aes 加密的 key，根据需要采用不同的长度；AES-128Bit-CBC加密算法，请提供 16位的单字节字符' ,
  res_expired tinyint default 0 comment '仅针对资源类型的消息有效，资源是否已经过期：0-未过期 1-已过期' ,
  res_expired_time datetime default null comment '资源过期时间，仅针对资源类型的消息有效' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群聊消息';

drop table if exists `rtc_group_message_read_status`;
create table if not exists `rtc_group_message_read_status` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  group_message_id int unsigned default 0 comment 'rtc_group_message.id' ,
  is_read tinyint default 0 comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`) ,
  key `user_id|group_message_id` (`user_id` , `group_message_id`) ,
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群聊消息-读取状态';

drop table if exists `rtc_message_read_status`;
create table if not exists `rtc_message_read_status` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  chat_id varchar(255) default '' comment '私聊会话id' ,
  message_id int unsigned default 0 comment 'rtc_message.id' ,
  is_read tinyint default 0 comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '私聊消息-读取状态';

drop table if exists `rtc_push`;
create table if not exists `rtc_push` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  push_type varchar(255) default 'single' comment '推送类型：single-推单人；multiple-推多人' ,
  user_id varchar(500) default '' comment 'rtc_user.id 可以是纯数字 或 json 字符串；仅在 type = single | desiganation 的时候有效' ,
  role varchar(255) default 'all' comment '接收方角色: admin-工作人员 user-平台用户 all-全部，desiganation-指定用户 仅在 type = multiple 的时候有效' ,
  type varchar(255) default '' comment '推送类型：system-系统公告' ,
  title varchar(500) comment '标题' ,
  `desc` varchar(3000) default '' comment '描述' ,
  `content` longtext comment '内容' ,
  `is_show` tinyint default 1 comment '是否显示：例如注册时的单人公告要求不能再后台显示，所以有些公告需要屏蔽' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送表';

drop table if exists `rtc_push_read_status`;
create table if not exists `rtc_push_read_status` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  push_id int unsigned default 0 comment 'rtc_push.id' ,
  is_read tinyint default 0 comment '是否读取: y-已读 n-未读' ,
  `type` varchar(255) default '' comment '推送类型，缓存字段 rtc_push.type' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送消息-读取状态';

-- 黑名单
drop table if exists `rtc_blacklist`;
create table if not exists `rtc_blacklist` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  block_user_id int unsigned default 0 comment '屏蔽的用户Id: rtc_user.id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`) ,
  key `user_id|block_user_id` (`user_id` , `block_user_id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '黑名单列表';

drop table if exists `rtc_delete_message`;
create table if not exists `rtc_delete_message` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  type varchar(255) default '' comment 'private-私聊 group-群聊' ,
  message_id int unsigned default 0 comment 'rtc_message.id or rtc_group_message.id' ,
  target_id varchar(255) default '' comment 'type=private,则 target_id=chat_id；type=group，则target_id=group_id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户删除的聊天记录';

drop table if exists `rtc_delete_message_for_private`;
create table if not exists `rtc_delete_message_for_private` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  chat_id varchar(255) default '' comment '根据 user_id and other_id 根据一定规则生成的字符串' ,
  message_id int unsigned default 0 comment 'rtc_message.id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户删除的私聊聊天记录';

drop table if exists `rtc_delete_message_for_group`;
create table if not exists `rtc_delete_message_for_group` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  group_message_id int unsigned default 0 comment 'rtc_group_message.id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户删除的群聊聊天记录';


drop table if exists `rtc_sms_code`;
create table if not exists `rtc_sms_code` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment '标识符rtc_project.identifier' ,
  area_code varchar(255) default '' comment '区号' ,
  phone varchar(255) default '' comment '手机号码' ,
  `type` int unsigned default 0 comment '1-注册 2-登录 3-修改密码，其他待补充' ,
  code varchar(255) default '' comment '短信验证码' ,
  used tinyint default 0 comment '是否已经使用：0-未使用 1-已使用' ,
  update_time datetime default current_timestamp on update current_timestamp comment '更新时间' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '短信验证码';

drop table if exists `rtc_session`;
create table if not exists `rtc_session` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  type varchar(255) default '' comment '类型：private-私聊 group-群聊' ,
  target_id varchar(255) default '' comment 'type=private，则 target_id=chat_id；type=group，target_id=group_id' ,
  session_id varchar(255) default '' comment '会话id，生成规则 type=private，session_id=md5("private_" + chat_id); type=group,session_id=md5("group_" + group_id)' ,
  top tinyint unsigned default 0 comment '置顶?：0-否 1-是' ,
  can_notice tinyint unsigned default 1 comment '能否通知？：0-否 1-是' ,
  update_time datetime default current_timestamp on update current_timestamp ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '会话列表';

-- 群消息免打扰
drop table if exists `rtc_group_notice`;

drop table if exists `rtc_program_error_log`;
create table if not exists `rtc_program_error_log` (
  id int unsigned not null auto_increment ,
  type varchar(255) default '' comment '日志类型，仅用于搜索区分不同模块的错误日志，辅助快速定位错误' ,
  name varchar(500) default '' comment '错误描述' ,
  detail mediumtext comment '错误详情' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '程序错误日志';

drop table if exists `rtc_clear_timer_log`;
create table if not exists `rtc_clear_timer_log` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  type varchar(255) default '' comment '类型：private-私聊 group-群聊' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '记录清除日志';

drop table if exists `rtc_timer_log`;
create table if not exists `rtc_timer_log` (
  id int unsigned not null auto_increment ,
  type varchar(255) default '' comment '用于区分不同用途的定时器' ,
  log varchar(1000) default '' comment '定时器执行日志，请随意书写' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '定时器执行日志';

drop table if exists `rtc_join_friend_method`;
create table if not exists `rtc_join_friend_method` (
  id int unsigned not null auto_increment ,
  name varchar(1000) default '' comment '' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '添加好友的方式';

drop table if exists `rtc_user_join_friend_option`;
create table if not exists `rtc_user_join_friend_option` (
  id int unsigned not null auto_increment ,
  identifier varchar(500) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  join_friend_method_id int unsigned default 0 comment 'rtc_join_friend_friend_method.id' ,
  enable tinyint default 1 comment '是否开启：0-关闭 1-开启' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户添加好友的选项';

insert into `rtc_join_friend_method` (id , name) values (1 , '手机号码');
insert into `rtc_join_friend_method` (id , name) values (2 , 'ID');
insert into `rtc_join_friend_method` (id , name) values (3 , '我的二维码');

drop table if exists `rtc_article_type`;
create table if not exists `rtc_article_type` (
  id int unsigned not null auto_increment ,
  identifier varchar(500) default '' comment 'rtc_project.identifier' ,
  name varchar(1000) default '' comment '分类名称' ,
  p_id int unsigned default 0 comment '上级id' ,
  enable tinyint default 1 comment '启用？0-否 1-是' ,
  weight smallint default 0 comment '权重' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '文章类型';

drop table if exists `rtc_article`;
create table if not exists `rtc_article` (
  id int unsigned not null auto_increment ,
  identifier varchar(500) default '' comment 'rtc_project.identifier' ,
  article_type_id int unsigned default 0 comment 'rtc_article_type.id' ,
  title varchar(1000) default '' comment '标题' ,
  thumb varchar(1000) default '' comment '封面' ,
  author varchar(1000) default '' comment '作者' ,
  content longtext comment '内容' ,
  enable tinyint default 1 comment '启用？0-否 1-是' ,
  weight smallint default 0 comment '权重' ,
  update_time datetime default current_timestamp ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '文章表';

insert into `rtc_article_type` (id , name , p_id) values
(1,'帮助中心' , 0) ,
(2,'关于我们' , 0) ,
(3,'使用协议' , 2) ,
(4,'隐私条款' , 2);

drop table if exists `rtc_task_log`;
create table if not exists `rtc_task_log` (
  id int unsigned not null auto_increment ,
  `data` mediumtext comment '任务数据' ,
  `result` mediumtext comment '执行结果' ,
  `desc` varchar(1000) default '' comment '任务描述' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '任务执行日志';

drop table if exists `rtc_combination_word`;

drop table if exists `rtc_bind_device`;
create table if not exists `rtc_bind_device` (
  id int unsigned not null auto_increment ,
  `user_id` int unsigned default 0 comment 'rtc_user.id' ,
  `device_code` varchar(1000) default '' comment '设备码' ,
  platform varchar(500) default '' comment '设备平台' ,
  identifier varchar(500) default '' comment '项目标识符' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户绑定的设备';

drop table if exists `rtc_system_param`;
create table if not exists `rtc_system_param` (
  id int unsigned not null auto_increment ,
  `name` varchar(255) default '' comment '名称' ,
  `key` varchar(255) default '' comment 'key' ,
  `value` varchar(255) default '' comment 'value' ,
  `desc` varchar(1000) default '' comment 'value' ,
  identifier varchar(500) default '' comment '项目标识符' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '系统参数';

drop table if exists `rtc_user_activity_log`;
create table if not exists `rtc_user_activity_log` (
  id int unsigned not null auto_increment ,
  identifier varchar(500) default '' comment '项目标识符' ,
  `user_id` int unsigned default 0 comment 'rtc_user.id' ,
  `online_count` int unsigned default 0 comment '在线次数' ,
  `offline_count` int unsigned default 0 comment '离线次数（完整离线）' ,
  `login_count` int unsigned default 0 comment '登录次数' ,
  `logout_count` int unsigned default 0 comment '登出次数' ,
  `date` date default null comment '日期' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户活跃记录';

drop table if exists `rtc_statistics_user_activity_log`;
create table if not exists `rtc_statistics_user_activity_log` (
  id int unsigned not null auto_increment ,
  identifier varchar(500) default '' comment '项目标识符' ,
  `user_count` varchar(255) default 0 comment '在线用户数' ,
  `client_count` varchar(255) default 0 comment '在线客户端数量' ,
  `date` date default null comment '日期' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '统计：用户活跃记录';

drop table if exists `rtc_translation`;
create table if not exists `rtc_translation` (
  id int unsigned not null auto_increment ,
  `source_language` char(255) default 'cn' comment '源语言' ,
  `target_language` char(255) default 'cn' comment '目标语言' ,
  original mediumtext comment '原文' ,
  `translation` mediumtext comment '译文' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '翻译表';

-- drop table if exists `rtc_report`;
-- create table if not exists `rtc_report` (
--   id int unsigned not null auto_increment ,
--   user_id int unsigned default 0 comment 'rtc_user.id' ,
--
--   primary key `id` (`id`)
-- ) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '举报功能';

drop table if exists `rtc_combination_word`;

-- 上下线通知
-- 写入状态通知
-- 时效群到时解散
-- 消息记录清除
-- 相关搜索（好友+群组+聊天记录搜索）
-- 会话删除（删除会话，删除该用户的聊天记录，仅当前用户的记录删除）
--  私聊
-- 群聊（其中还有一个客服聊天，群聊的一种）

-- 信息加密
-- 系统公告

alter table `rtc_group` add banned tinyint default 0 comment '全体禁言，仅群主可设置！是否禁言？0-否 1-是';
alter table `rtc_article_type` add enable tinyint default 1 comment '启用？0-否 1-是';
alter table `rtc_article_type` add weight smallint default 0 comment '权重';
alter table `rtc_article` add enable tinyint default 1 comment '启用？0-否 1-是';
alter table `rtc_article` add weight smallint default 0 comment '权重';
alter table `rtc_article` add thumb varchar(1000) default '' comment '封面';
alter table `rtc_user` add enable_destroy_password tinyint default 1 comment '启用销毁密码?：0-禁用 1-启用';
alter table `rtc_user` add is_init_destroy_password tinyint default 0 comment '是否初始化了销毁密码： 0-否 1-是';
alter table `rtc_user` add destroy_password varchar(255) default '' comment '销毁密码：销毁账号的时候要求输入改密码，如果有设置的话';
alter table `rtc_user` add is_init_password tinyint default 0 comment '是否初始化了登录密码？0-否 1-是';
alter table `rtc_message` add old tinyint default 1 comment '旧消息（兼容字段）：0-否 1-是';
alter table `rtc_group_message` add old tinyint default 1 comment '旧消息（兼容字段）：0-否 1-是';
alter table `rtc_user` add aes_key varchar(255) default '' comment 'aes 加密的 key，根据需要采用不同的长度；AES-128Bit-CBC加密算法，请提供 16位的单字节字符';
alter table `rtc_message` add aes_key varchar(255) default '' comment 'aes 加密的 key，根据需要采用不同的长度；AES-128Bit-CBC加密算法，请提供 16位的单字节字符';
alter table `rtc_group_message` add aes_key varchar(255) default '' comment 'aes 加密的 key，根据需要采用不同的长度；AES-128Bit-CBC加密算法，请提供 16位的单字节字符';
alter table `rtc_task_log` add `result` mediumtext comment '执行结果';
alter table `rtc_user_token` add platform varchar(255) default '' comment '平台：pc|mobile|app 等';


-- 新增项目标识符 nimo 带新增的字段（建议是清除数据库然后重新建表）
alter table `rtc_user_option` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_user_join_friend_option` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_group` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_group_member` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_message` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_group_message` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_message_read_status` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_group_message_read_status` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_blacklist` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_session` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_push` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_push_read_status` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_user_token` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_article` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_article_type` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_friend_group` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_application` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_friend` add identifier varchar(255) default '' comment 'rtc_project.identifier';
alter table `rtc_push` add `is_show` tinyint default 1 comment '是否显示：例如注册时的单人推送要求不能再后台显示，所以有些推送需要屏蔽';


alter table rtc_message add res_expired tinyint default 0 comment '仅针对资源类型的消息有效，资源是否已经过期：0-未过期 1-已过期';
alter table rtc_group_message add res_expired tinyint default 0 comment '仅针对资源类型的消息有效，资源是否已经过期：0-未过期 1-已过期';


update `rtc_user_option` set identifier = 'nimo';
update `rtc_user_join_friend_option` set identifier = 'nimo';
update `rtc_group` set identifier = 'nimo';
update `rtc_group_member` set identifier = 'nimo';
update `rtc_message` set identifier = 'nimo';
update `rtc_group_message` set identifier = 'nimo';
update `rtc_message_read_status` set identifier = 'nimo';
update `rtc_group_message_read_status` set identifier = 'nimo';
update `rtc_blacklist` set identifier = 'nimo';
update `rtc_session` set identifier = 'nimo';
update `rtc_push` set identifier = 'nimo';
update `rtc_user_token` set identifier = 'nimo';
update `rtc_article` set identifier = 'nimo';
update `rtc_article_type` set identifier = 'nimo';
update `rtc_friend_group` set identifier = 'nimo';
update `rtc_application` set identifier = 'nimo';
update `rtc_friend` set identifier = 'nimo';

alter table `rtc_user` add `balance` decimal(13 , 2) default 0 comment '用户余额';
alter table `rtc_user` add `language` varchar(500) default '' comment '语言';
alter table `rtc_message` add res_expired_time datetime default null comment '资源过期时间，仅针对资源类型的消息有效';
alter table `rtc_group_message` add res_expired_time datetime default null comment '资源过期时间，仅针对资源类型的消息有效';
alter table `rtc_user` add `pay_password` varchar(255) default '' comment '支付密码';
alter table `rtc_red_packet` drop `total`;
alter table `rtc_red_packet` add `money` decimal(13 , 2) unsigned default 0 comment '红包金额';
alter table `rtc_user` add is_init_pay_password tinyint default 0 comment '是否初始化了支付密码： 0-否 1-是';

alter table `rtc_user_option` add friend_circle_range tinyint default 0 comment '朋友圈查看范围：0-全部 1-最近三天 2-最近一个月 3-最近半年';
alter table `rtc_user_option` add friend_circle_tip tinyint default 0 comment '朋友圈更新提醒：0-不提醒（当好友发布朋友圈的时候，没有红点提醒） 1-当发布朋友圈的时候有红点提醒';
alter table `rtc_user_option` add friend_circle_background varchar(1000) default '' comment '朋友圈背景图片';

alter table `rtc_red_packet` add coin_id int unsigned default 0 comment 'rtc_coin.id，也有可能是第三方的 coin_id（作为第三方模块嵌入到其他系统的时候一般是第三方 coin_id）';
alter table `rtc_red_packet` add order_no varchar(100) default '' comment '订单号';
alter table `rtc_red_packet_receive_log` add coin_id int unsigned default 0 comment 'rtc_coin.id，也有可能是第三方的 coin_id（作为第三方模块嵌入到其他系统的时候一般是第三方 coin_id）';
alter table `rtc_fund_log` add `order_no` varchar(100) default '' comment '订单号，用在于不同模块之间核对记录用';
alter table `rtc_red_packet` drop order_no;

-- 缓存方面更改了 user 和 user_option

/**
 * 撤回
 * 双向撤回
 * 删除用户
 * 删除好友
 * 删除群
 *
 * 用户在线的情况下需要清空本地缓存的行为，现在的情况是可能用户不在线
 * 如果用户不在线的话，那该采取什么方式呢？ ...
 *
 * 1. 阅后即焚消息，等待服务端推送
 * 2. 单条消息撤回，等待服务端推送
 * 3. 双向删除|删除用户|删除好友|删除群 这些 等待服务端推送

 * 以上，当有服务端推送的时候执行响应的动作
 *
 * 现在问题就是，服务端不知道究竟有多少个客户端，然后这种情况下，当有一个客户端消费队列的时候，旧结束了。
 * 压根就不会
 */

