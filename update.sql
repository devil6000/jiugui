ALTER TABLE `ims_ewei_shop_lottery` ADD `lottery_num` INT(4) NULL DEFAULT 0 COMMENT '初始次数';
alter table `ims_ewei_shop_lottery_join` add `share_num` int(4) null default 0 comment '分享次数';
/**
分享日志
 */
create table `ims_ewei_shop_lottery_share`(
 `id` int(11) not null  AUTO_INCREMENT,
 `uniacid` int(11) null default 0,
 `openid` varchar(50) null,
 `lottery_id` int(11) not null,
 `create_time` int(10) null default 0,
 `mid` int(11) not null,
 primary key (`id`)
);

create table `ims_ewei_shop_lottery_share_join`(
 `id` int(11) not null AUTO_INCREMENT,
 `uniacid` int(11) null default 0,
 `openid` varchar(50) null,
 `lottery_id` int(11) not null,
 `share_num` int(10) null default 0,
 primary key (`id`)
);

/**
存酒信息
 */
alter table `ims_ewei_shop_order_goods` add `repertory_num` int(10) null default 0 comment '存酒数量';
ALTER TABLE `ims_ewei_shop_order` ADD `is_repertory` INT(11) NULL DEFAULT 0 COMMENT '是否存酒';

create table `ims_ewei_shop_repertory`(
  `id` int(11) not null AUTO_INCREMENT,
  `uniacid` int(10) null default 0,
  `goods_id` int(11) not null,
  `thumb` varchar(300) not null,
  `order_id` int(11) not null,
  `order_sn` varchar(50) not null,
  `total` int(4) null default 0 COMMENT '数量',
  `create_time` int(10) null default 0,
  `goods_title` varchar(300) null,
  `openid` varchar(50) not null,
  primary key (`id`)
);