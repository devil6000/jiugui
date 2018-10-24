ALTER TABLE `ims_ewei_shop_lottery` ADD `lottery_num` INT(4) NULL DEFAULT 0 COMMENT '抽奖默认次数';
alter table `ims_ewei_shop_lottery_join` add `share_num` int(4) null default 0 comment '已分享次数';
/**
分享表，保存活动分享次数
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

/**
存酒功能
 */
ALTER TABLE `ims_ewei_shop_order` ADD `is_repertory` INT(11) NULL DEFAULT 0 COMMENT '是否为存酒订单，0:不是，1:是';