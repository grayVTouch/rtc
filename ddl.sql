drop table if exists `rtc_project`;
create table if not exists `rtc_project` (
  id int unsigned not null auto_increment ,
  name char(255) default '' comment '项目名称' ,
  identifier char(255) default '' comment '标识符，唯一' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '项目表';

drop table if exists `rtc_user`;
create table if not exists `rtc_user` (
  id int unsigned not null auto_increment ,
  identifier char(255) default '' comment 'rtc_project.identifier' ,
  username char(255) default '' comment '用户名' ,
  nickname char(255) default '' comment '昵称' ,
  avatar varchar(500) default '' comment '头像' ,
  role enum('admin' , 'user') default 'user' comment 'user' ,
  unique_code char(255) default '' comment '唯一码，同一项目不允许重复！其他平台在我们系统的标识符' ,
  is_temp enum('y' , 'n') default 'n' comment '是否是临时用户: y-是 n-否' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户表';

drop table if exists `rtc_user_token`;
create table if not exists `rtc_user_token` (
  id int unsigned not null auto_increment ,
  identifier char(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment 'rtc_user.user_id' ,
  token char(255) default '' comment 'token' ,
  expire datetime default current_timestamp comment '过期时间' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户 token 表';

drop table if exists `rtc_group`;
create table if not exists `rtc_group` (
  id int unsigned not null auto_increment ,
  identifier char(255) default '' comment 'rtc_project.identifier' ,
  user_id int unsigned default 0 comment '群主：rtc_user.id' ,
  name char(255) default '' comment '群名' ,
  image varchar(500) default '' comment '群图片' ,
  is_temp enum('y' , 'n') default 'n' comment '是否是临时群: y-是 n-否' ,
  is_service enum('y' , 'n') default 'n' comment '是否是服务通道' ,
  enable_auth enum('y' , 'n') default 'n' comment '开启进群认证：y-是 n-否' ,
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
  name char(255) default '' comment '分组名称' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '好友分组';

drop table if exists `rtc_friend`;
create table if not exists `rtc_friend` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment '我的id: rtc_user.id' ,
  friend_id int unsigned default 0 comment '好友id：rtc_user.id' ,
  friend_group_id int unsigned default 0 comment 'rtc_friend_group.id' ,
  remark char(255) default '' comment '好友备注' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '我的好友';

drop table if exists `rtc_application`;
create table if not exists `rtc_application` (
  id int unsigned not null auto_increment ,
  type char(255) default '' comment '类型：private-私聊；group-群聊' ,
  op_type char(255) default '' comment '操作类型：add-申请成为好友/申请进群；invite-邀请好友加群；...其他待扩充' ,
  user_id int unsigned default 0 comment '受理方: rtc_user.id' ,
  group_id int unsigned default 0 comment '如果 type = group，那么这个字段将会有用' ,
  relation_user_id char(255) default '' comment '关联用户，rtc_user.id 的集合，用,分割；如果是单人，那么就是 rtc_user.id' ,
  status enum('approve' , 'refuse' , 'wait') default 'wait' comment '申请状态：approve-同意；refuse-拒绝；wait-等待处理' ,
  remark varchar(500) default '' comment '备注信息' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '申请记录';

drop table if exists `rtc_message`;
create table if not exists `rtc_message` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  chat_id int unsigned default 0 comment '会话id，生成规则：minUserId_maxUserId' ,
  type char(255) default 'text' comment '消息类型：text-文本消息 image-图片...等，待扩展' ,
  message text comment '消息' ,
  extra text comment '额外数据' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '私聊消息';

drop table if exists `rtc_group_message`;
create table if not exists `rtc_group_message` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment '群主：rtc_user.id' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  type char(255) default 'text' comment '消息类型：text-文本消息 image-图片...等，待扩展' ,
  message text comment '消息' ,
  extra text comment '额外数据' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群聊消息';

drop table if exists `rtc_group_message_read_status`;
create table if not exists `rtc_group_message_read_status` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  group_message_id int unsigned default 0 comment 'rtc_group_message.id' ,
  is_read enum('y' , 'n') default 'n' comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群聊消息-读取状态';

drop table if exists `rtc_message_read_status`;
create table if not exists `rtc_message_read_status` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  message_id int unsigned default 0 comment 'rtc_message.id' ,
  is_read enum('y' , 'n') default 'n' comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '私聊消息-读取状态';

drop table if exists `rtc_push`;
create table if not exists `rtc_push` (
  id int unsigned not null auto_increment ,
  identifier char(255) default '' comment 'rtc_project.identifier' ,
  push_type enum('single' , 'multiple') default 'single' comment '推送类型：single-推单人；multiple-推多人' ,
  user_id int unsigned default 0 comment 'rtc_user.id，仅在 type = single 的时候有效' ,
  role enum('admin' , 'user' , 'all') default 'all' comment '接收方角色: admin-工作人员 user-平台用户 all-全部，仅在 type = multiple 的时候有效' ,
  `type` char(255) default '' comment '推送类型' ,
  `data` text comment '推送的数据' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送表';

drop table if exists `rtc_push_read_status`;
create table if not exists `rtc_push_read_status` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  push_id int unsigned default 0 comment 'rtc_push.id' ,
  is_read enum('y' , 'n') default 'n' comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送消息-读取状态';

