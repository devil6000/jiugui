<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Detail_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);
		$p = p('commission');
		$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_order') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		$item['statusvalue'] = $item['status'];
		$item['paytypevalue'] = $item['paytype'];
		$send_member=m('member')->getMember($item['agentid']);
		$item['agent_us']=p('commission')->getInfo($send_member['openid']);
		$item['delivery_us']=pdo_fetch('SELECT dm.*,u.* FROM ' . tablename('ewei_shop_distribution_user') . ' u  ' . ' left join ' . tablename('ewei_shop_member') . '  dm  on dm.openid = u.openid WHERE u.id = ' . $item['delivery_id'] . ' AND u.uniacid=' . $_W['uniacid']);
		
		$isonlyverifygoods = m('order')->checkisonlyverifygoods($item['id']);
		$order_goods = array();

		if (0 < $item['sendtype']) {
			$order_goods = pdo_fetchall('SELECT orderid,goodsid,sendtype,expresssn,expresscom,express,sendtime FROM ' . tablename('ewei_shop_order_goods') . "\r\n            WHERE orderid = " . $id . ' and sendtime > 0 and uniacid=' . $_W['uniacid'] . ' and sendtype > 0 group by sendtype order by sendtime desc ');

			foreach ($order_goods as $key => $value) {
				$order_goods[$key]['goods'] = pdo_fetchall('select g.id,g.title,g.thumb,og.sendtype,g.ispresell,og.realprice from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join ' . tablename('ewei_shop_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid and og.sendtype=' . $value['sendtype'] . ' ', array(':uniacid' => $_W['uniacid'], ':orderid' => $id));
			}

			$item['sendtime'] = $order_goods[0]['sendtime'];
		}

		$shopset = m('common')->getSysset('shop');

		if (empty($item)) {
			$this->message('抱歉，订单不存在!', referer(), 'error');
		}

		if ($_W['ispost']) {
			pdo_update('ewei_shop_order', array('remark' => trim($_GPC['remark'])), array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
			plog('order.op.remarksaler', '订单保存备注  ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
			$this->message('订单备注保存成功！', webUrl('order', array('op' => 'detail', 'id' => $item['id'])), 'success');
		}

		$member = m('member')->getMember($item['openid']);
		$dispatch = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_dispatch') . ' WHERE id = :id and uniacid=:uniacid and merchid=0', array(':id' => $item['dispatchid'], ':uniacid' => $_W['uniacid']));

		if (empty($item['addressid'])) {
			$user = unserialize($item['carrier']);
		}
		else {
			$user = iunserializer($item['address']);

			if (!is_array($user)) {
				$user = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_member_address') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $item['addressid'], ':uniacid' => $_W['uniacid']));
			}

			$address_info = $user['address'];
			$user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['street'] . ' ' . $user['address'];
			$item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
		}

		$refund = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_order_refund') . ' WHERE orderid = :orderid and uniacid=:uniacid order by id desc', array(':orderid' => $item['id'], ':uniacid' => $_W['uniacid']));
		$diyformfields = '';
		$showdiyform = false;

		if (p('diyform')) {
			$diyformfields = ',o.diyformfields,o.diyformdata';
		}

		$goods = pdo_fetchall('SELECT g.*, o.goodssn as option_goodssn, o.productsn as option_productsn,o.total,g.type,o.optionname,o.optionid,o.price as orderprice,o.realprice,o.changeprice,o.oldprice,o.commission1,o.commission2,o.commission3,o.commissions,o.seckill,o.seckill_taskid,o.seckill_roomid' . $diyformfields . ' FROM ' . tablename('ewei_shop_order_goods') . ' o left join ' . tablename('ewei_shop_goods') . ' g on o.goodsid=g.id ' . ' WHERE o.orderid=:orderid and o.uniacid=:uniacid', array(':orderid' => $id, ':uniacid' => $_W['uniacid']));
		$is_merch = false;

		foreach ($goods as &$r) {
			$r['seckill_task'] = false;

			if ($r['seckill']) {
				$r['seckill_task'] = plugin_run('seckill::getTaskInfo', $r['seckill_taskid']);
				$r['seckill_room'] = plugin_run('seckill::getRoomInfo', $r['seckill_taskid'], $r['seckill_roomid']);
			}

			if (!empty($r['option_goodssn'])) {
				$r['goodssn'] = $r['option_goodssn'];
			}

			if (!empty($r['option_productsn'])) {
				$r['productsn'] = $r['option_productsn'];
			}

			$r['marketprice'] = $r['orderprice'] / $r['total'];

			if (p('diyform')) {
				$r['diyformfields'] = iunserializer($r['diyformfields']);
				$r['diyformdata'] = iunserializer($r['diyformdata']);
			}

			if (!empty($r['merchid'])) {
				$is_merch = true;
			}

			if (!empty($r['diyformdata']) && ($r['diyformdata'] != 'false') && !$showdiyform) {
				$showdiyform = true;
			}
		}

		unset($r);
		$item['goods'] = $goods;
		$agents = array();

		if ($p) {
			$agents = $p->getAgents($id);
			$m1 = (isset($agents[0]) ? $agents[0] : false);
			$m2 = (isset($agents[1]) ? $agents[1] : false);
			$m3 = (isset($agents[2]) ? $agents[2] : false);
			$commission1 = 0;
			$commission2 = 0;
			$commission3 = 0;

			foreach ($goods as &$og) {
				$oc1 = 0;
				$oc2 = 0;
				$oc3 = 0;
				$commissions = iunserializer($og['commissions']);

				if (!empty($m1)) {
					if (is_array($commissions)) {
						$oc1 = (isset($commissions['level1']) ? floatval($commissions['level1']) : 0);
					}
					else {
						$c1 = iunserializer($og['commission1']);
						$l1 = $p->getLevel($m1['openid']);
						$oc1 = (isset($c1['level' . $l1['id']]) ? $c1['level' . $l1['id']] : $c1['default']);
					}

					$og['oc1'] = $oc1;
					$commission1 += $oc1;
				}

				if (!empty($m2)) {
					if (is_array($commissions)) {
						$oc2 = (isset($commissions['level2']) ? floatval($commissions['level2']) : 0);
					}
					else {
						$c2 = iunserializer($og['commission2']);
						$l2 = $p->getLevel($m2['openid']);
						$oc2 = (isset($c2['level' . $l2['id']]) ? $c2['level' . $l2['id']] : $c2['default']);
					}

					$og['oc2'] = $oc2;
					$commission2 += $oc2;
				}

				if (!empty($m3)) {
					if (is_array($commissions)) {
						$oc3 = (isset($commissions['level3']) ? floatval($commissions['level3']) : 0);
					}
					else {
						$c3 = iunserializer($og['commission3']);
						$l3 = $p->getLevel($m3['openid']);
						$oc3 = (isset($c3['level' . $l3['id']]) ? $c3['level' . $l3['id']] : $c3['default']);
					}

					$og['oc3'] = $oc3;
					$commission3 += $oc3;
				}
			}

			unset($og);
			$commission_array = array($commission1, $commission2, $commission3);

			foreach ($agents as $key => $value) {
				$agents[$key]['commission'] = $commission_array[$key];

				if (2 < $key) {
					unset($agents[$key]);
				}
			}
		}

		$condition = ' o.uniacid=:uniacid and o.deleted=0';
		$paras = array(':uniacid' => $_W['uniacid']);
		$totals = array();
		$coupon = false;
		if (com('coupon') && !empty($item['couponid'])) {
			$coupon = com('coupon')->getCouponByDataID($item['couponid']);
		}

		$order_fields = false;
		$order_data = false;

		if (p('diyform')) {
			$diyform_set = p('diyform')->getSet();

			foreach ($goods as $g) {
				if (!empty($g['diyformdata'])) {
					break;
				}
			}

			if (!empty($item['diyformid'])) {
				$orderdiyformid = $item['diyformid'];

				if (!empty($orderdiyformid)) {
					$order_fields = iunserializer($item['diyformfields']);
					$order_data = iunserializer($item['diyformdata']);
				}
			}
		}

		if (com('verify')) {
			$verifyinfo = iunserializer($item['verifyinfo']);

			if (!empty($item['verifyopenid'])) {
				$saler = m('member')->getMember($item['verifyopenid']);

				if (empty($item['merchid'])) {
					$saler['salername'] = pdo_fetchcolumn('select salername from ' . tablename('ewei_shop_saler') . ' where openid=:openid and uniacid=:uniacid limit 1 ', array(':uniacid' => $_W['uniacid'], ':openid' => $item['verifyopenid']));
				}
				else {
					$saler['salername'] = pdo_fetchcolumn('select salername from ' . tablename('ewei_shop_merch_saler') . ' where openid=:openid and merchid=:merchid and uniacid=:uniacid limit 1 ', array(':uniacid' => $_W['uniacid'], ':openid' => $item['verifyopenid'], ':merchid' => $item['merchid']));
				}
			}

			if (!empty($item['verifystoreid'])) {
				if (empty($item['merchid'])) {
					$store = pdo_fetch('select * from ' . tablename('ewei_shop_store') . ' where id=:storeid limit 1 ', array(':storeid' => $item['verifystoreid']));
				}
				else {
					$store = pdo_fetch('select * from ' . tablename('ewei_shop_merch_store') . ' where id=:storeid limit 1 ', array(':storeid' => $item['verifystoreid']));
				}
			}

			if ($item['isverify']) {
				if (is_array($verifyinfo)) {
					if (empty($item['dispatchtype'])) {
						foreach ($verifyinfo as &$v) {
							if ($v['verified'] || ($item['verifytype'] == 1)) {
								if (empty($item['merchid'])) {
									$v['storename'] = pdo_fetchcolumn('select storename from ' . tablename('ewei_shop_store') . ' where id=:id limit 1', array(':id' => $v['verifystoreid']));
								}
								else {
									$v['storename'] = pdo_fetchcolumn('select storename from ' . tablename('ewei_shop_merch_store') . ' where id=:id limit 1', array(':id' => $v['verifystoreid']));
								}

								if (empty($v['storename'])) {
									$v['storename'] = '总店';
								}

								$v['nickname'] = pdo_fetchcolumn('select nickname from ' . tablename('ewei_shop_member') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $v['verifyopenid'], ':uniacid' => $_W['uniacid']));

								if (empty($item['merchid'])) {
									$v['salername'] = pdo_fetchcolumn('select salername from ' . tablename('ewei_shop_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $v['verifyopenid'], ':uniacid' => $_W['uniacid']));
								}
								else {
									$v['salername'] = pdo_fetchcolumn('select salername from ' . tablename('ewei_shop_merch_saler') . ' where openid=:openid and merchid=:merchid and uniacid=:uniacid limit 1', array(':openid' => $v['verifyopenid'], ':uniacid' => $_W['uniacid'], ':merchid' => $item['merchid']));
								}
							}
						}

						unset($v);
					}
				}
			}
		}

		load()->func('tpl');
		include $this->template();
		exit();
	}
	public function check_agent_send(){
		global $_W,$_GPC;

		$id=$_GPC['id'];
		$agent_send=$_GPC['agent_send'];
		$item = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_order') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if(empty($item)){
			show_json(0,'未找到此订单，请刷新重试！');
		}
		if($item['status']==2){
			show_json(1,'此订单已发货，无需重新发货！');
		}
		if(empty($id)){
			show_json(0,'参数错误，请刷新重试！');
		}
		if($agent_send==2){
			$senddata['delivery_id']=$_GPC['v_id'];
			
			//这里判断是否选择配送员
//			show_json(0,'参数错误!');
		}
		$send_member=m('member')->getMember($item['agentid']);
		$send_member=p('commission')->getInfo($send_member['openid']);
		if($agent_send==1){
			if(empty($item['agentid']) || $send_member['agent_level']['levelname']!='个人代理'){
				show_json(0,'此订单上级不是个人代理，无法分配上级发货！');
			}
		}
		$senddata['agent_send']=$agent_send;
		if(!empty($agent_send)){
			$data['sendtype']=1;
			$senddata['status'] = 2;
		}else{
			$data['sendtype']=0;
			$senddata['status'] = 1;
		}
		
		//下面是发送信息内容
		$item['address']=iunserializer($item['address']);
		$item['address']['address_area']=$item['address']['province'].' '.$item['address']['city'].' '.$item['address']['area'].' '.$item['address']['address'];
		$order_goods=pdo_fetchall('SELECT g.title,og.total,og.optionname FROM ' . tablename('ewei_shop_order_goods') . ' as og left join ' . tablename('ewei_shop_goods') . ' as g on g.id=og.goodsid WHERE og.orderid = :id and og.uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		$og_title='';
		foreach($order_goods as $val){
			$og_title[]= $val['title'].' '.$val['optionname'] .' x'.$val['total'];
		}
		$item['goods']=implode(',', $og_title);
		
		if($agent_send==1){
			//先获取接收人openid
			$send_member=m('member')->getMember($item['agentid']);
			//发送通知
			p('commission')->sendMessage($send_member['openid'], array('order'=>$item,'nickname' => $send_member['nickname'], 'agenttime' => time()), TM_COMMISSION_ANGENT_SEND);
		}elseif($agent_send==2){
			$send_member=pdo_fetch('SELECT u.openid FROM ' . tablename('ewei_shop_distribution_user') . ' u  ' . ' left join ' . tablename('ewei_shop_member') . '  dm  on dm.openid = u.openid WHERE u.id = ' . $senddata['delivery_id']. ' AND u.uniacid=' . $_W['uniacid']);
			
			p('commission')->sendMessage($send_member['openid'], array('order'=>$item,'nickname' => $send_member['nickname'], 'agenttime' => time()), TM_COMMISSION_ANGENT_SEND);
		}
		pdo_update('ewei_shop_order_goods', $data, array('orderid' => $id, 'uniacid' => $_W['uniacid']));
		pdo_update('ewei_shop_order', $senddata, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
		show_json(1,'操作成功！');
	}
}

?>
