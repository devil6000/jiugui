<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/27
 * Time: 22:30
 */
defined('IN_IA') or exit('Access Denied');

class Verify_EweiShopV2Page extends PluginMobilePage{
    public function qrcode()
    {
        global $_W;
        global $_GPC;
        $orderid = intval($_GPC['id']);
        $verifycode = $_GPC['verifycode'];
        $query = array('id' => $orderid, 'verifycode' => $verifycode);
        $url = mobileUrl('repertory/verify/detail', $query, true);
        show_json(1, array('url' => m('qrcode')->createQrcode($url)));
    }

    public function detail(){
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $uniacid = $_W['uniacid'];
        $orderid = intval($_GPC['id']);
        $data = p('repertory')->allow($orderid);

        if (is_error($data)) {
            $this->message($data['message']);
        }

        extract($data);
        include $this->template();
    }
}