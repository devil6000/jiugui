<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginMobilePage
{
	public function main() {
		include $this->template();
	}

	public function get_list(){
        global $_W;
        global $_GPC;

        $pIndex = max(1,intval($_GPC['page']));
        $pSize = 6;
        $openid = $_W['openid'];

        $status = intval($_GPC['status']);
        $conditions = 'uniacid=:uniacid and openid=:openid';
        if($status > 0 ){
            $conditions .= ' and status=' . ($status - 1);
        }

        $total = pdo_fetchcolumn('select count(id) from ' .  tablename('ewei_shop_repertory') . ' where ' . $conditions, array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
        $list = pdo_fetchall('select * from ' . tablename('ewei_shop_repertory') . ' where ' . $conditions . ' order by id desc limit ' . ($pIndex - 1) * $pSize . ',' . $pSize, array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
        $list = set_medias($list, 'thumb');
        show_json(1, array('list' => $list, 'pagesize' => $pSize, 'total' => $total));
	}
}

?>
