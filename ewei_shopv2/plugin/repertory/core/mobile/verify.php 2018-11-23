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
        $verifycode = pdo_fetchcolumn('select verifycode from ' . tablename('ewei_shop_repertory') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $orderid));
        $query = array('id' => $orderid, 'verifycode' => $verifycode, 'times' => intval($_GPC['times']));
        //$url = mobileUrl('repertory/verify/detail', $query, true);
        $url = mobileUrl('repertory/verify/complete', $query, true);
        show_json(1, array('url' => m('qrcode')->createQrcode($url)));
    }

    public function detail(){
        global $_W;
        global $_GPC;
        $orderid = intval($_GPC['id']);
        /*
        $data = p('repertory')->allow($orderid);

        if (is_error($data)) {
            $this->message($data['message']);
        }

        extract($data);
        */
        $order = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_repertory') . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $orderid));
        include $this->template();
    }

    public function complete(){
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $uniacid = $_W['uniacid'];
        $orderid = intval($_GPC['id']);
        $times = intval($_GPC['times']);
        p('repertory')->verify($orderid, $times);
        $this->message(array('title' => '操作完成', 'message' => '您可以退出浏览器了'), 'javascript:WeixinJSBridge.call("closeWindow");', 'success');
    }

    public function success(){
        global $_W;
        global $_GPC;
        $id = intval($_GPC['orderid']);
        $times = intval($_GPC['times']);
        $this->message(array('title' => '操作完成', 'message' => '您可以退出浏览器了'), 'javascript:WeixinJSBridge.call("closeWindow");', 'success');
    }
}