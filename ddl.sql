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
  password varchar(255) default '' comment '密码' ,
  phone varchar(255) default '' comment '手机号码' ,
  area_code varchar(255) default '' comment '区号' ,
  full_phone varchar(255) default '' comment '完整的手机号码: 区号 + 手机号码' ,
  role enum('admin' , 'user') default 'user' comment 'admin-后台用户 user-平台用户' ,
  unique_code varchar(255) default '' comment '唯一码，同一项目不允许重复！我们系统的唯一标识符' ,
  is_temp tinyint default 0 comment '是否是临时用户: 0-否 1-是' ,
  p_id int unsigned default 0 comment '上级用户（推荐人）：rtc_user.id' ,
  invite_code varchar(255) default '' comment '邀请码' ,
  nickname varchar(255) default '' comment '昵称' ,
  avatar varchar(500) default '' comment '头像' ,
  sex tinyint default 0 comment '0-保密 1-男 2-女' ,
  birthday datetime default null comment '出生日期' ,
  signature varchar(500) default '' comment '个性签名' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户表';

drop table if exists `rtc_user_option`;
create table if not exists `rtc_user_option` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  private_notification tinyint default 1 comment '私聊通知：0-不允许 1-允许' ,
  group_notification tinyint default 1 comment '群聊通知：0-不允许 1-允许' ,
  write_status tinyint default 1 comment '输入状态开关：0-关闭 1-开启' ,
  friend_auth tinyint default 1 comment '申请我为好友验证：0-关闭 1-开启' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户设置表';

drop table if exists `rtc_user_token`;
create table if not exists `rtc_user_token` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.user_id' ,
  token varchar(255) default '' comment 'token' ,
  expire datetime default current_timestamp comment '过期时间' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户 token 表';

drop table if exists `rtc_group`;
create table if not exists `rtc_group` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment '群主：rtc_user.id' ,
  name varchar(255) default '' comment '群名' ,
  image varchar(500) default '' comment '群图片' ,
  is_temp tinyint default 0 comment '是否是临时群: 0-否 1-是' ,
  is_service tinyint default 0 comment '是否是服务通道' ,
  auth tinyint default 0 comment '进群认证：0-否 1-是' ,
  announcement varchar(5000) default '' comment '群公告' ,
  introduction varchar(5000) default '' comment '群简介' ,
  `type` tinyint default 1 comment '群类型：1-永久群 2-时效群' ,
  `anonymous` tinyint default 1 comment '匿名聊天：0-否 1-是' ,
  `expire` datetime default null comment '当 type = 2时，该字段有效，表示群的过期时间' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群';

drop table if exists `rtc_group_member`;
create table if not exists `rtc_group_member` (
  id int unsigned not null auto_increment ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群成员';

drop table if exists `rtc_friend_group`;
create table if not exists `rtc_friend_group` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  name varchar(255) default '' comment '分组名称' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '好友分组';

drop table if exists `rtc_friend`;
create table if not exists `rtc_friend` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment '我的id: rtc_user.id' ,
  friend_id int unsigned default 0 comment '好友id：rtc_user.id' ,
  friend_group_id int unsigned default 0 comment 'rtc_friend_group.id' ,
  burn tinyint default 0 comment '阅后即焚：0-否 1-是' ,
  remark varchar(255) default '' comment '好友备注' ,
  background varchar(1000) default '' comment '聊天背景' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '我的好友';

drop table if exists `rtc_application`;
create table if not exists `rtc_application` (
  id int unsigned not null auto_increment ,
  type varchar(255) default '' comment '类型：private-私聊；group-群聊' ,
  op_type varchar(255) default '' comment '操作类型：app_friend-申请成为好友；app_group-申请进群；invite_into_group-邀请好友进群；...其他待扩充' ,
  user_id int unsigned default 0 comment '受理方: rtc_user.id' ,
  invite_user_id int unsigned default 0 comment '邀请用户（群用户）：rtc_user.id' ,
  group_id int unsigned default 0 comment '如果 type = group，那么这个字段将会有用' ,
  relation_user_id varchar(255) default '' comment '如果是邀请用户进群，那么这个字段就会有用，关联用户，rtc_user.id 的集合，用,分割；如果是单人，那么就是 rtc_user.id' ,
  status varchar(500) default 'wait' comment '申请状态：approve-同意；refuse-拒绝；wait-等待处理' ,
  remark varchar(500) default '' comment '备注信息' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '申请记录';

drop table if exists `rtc_message`;
create table if not exists `rtc_message` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  chat_id varchar(255) default '' comment '会话id，生成规则：minUserId_maxUserId' ,
  type varchar(255) default 'text' comment '消息类型：text-文本消息 image-图片...等，待扩展' ,
  message text comment '消息' ,
  extra text comment '额外数据' ,
  flag varchar(255) default 'normal' comment '消息标志：burn-阅后即焚消息；normal-正常消息' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '私聊消息';

drop table if exists `rtc_group_message`;
create table if not exists `rtc_group_message` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment '群主：rtc_user.id' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  type varchar(255) default 'text' comment '消息类型：text-文本消息 image-图片...等，待扩展' ,
  message text comment '消息' ,
  extra text comment '额外数据' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群聊消息';

drop table if exists `rtc_group_message_read_status`;
create table if not exists `rtc_group_message_read_status` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  group_message_id int unsigned default 0 comment 'rtc_group_message.id' ,
  is_read tinyint default 0 comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群聊消息-读取状态';

drop table if exists `rtc_message_read_status`;
create table if not exists `rtc_message_read_status` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  message_id int unsigned default 0 comment 'rtc_message.id' ,
  is_read tinyint default 0 comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '私聊消息-读取状态';

drop table if exists `rtc_push`;
create table if not exists `rtc_push` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment 'rtc_project.identifier' ,
  push_type enum('single' , 'multiple') default 'single' comment '推送类型：single-推单人；multiple-推多人' ,
  user_id int unsigned default 0 comment 'rtc_user.id，仅在 type = single 的时候有效' ,
  role enum('admin' , 'user' , 'all') default 'all' comment '接收方角色: admin-工作人员 user-平台用户 all-全部，仅在 type = multiple 的时候有效' ,
  `type` varchar(255) default '' comment '推送类型' ,
  `data` text comment '推送的数据' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送表';

drop table if exists `rtc_push_read_status`;
create table if not exists `rtc_push_read_status` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  push_id int unsigned default 0 comment 'rtc_push.id' ,
  is_read tinyint default 0 comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送消息-读取状态';

-- 黑名单
drop table if exists `rtc_blacklist`;
create table if not exists `rtc_blacklist` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  block_user_id int unsigned default 0 comment '屏蔽的用户Id: rtc_user.id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '黑名单列表';

drop table if exists `rtc_sms_code`;
create table if not exists `rtc_sms_code` (
  id int unsigned not null auto_increment ,
  identifier varchar(255) default '' comment '标识符rtc_project.identifier' ,
  area_code varchar(255) default '' comment '区号' ,
  phone varchar(255) default '' comment '手机号码' ,
  `type` int unsigned default 0 comment '1-注册 2-登录 3-修改密码，其他待补充' ,
  code varchar(255) default '' comment '短信验证码' ,
  update_time datetime default current_timestamp on update current_timestamp comment '更新时间' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '短信验证码';

-- 私聊-删除消息
-- 群聊-删除消息
-- 黑名单
-- 消息撤回
-- 转发