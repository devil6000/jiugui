<?php
//dezend by QQ:4424986  售后更新  维护群：473259627 
?>
<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

if (!(class_exists('RepertoryModel'))) {
	class RepertoryModel extends PluginModel
	{
		public function getSet($uniacid = 0)
		{
			$set = parent::getSet($uniacid);
			$set['texts'] = array();
			return $set;
		}

		public function allow($orderid, $times = 0, $verifycode = '', $openid = ''){
            global $_W;

            if (empty($openid)) {
                $openid = $_W['openid'];
            }

            $uniacid = $_W['uniacid'];
            $store = false;
            $merchid = 0;
            $lastverifys = 0;
            $verifyinfo = false;
            if ($times <= 0) {
                $times = 1;
            }

            $saler = pdo_fetch('select * from ' . tablename('ewei_shop_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
            if (empty($saler)) {
                return error(-1, '无核销权限!');
            }
            //$merchid = $saler['merchid'];
            $order = pdo_get('ewei_shop_repertory', array('uniacid' => $uniacid,'id' => $orderid));
            if(empty($order)){
                return error(-1, '未找到订单');
            }
            if(empty($order['total'])){
                return error(-1, '订单已核销完成,不能核销');
            }

            if(!empty($saler['storeid'])){
                if(0 < $merchid){
                    $store = pdo_fetch('select * from ' . tablename('ewei_shop_merch_store') . ' where id=:id and uniacid=:uniacid and merchid = :merchid limit 1', array(':id' => $saler['storeid'], ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
                }else{
                    $store = pdo_fetch('select * from ' . tablename('ewei_shop_store') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $saler['storeid'], ':uniacid' => $_W['uniacid']));
                }
            }

            return array('order' => $order, 'store' => $store, 'saler' => $saler);
        }

        /**
         * 保存需存酒订单
         * @param $order_id
         */
        public function setOrdeToRepertory($order_id){
            global $_W;
            $uniacid = $_W['uniacid'];
            $order_goods_list = pdo_fetchall('select og.goodsid,og.orderid,og.total,o.openid,o.ordersn,g.thumb,g.title from ' . tablename('ewei_shop_order_goods') . ' og left join ' . tablename('ewei_shop_order') . ' o on og.orderid=o.id left join ' . tablename('ewei_shop_goods') . ' g on og.goodsid=g.id where o.status=3 and o.is_repertory = 1 and uniacid=:uniacid and og.orderid=:orderid', array(':uniacid' => $uniacid,':orderid' => $order_id));
            if(!empty($order_goods_list)){
                $time = time();
                $order_goods_list = tomedia($order_goods_list);
                foreach ($order_goods_list as $key => $item){
                    $insert = array('uniacid' => $_W['uniacid'], 'goods_id' => $item['goodsid'], 'thumb' => $item['thumb'], 'order_id' => $item['orderid'], 'order_sn' => $item['ordersn'], 'total' => $item['total'], 'create_time' => $time, 'goods_title' => $item['title'], 'openid' => $item['openid']);
                    pdo_insert($insert);
                }
            }
        }
	}
}


?>