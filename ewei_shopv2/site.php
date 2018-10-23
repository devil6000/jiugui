<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}


if (!(function_exists('getIsSecureConnection'))) {
function getIsSecureConnection()
{
	if (isset($_SERVER['HTTPS']) && (('1' == $_SERVER['HTTPS']) || ('on' == strtolower($_SERVER['HTTPS'])))) {
		return true;
	}


	if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
		return true;
	}


	return false;
}
}


if (function_exists('getIsSecureConnection')) {
	$secure = getIsSecureConnection();
	$http = (($secure ? 'https' : 'http'));
	$_W['siteroot'] = ((strexists($_W['siteroot'], 'https://') ? $_W['siteroot'] : str_replace('http', $http, $_W['siteroot'])));
}


require_once IA_ROOT . '/addons/ewei_shopv2/version.php';
require_once IA_ROOT . '/addons/ewei_shopv2/defines.php';
require_once EWEI_SHOPV2_INC . 'functions.php';
class Ewei_shopv2ModuleSite extends WeModuleSite
{
	public  function __construct(){
		global $_W,$_GPC;
		if($_GPC['token']=='send'){
			ini_set("display_errors", "On");
			error_reporting( E_STRICT);
			
			$id='164';
			$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_order') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
			$item['address']=iunserializer($item['address']);
			$item['address']['address_area']=$item['address']['province'].' '.$item['address']['city'].' '.$item['address']['area'].' '.$item['address']['address'];
			$order_goods=pdo_fetchall('SELECT g.title,og.total,og.optionname FROM ' . tablename('ewei_shop_order_goods') . ' as og left join ' . tablename('ewei_shop_goods') . ' as g on g.id=og.goodsid WHERE og.orderid = :id and og.uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
			$og_title='';
			foreach($order_goods as $val){
				$og_title[]= $val['title'].' '.$val['optionname'] .' x'.$val['total'];
			}
			$item['goods']=implode(',', $og_title);

			$send_member=m('member')->getMember($item['agentid']);
			p('commission')->sendMessage($send_member['openid'], array('order'=>$item,'nickname' => $send_member['nickname'], 'agenttime' => time()), TM_COMMISSION_ANGENT_SEND);
			die;
		}
	}
	public function getMenus()
	{
		global $_W;
		return array(
	array('title' => '管理后台', 'icon' => 'fa fa-shopping-cart', 'url' => webUrl())
	);
	}

	public function doWebWeb()
	{
		m('route')->run();
	}

	public function doMobileMobile()
	{
		m('route')->run(false);
	}

	public function payResult($params)
	{
		return m('order')->payResult($params);
	}
}


?>