<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Order_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        $condition = "uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        $keyword = $_GPC['keyword'];
        if(!empty($keyword)){
            $condition .= " and goods_title like '%" . $keyword . "%'";
        }

        $list = pdo_fetchall("select * from " . tablename('ewei_shop_repertory') . " where " . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
        foreach($list as &$item){
            $item['thumb'] = tomedia($item['thumb']);
            $item['nickname'] = pdo_getcolumn('ewei_shop_member', array('openid' => $item['openid'], 'uniacid' => $_W['uniacid']),'nickname');
            $item['num'] = $item['total'] - $item['get_num'];
        }
        unset($item);

        $count = pdo_fetchcolumn("select count(id) from " . tablename('ewei_shop_repertory') . " where " . $condition, $params);

        $pager = pagination2($count, $pindex, $psize);

        $total = pdo_fetchcolumn("select sum(total) - sum(get_num) from " . tablename('ewei_shop_repertory') . " where " . $condition, $params);

        load()->func('tpl');
		include $this->template();
	}

	public function detail(){
	    global $_W;
	    global $_GPC;
	    $id = intval($_GPC['id']);

	    $pIndex = max(1,intval($_GPC['page']));
	    $pSize = 20;

	    $list = pdo_fetchall("select * from " . tablename('ewei_shop_repertory_log') . " where uniacid=:uniacid and rid=:id order create_time desc limit " . ($pIndex - 1) * $pSize . "," . $pSize, array(':uniacid' => $_W['uniacid'], ':id' => $id));
	    foreach($list as &$item){
	        if($item['store_id']){
	            $item['store_name'] = pdo_getcolumn('ewei_shop_store', array('uniacid' => $_W['uniacid'], 'id' => $item['store_id']), 'storename');
            }
            $goods = pdo_get('ewei_shop_repertory', array('uniacid' => $_W['uniacid'], 'id' => $item['rid']), array('goods_title','thumb'));
	        $item['goods_title'] = $goods['goods_title'];
	        $item['thumb'] = tomedia($goods['thumb']);
        }
        unset($item);

	    $count = pdo_fetchcolumn("select count(id) from " . tablename('ewei_shop_repertory_log') . " where uniacid=:uniacid and rid=:id ", array(':uniacid' => $_W['uniacid'], ':id' => $id));

	    $pager = pagination2($count, $pIndex, $pSize);

	    load()->func('tpl');
        include $this->template();
    }
}

?>
