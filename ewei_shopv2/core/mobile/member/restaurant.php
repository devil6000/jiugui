<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/1
 * Time: 20:52
 */
defined('IN_IA') or exit('Access Denied');

class Restaurant_EweiShopV2Page extends MobilePage{

    public function main(){
        global $_W;
        $openid = $_W['openid'];
        $apply = pdo_get('ewei_shop_restaurant_apply', array('openid' => $openid, 'uniacid' => $_W['uniacid']));
        include $this->template();
    }

    public function apply(){
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        if($_W['ispost']){
            $insert = array(
                'uniacid' => $_W['uniacid'],
                'openid' => $openid,
                'store_name' => $_GPC['store_name'],
                'contacts' => $_GPC['contacts'],
                'tel' => $_GPC['tel'],
                'store_name' => time()
            );

            pdo_insert('ewei_shop_restaurant_apply', $insert);
            show_json(1);
        }
    }
}