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

            $saler = pdo_fetch('select * from ' . tablename('ewei_shop_merch_saler') . ' where openid=:openid and uniacid=:uniacid and status=1 limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));   //判断在其他门店是否有核销员
            if(empty($saler)){ //没有判断总店是否有核销员
                $saler = pdo_fetch('select * from ' . tablename('ewei_shop_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
            }
            if (empty($saler)) {
                return error(-1, '无核销权限!');
            }

            /*
            if(!empty($saler['storeid'])){
                if(0 < $merchid){
                    $store = pdo_fetch('select * from ' . tablename('ewei_shop_merch_store') . ' where id=:id and uniacid=:uniacid and merchid = :merchid limit 1', array(':id' => $saler['storeid'], ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
                }else{
                    $store = pdo_fetch('select * from ' . tablename('ewei_shop_store') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $saler['storeid'], ':uniacid' => $_W['uniacid']));
                }
            }
            */

            $total = $order['total'] - $order['get_num'];   //获取剩余数量

            $carrier = unserialize($order['carrier']);

            return array('order' => $order, 'store' => $store, 'saler' => $saler, 'carrier' => $carrier, 'total' => $total);
        }

        /**
         * 保存需存酒订单
         * @param $order_id
         */
        public function setOrdeToRepertory($order_id){
            global $_W;
            $uniacid = $_W['uniacid'];
            $order = pdo_fetch('select openid, ordersn, verifycode, carrier from ' . tablename('ewei_shop_order') . ' where uniacid=:uniacid and status=3 and id=:id and dispatchtype=2 limit 1', array(':uniacid' => $uniacid, ':id' => $order_id));
            if(!empty($order)){
                $member = pdo_get('ewei_shop_member', array('openid' => $order['openid'], 'uniacid' => $uniacid), array('id','repertory', 'nickname'));
                $order_goods_list = pdo_fetchall('select og.goodsid,og.total,og.optionid,og.optionname,g.thumb,g.title,g.marketprice,g.bottle from ' . tablename('ewei_shop_order_goods') . ' og left join ' . tablename('ewei_shop_goods') . ' g on og.goodsid=g.id where og.orderid=:orderid', array(':orderid' => $order_id));
                $num = $member['repertory'];
                $time = time();
                $total = 0;
                foreach ($order_goods_list as $key => $item){
                    $num += $item['bottle'];
                    $total += $item['bottle'];
                    $insert = array('uniacid' => $_W['uniacid'], 'goods_id' => $item['goodsid'], 'thumb' => $item['thumb'], 'option_id' => $item['optionid'], 'option_name' => $item['optionname'], 'goods_price' => $item['marketprice'], 'order_id' => $order_id, 'order_sn' => $order['ordersn'], 'total' => $item['bottle'], 'create_time' => $time, 'goods_title' => $item['title'], 'openid' => $order['openid'], 'verifycode' => $order['verifycode'], 'carrier' => $order['carrier'], 'get_num' => 0, 'status' => 0);
                    pdo_insert('ewei_shop_repertory',$insert);
                }
                pdo_update('ewei_shop_member', array('isrepertory' => 1, 'repertory' => $num, 'repertorytime' => time()), array('openid' => $order['openid'], 'uniacid' => $uniacid));
                $this->sendMessage(array('openid' => $member['nickname'], 'num' => $total, 'applytime' => $time),'repertory_apply');
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

            if (empty($openid)) {
                $openid = $_W['openid'];
            }

            $data = $this->allow($orderid, $times, $openid);

            if (is_error($data)) {
                return NULL;
            }

            extract($data);
            $order = pdo_fetch('select * from ' . tablename('ewei_shop_repertory') . ' where id=:id and uniacid=:uniacid', array(':id' => $orderid, ':uniacid' => $uniacid));
            if(empty($order['status'])){
                $get_num = $order['get_num'] + $times;
                $status = ($order['total'] - $get_num) <= 0 ? 1 : 0;
                $data = array('get_num' => $get_num, 'status' => $status);
                pdo_update('ewei_shop_repertory', $data, array('id' => $orderid, 'uniacid' => $uniacid));
                $member = m('member')->getMember($order['openid']);
                $num = $member['repertory'] - $times;
                pdo_update('ewei_shop_member', array('repertory' => $num), array('openid' => $order['openid'], 'uniacid' => $uniacid));
                plog('reportory.verify.complete', '核销酒水 ' . $order['goods_title'] . ' ' . $times . ' 件');
                //保存取酒信息
                $saler = pdo_fetch('select * from ' . tablename('ewei_shop_merch_saler') . ' where openid=:openid and uniacid=:uniacid and status=1 limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
                if(empty($saler)){
                    $saler = pdo_fetch('select * from ' . tablename('ewei_shop_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
                }

                $insert = array('uniacid' => $uniacid, 'store_id' => $saler['storeid'], 'verify_openid' => $saler['openid'], 'rid' => $order['id'], 'total' => $times, 'create_time' => time(), 'verify_name' => $saler['salername'], 'merchid' => $saler['merchid']);
                pdo_insert('ewei_shop_repertory_log', $insert);

                if($saler['merchid'] > 0){
                    $goods = pdo_fetch("select subsidy from " . tablename('ewei_shop_goods') . " where id=:id and uniacid=:uniacid", array(':id' => $order['goods_id'], ':uniacid' => $uniacid));
                    $balance = $goods['subsidy'] * $times;
                    $shop = pdo_fetch('select * from ' . tablename('ewei_shop_merch_user') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $uniacid, ':id' => $saler['merchid']));
                    m('member')->setCredit($shop['payopenid'], 'credit2', $balance, array(0, $shop['merchname'] . '核销存酒，订单号: ' . $order['order_sn'] . '数量：' . $times . '瓶,返余额：' . $balance . ' 元'));
                }

                $this->sendMessage(array('openid' => $order['openid'], 'nickname' => $member['nickname'], 'num' => $times, 'title' => $order['goods_title'], 'verifytime' => time()), 'repertory_verify');
            }

            return true;
        }

        public function sendMessage($sendData, $message_type)
        {
            $notice = m('common')->getPluginset('repertory');
            $tm = $notice['tm'];

            if (($message_type == 'repertory_credit') && empty($usernotice['repertory_credit'])) {

                $tm['msguser'] = $sendData['openid'];
                $data = array('[昵称]' => $sendData['nickname'], '[存酒数量]' => $sendData['total'], '[积分]' => $sendData['credit'], '[时间]' => date('Y-m-d H:i:s', $sendData['time']));
                $message = array('keyword1' => (!(empty($tm['repertory_credittitle'])) ? $tm['repertory_credittitle'] : '存酒获取积分通知'), 'keyword2' => (!(empty($tm['repertory_credit'])) ? $tm['repertory_credit'] : '[昵称]，您共存酒[存酒数量]件 ，可获得[积分]积分'));
                return $this->sendNotice($tm, 'repertory_credit_advanced', $data, $message);
            }


            if (($message_type == 'repertory_apply') && empty($usernotice['repertory_apply'])) {

                $tm['msguser'] = $sendData['openid'];
                $data = array('[昵称]' => $sendData['nickname'], '[数量]' => $sendData['num'], '[时间]' => date('Y-m-d H:i:s', $sendData['applytime']));
                $message = array('keyword1' => (!(empty($tm['repertory_applytitle'])) ? $tm['repertory_applytitle'] : '存酒通知'), 'keyword2' => (!(empty($tm['repertory_apply'])) ? $tm['repertory_apply'] : '[昵称]在[时间]成功存酒[数量]件.请到后台查看~'));
                return $this->sendNotice($tm, 'repertory_apply_advanced', $data, $message);
            }

            if(($message_type == 'repertory_verify') && empty($usernotice['repertory_verify'])){
                $tm['msguser'] = $sendData['openid'];
                $data = array('[昵称]' => $sendData['nickname'], '[件数]' => $sendData['num'], '[商品名称]' => $sendData['title'], '[时间]' => date('Y-m-d H:i:s', $sendData['verifytime']));
                $message = array('keyword1' => (!(empty($tm['repertory_verifytitle'])) ? $tm['repertory_verifytitle'] : '核销成功通知'), 'keyword2' => (!(empty($tm['repertory_verify'])) ? $tm['repertory_verify'] : '[昵称]在[时间]成功消费[件数]件[商品名称].请到后台查看~'));
                return $this->sendNotice($tm, 'repertory_verify_advanced', $data, $message);
            }

        }

        protected function sendNotice($tm, $tag, $datas, $message)
        {
            global $_W;

            if (!(empty($tm['is_advanced'])) && !(empty($tm[$tag]))) {

                $advanced_template = pdo_fetch('select * from ' . tablename('ewei_shop_member_message_template') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $tm[$tag], ':uniacid' => $_W['uniacid']));


                if (!(empty($advanced_template))) {

                    $url = ((!(empty($advanced_template['url'])) ? $this->replaceArray($datas, $advanced_template['url']) : ''));
                    $advanced_message = array(
                        'first'  => array('value' => $this->replaceArray($datas, $advanced_template['first']), 'color' => $advanced_template['firstcolor']),
                        'remark' => array('value' => $this->replaceArray($datas, $advanced_template['remark']), 'color' => $advanced_template['remarkcolor'])
                    );
                    $data = iunserializer($advanced_template['data']);


                    foreach ($data as $d ) {

                        $advanced_message[$d['keywords']] = array('value' => $this->replaceArray($datas, $d['value']), 'color' => $d['color']);
                    }



                    $tm['templateid'] = $advanced_template['template_id'];
                    $this->sendMoreAdvanced($tm, $tag, $advanced_message, $url);
                }

            }

            else {

                $tag = str_replace('_advanced', '', $tag);
                $this->sendMore($tm, $message, $datas);
            }



            return true;
        }

        protected function sendMore($tm, $message, $datas)
        {
            $message['keyword2'] = $this->replaceArray($datas, $message['keyword2']);
            $msg = array(
                'keyword1' => array('value' => $message['keyword1'], 'color' => '#73a68d'),
                'keyword2' => array('value' => $message['keyword2'], 'color' => '#73a68d')
            );
            $openid = $tm['msguser'];
            if(!empty($tm['templateid'])){
                $send = m('message')->sendTplNotice($openid, $tm['templateid'], $msg);
            }else{
                m('message')->sendCustomNotice($openid, $msg);
            }
        }

        protected function replaceArray(array $array, $message) {

            foreach ($array as $key => $value) {

                $message = str_replace($key, $value, $message);

            }

            return $message;

        }

        protected function sendMoreAdvanced($tm, $tag, $msg, $url)
        {
            if ($tm['msguser'] == 1) {

                $openid = $tm['applyopenid'];
            }

            else {

                $openid = $tm['openid'];
            }



            if (!(empty($openid))) {

                foreach ($openid as $openid ) {

                    if (!(empty($tm[$tag])) && !(empty($tm['templateid']))) {

                        m('message')->sendTplNotice($openid, $tm['templateid'], $msg, $url);
                    }

                    else {

                        m('message')->sendCustomNotice($openid, $msg, $url);
                    }

                }

            }
        }
	}
}


?>