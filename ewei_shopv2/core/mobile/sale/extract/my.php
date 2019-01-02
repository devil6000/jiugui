<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class My_EweiShopV2Page extends MobileLoginPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$set = m('common')->getPluginset('coupon');
		com('coupon')->setShare();
		include $this->template();
	}

	public function detail()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$record = pdo_fetch('select * from ' . tablename('mon_qmshake_record') . ' where id=:id ', array(':id' => $id));

		if (empty($record)) {
			header('location: ' . mobileUrl('sale/extract/my'));
			exit();
		}
		
		include $this->template();
	}

	public function getlist()
	{
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$status = intval($_GPC['status']);

		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$time = time();
		$sql = 'select r.id, r.status, p.pimg, p.pname from ' . tablename('mon_qmshake_record') . ' r ';
		$sql .= ' left join ' . tablename('mon_qmshake_prize') . ' p on r.pid = p.id';
		$sql .= ' left join ' . tablename('mon_qmshake') . ' s on r.sid = s.id';
		$sql .= ' where r.openid=:openid and s.weid=:uniacid and p.ptype=3 and r.status=:status';

		$total = pdo_fetchcolumn($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid'], ':status' => $status));
		$sql .= ' order by r.createtime desc  LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
		$coupons = pdo_fetchall($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid'], ':status' => $status));

		if (empty($coupons)) {
			$coupons = array();
		}

		show_json(1, array('list' => $coupons, 'pagesize' => $psize, 'total' => $total));
	}

	private function _condition($args)
	{
		global $_GPC;
		$merch_plugin = p('merch');
		$merch_data = m('common')->getPluginset('merch');
		if ($merch_plugin && $merch_data['is_openmerch']) {
			$args['merchid'] = intval($_GPC['merchid']);
		}
		$args['merchid'] = -1; //只显示主商城商品，其他商品不能购买

		if (isset($_GPC['nocommission'])) {
			$args['nocommission'] = intval($_GPC['nocommission']);
		}

		$goods = m('goods')->getListbyCoupon($args);
		show_json(1, array('list' => $goods['list'], 'total' => $goods['total'], 'pagesize' => $args['pagesize']));
	}
}

?>
