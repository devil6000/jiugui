<?php
/**
 * Created by PhpStorm.
 * User: appleimac
 * Date: 18/11/8
 * Time: 下午3:35
 */
defined('IN_IA') or exit('Access Denied');

require EWEI_SHOPV2_PLUGIN . 'merch/core/inc/page_merch.php';
class Repertory_EweiShopV2Page extends MerchWebPage{
    public function __construct($_init = false, $_com = 'verify') {
        parent::__construct($_init, $_com);
    }
    public function main(){
        global $_W;
        global $_GPC;
        $pIndex = max(1,intval($_GPC['page']));
        $pSize = 20;

        $count = pdo_fetchcolumn("select count(id) from " .tablename('ewei_shop_repertory_log') . " where uniacid=:uniacid and merchid=:merchid",array(':uniacid' => $_W['uniacid'],':merchid' => $_W['merchid']));
        $pager = pagination2($count, $pIndex, $pSize);

        $list = pdo_fetchall("select * from " . tablename('ewei_shop_repertory_log') . " where uniacid=:uniacid and merchid=:merchid order by create_time desc limit " . ($pIndex -1) * $pSize . "," . $pSize, array(':uniacid' => $_W['uniacid'],':merchid' => $_W['merchid']));
        foreach ($list as &$item){
            $repertory = pdo_fetch("select * from " . tablename('ewei_shop_repertory') . " where uniacid=:uniacid and id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $item['rid']));
            $item['ordersn'] = $repertory['order_sn'];
            $item['goods_title'] = $repertory['goods_title'];
            $item['thumb'] = tomedia($repertory['thumb']);
        }
        unset($item);

        include $this->template();
    }
}