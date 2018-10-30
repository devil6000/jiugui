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
            if($times <= 0){
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
            if($order['status'] == 1 || ($order['total'] <= $order['get_num'])){
                return error(-1, '订单已核销完成,不能核销');
            }

            if($times > ($order['total'] - $order['get_num'])){
                return error(-1, '核销数量不足');
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
            $order_goods_list = pdo_fetchall('select og.goodsid,og.orderid,og.total,o.openid,o.ordersn,g.thumb,g.title,o.verifycode from ' . tablename('ewei_shop_order_goods') . ' og left join ' . tablename('ewei_shop_order') . ' o on og.orderid=o.id left join ' . tablename('ewei_shop_goods') . ' g on og.goodsid=g.id where o.status=3 and o.dispatchtype=2 and og.uniacid=:uniacid and og.orderid=:orderid', array(':uniacid' => $uniacid,':orderid' => $order_id));
            if(!empty($order_goods_list)){
                $time = time();
                foreach ($order_goods_list as $key => $item){
                    $insert = array('uniacid' => $_W['uniacid'], 'goods_id' => $item['goodsid'], 'thumb' => $item['thumb'], 'order_id' => $item['orderid'], 'order_sn' => $item['ordersn'], 'total' => $item['total'], 'create_time' => $time, 'goods_title' => $item['title'], 'openid' => $item['openid'], 'verifycode' => $item['verifycode'], 'get_num' => 0, 'status' => 0);
                    pdo_insert('ewei_shop_repertory',$insert);
                }
            }
        }

        /**
         * 获取存酒数量
         * @param $openid
         * @return mixed
         */
        public function getNumber($openid){
            global $_W;
            $uniacid = $_W['uniacid'];
            $count = pdo_fetchcolumn('select (sum(total) - sum(get_num)) as num from ' . tablename('ewei_shop_repertory') . ' where openid=:openid and uniacid=:uniacid and status=0 limit 1', array(':openid' => $openid, ':uniacid' => $uniacid));
            return $count;
        }

        public function verify($orderid = 0, $times = 0, $verifycode = '', $openid = '')
        {
            global $_W;
            global $_GPC;
            $uniacid = $_W['uniacid'];
            $current_time = time();

            if (empty($openid)) {
                $openid = $_W['openid'];
            }

            $data = $this->allow($orderid, $times, $openid);

            if (is_error($data)) {
                return NULL;
            }

            extract($data);
            if(empty($data['status'])){
                $get_num = $data['get_num'] + $times;
                $status = ($data['total'] - $get_num) <= 0 ? 1 : 0;
                $update = array('get_num' => $get_num, 'status' => $status);
                pdo_update('ewei_shop_repertory', $update, array('id' => $orderid, 'uniacid' => $uniacid));

            }

            return true;
        }
	}
}


?>