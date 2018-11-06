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
  `verifycode` varchar(255) not null COMMENT '核销码',
  `get_num` int(4) NULL DEFAULT 0 COMMENT '已取用数量',
  `status` int(4) NULL DEFAULT 0 COMMENT '状态,1已用完',
  `carrier` TEXT,
  `goods_price` DECIMAL(10,2) NULL DEFAULT 0 COMMENT '商品价格',
  `option_id` INT(11) NULL DEFAULT 0 COMMENT '商品属性ID',
  `option_name` VARCHAR(300) NULL COMMENT '商品属性名称',
  primary key (`id`)
);

alter table `ims_ewei_shop_goods` add `bottle` int(10) null default 0 comment '瓶数';

create table `ims_ewei_shop_repertory_log`(
  `id` int(11) not null AUTO_INCREMENT,
  `uniacid` int(10) null default 0,
  `store_id` int(11) null default 0 comment '核销门店ID',
  `verify_openid` varchar(50) null comment '核销员',
  `rid` int(11) not null comment '核销存酒ID',
  `total` int(4) not null comment '核销数量',
  `create_time` int(6) null,
  `verify_name` varchar(100) null,
  primary key (`id`)
);

/**
会员信息增加
 */

alter table `ims_ewei_shop_member` add store_name varchar(300) null comment '餐饮店名称，只有餐饮店会员显示';
alter table `ims_ewei_shop_member` add contacts varchar(100) null comment '联系人，只有餐饮店会员显示';
alter table `ims_ewei_shop_member` add tel varchar(20) null comment '电话，只有餐饮店会员显示';

create table `ims_ewei_shop_restaurant_apply`(
  `id` int(11) not null AUTO_INCREMENT,
  `uniacid` int(10) null default 0,
  `openid` varchar(50) not null,
  `store_name` varchar(300) null comment '餐饮店名称',
  `contacts` varchar(100) null comment '联系人',
  `tel` varchar(20) null comment '电话',
  `create_time` int(6) null default 0,
  `status` int(1) null default 0 comment '状态,0待审核，1通过，2不通过',
  `remark` text,
  `apply_time` int(6) null default 0,
  primary key (`id`)
);

/**
商品管理－余额
 */
ALTER TABLE `ims_ewei_shop_goods` add `subsidy` DECIMAL(10,2) null DEFAULT 0 comment '补贴';
ALTER TABLE `ims_ewei_shop_store` add `merch_id` INT(11) null DEFAULT 0 comment '多商户ID';