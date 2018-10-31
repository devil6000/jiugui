<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30
 * Time: 21:02
 */
error_reporting(0);
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/ewei_shopv2/defines.php';
require '../../../../../addons/ewei_shopv2/core/inc/functions.php';
require '../../../../../addons/ewei_shopv2/core/inc/plugin_model.php';
global $_W;
global $_GPC;
ignore_user_abort();
set_time_limit(0);
$sets = pdo_fetchall('select uniacid from ' . tablename('ewei_shop_sysset'));
foreach($sets as $set){
    $_W['uniacid'] = $set['uniacid'];
    if (empty($_W['uniacid'])) {
        continue;
    }

    $users = pdo_fetchall('select openid,repertory,realname from ' . tablename('ewei_shop_member') . ' where uniacid=:uniacid and isrepertory=1', array(':uniacid' => $_W['uniacid']));
    if(!empty($users)){
        $repertory_set = m('common')->getPluginset('repertory');
        empty($repertory_set['repertory']) && $repertory_set['repertory'] = 1;
        foreach($users as $user){
            $get_credits = floor($user['repertory'] / $repertory_set['repertory']);
            $get_credits *= $repertory_set['repertory_credit'];
            $remark = '拥有存酒' . $user['repertory'] . ' 件,自动获取 ' . $get_credits . ' 积分';
            m('member')->setCredit($user['openid'], 'credit1', $get_credits, $remark);
            if(p('repertory')){
                p('repertory')->sendMessage(array('openid' => $user['openid'], 'nickname' => $user['nickname'], 'total' => $user['repertory'], 'credit' => $get_credits, 'time' => time()), 'repertory_credit');
            }
        }
    }
}