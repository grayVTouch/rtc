drop table if exists `xq_push_read_status`;
create table if not exists `xq_push_read_status` (
  id int unsigned not null auto_increment ,

  create_time datetime default current_timestamp comment '创建时间' ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '推送消息-读取状态';