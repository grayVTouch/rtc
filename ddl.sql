drop table if exists `xq_project`;
create table if not exists `xq_project` (
  id int unsigned not null auto_increment ,
  name char(255) default '' comment '项目名称' ,
  identifier char(255) default '' comment '项目标识符，唯一' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '项目表';

drop table if exists `xq_user`;
create table if not exists `xq_user` (
  id int unsigned not null auto_increment ,
  identifier char(255) default '' comment 'xq_project.identifier' ,
  user_id int unsigned default 0 comment '用户id' ,
  username char(255) default '' comment '用户名' ,
  nickname char(255) default '' comment '昵称' ,
  avatar varchar(500) default '' comment '头像' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户表';

drop table if exists `xq_user_token`;
create table if not exists `xq_user_token` (
  id int unsigned not null auto_increment ,
  identifier char(255) default '' comment 'xq_project.identifier' ,
  user_id int unsigned default 0 comment 'xq_user.user_id' ,
  token char(255) default '' comment 'token 表' ,
  expire datetime default current_timestamp comment '过期时间' ,
  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '用户 token 表';