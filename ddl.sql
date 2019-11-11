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
  is_system tinyint default 0 comment '是否是系统用户: 0-否 1-是' ,
  p_id int unsigned default 0 comment '上级用户（推荐人）：rtc_user.id' ,
  invite_code varchar(255) default '' comment '邀请码' ,
  nickname varchar(255) default '' comment '昵称' ,
  avatar varchar(500) default '' comment '头像' ,
  sex tinyint default 0 comment '0-保密 1-男 2-女' ,
  birthday date default null comment '出生日期' ,
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
  clear_timer_for_private varchar(255) default 'none' comment '自动清理私聊记录时长: none-关闭 day-天 week-周 month-月' ,
  clear_timer_for_group varchar(255) default 'none' comment '自动清理群聊记录时长: none-关闭 day-天 week-周 month-月' ,
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
  `anonymous` tinyint default 0 comment '匿名聊天：0-否 1-是' ,
  `expire` datetime default null comment '当 type = 2时，该字段有效，表示群的过期时间' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群';

drop table if exists `rtc_group_member`;
create table if not exists `rtc_group_member` (
  id int unsigned not null auto_increment ,
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
  alias varchar(255) default '' comment '别名（好友备注）' ,
  can_notice tinyint default 1 comment '消息免打扰：0-否 1-是' ,
  top tinyint default 0 comment '置顶？：0-否 1-是' ,
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
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  chat_id varchar(255) default '' comment '会话id，生成规则：minUserId_maxUserId' ,
  type varchar(255) default 'text' comment '消息类型：text-文本消息 image-图片...等，待扩展' ,
  message text comment '消息' ,
  extra text comment '额外数据' ,
  flag varchar(255) default 'normal' comment '消息标志：burn-阅后即焚消息；normal-正常消息' ,
  blocked tinyint default 0 comment '0-正常消息 1-黑名单消息' ,
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
  chat_id varchar(255) default '' comment '私聊会话id' ,
  message_id int unsigned default 0 comment 'rtc_message.id' ,
  is_read tinyint default 0 comment '是否读取: y-已读 n-未读' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '私聊消息-读取状态';

drop table if exists `rtc_push`;
create table if not exists `rtc_push` (
  id int unsigned not null auto_increment ,
  push_type varchar(255) default 'single' comment '推送类型：single-推单人；multiple-推多人' ,
  user_id int unsigned default 0 comment 'rtc_user.id，仅在 type = single 的时候有效' ,
  role varchar(255) default 'all' comment '接收方角色: admin-工作人员 user-平台用户 all-全部，仅在 type = multiple 的时候有效' ,
  type varchar(255) default '' comment '推送类型：system-系统公告' ,
  title varchar(500) comment '标题' ,
  `desc` varchar(3000) default '' comment '描述' ,
  `content` longtext comment '内容' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送表';

drop table if exists `rtc_push_read_status`;
create table if not exists `rtc_push_read_status` (
  id int unsigned not null auto_increment ,
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
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  block_user_id int unsigned default 0 comment '屏蔽的用户Id: rtc_user.id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '黑名单列表';

drop table if exists `rtc_delete_message`;
create table if not exists `rtc_delete_message` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  type varchar(255) default '' comment 'private-私聊 group-群聊' ,
  message_id int unsigned default 0 comment 'rtc_message.id or rtc_group_message.id' ,
  target_id varchar(255) default '' comment 'type=private,则 target_id=chat_id；type=group，则target_id=group_id' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户删除的聊天记录';


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
  update_time datetime default current_timestamp on update current_timestamp ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '会话列表';

-- 群消息免打扰
drop table if exists `rtc_group_notice`;
create table if not exists `rtc_group_notice` (
  id int unsigned not null auto_increment ,
  user_id int unsigned default 0 comment 'rtc_user.id' ,
  group_id int unsigned default 0 comment 'rtc_group.id' ,
  can_notice tinyint default 1 comment '消息免打扰？0-否 1-是' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '群消息免打扰';

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
  name varchar(1000) default '' comment '分类名称' ,
  p_id int unsigned default 0 comment '上级id' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '文章类型';

drop table if exists `rtc_article`;
create table if not exists `rtc_article` (
  id int unsigned not null auto_increment ,
  article_type_id int unsigned default 0 comment 'rtc_article_type.id' ,
  title varchar(1000) default '' comment '标题' ,
  author varchar(1000) default '' comment '作者' ,
  content longtext comment '内容' ,
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
  `data` varchar(1000) default '' comment '任务数据' ,
  `desc` varchar(1000) default '' comment '任务描述' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '任务执行日志';



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



