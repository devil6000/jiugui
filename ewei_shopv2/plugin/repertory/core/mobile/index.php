<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginMobilePage
{
	public function main()
	{
		global $_W;
		global $_GPC;

		$openid = $_W['openid'];
		$list = pdo_fetchall('select * from ' . tablename('ewei_shop_order_goods') . " og left join " . tablename('ewei_shop_order') . " o on og.orderid=o.id left join " . tablename('ewei_shop_goods') . " g on og.goodsid=g.id where o.status=3");


		include $this->template();
	}
}

?>
