<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Check_EweiShopV2Page extends MobileLoginPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$id = intval($_GPC['id']);
		$saler = pdo_fetch('select * from ' . tablename('ewei_shop_merch_saler') . ' where uniacid=:uniacid and openid=:openid and status=1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
		if(empty($saler)){
			$this->message('无核销权限!', 'close', 'error');
		}
		$record = pdo_fetch('select * from ' . tablename('mon_qmshake_record') . ' where id=:id ', array(':id' => $id));

		if (empty($record)) {
			$this->message('提货券不存在!', 'close', 'error');
		}
		include $this->template();
	}
	public function submit()
	{
		global $_W;
		global $_GPC;
		$openid = $_W['openid'];
		$id = intval($_GPC['id']);
		$saler = pdo_fetch('select * from ' . tablename('ewei_shop_merch_saler') . ' where uniacid=:uniacid and openid=:openid and status=1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
		if(empty($saler)){
			show_json(0, '无核销权限!');
		}
		$record = pdo_fetch('select * from ' . tablename('mon_qmshake_record') . ' where id=:id ', array(':id' => $id));

		if (empty($record)) {
			show_json(0, '提货券不存在!');
		}
		if($record['status']>1){
			show_json(0, '提货券已核销或过期!');
		}
		$data = array(
			'status' => 2,
			'djtime' => time(),
			'salername' => $saler['salername']
		);
		$result = pdo_update('mon_qmshake_record', $data, array('id' => $record['id']));
		show_json(1);
	}
}

?>
