<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Agent_EweiShopV2Page extends PluginWebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$params = array();
		$condition = '';
		$keyword = trim($_GPC['keyword']);
		if (!empty($keyword)) {
			$condition .= ' and ( dm.realname like :keyword or dm.nickname like :keyword or dm.mobile like :keyword)';
			$params[':keyword'] = '%' . $keyword . '%';
		}

		$sql = 'select dm.*,dm.nickname,dm.avatar,p.nickname as parentname,p.avatar as parentavatar,f.follow as followed, f.unfollowtime from ' . tablename('ewei_shop_member') . ' dm ' . ' left join ' . tablename('ewei_shop_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('mc_mapping_fans') . 'f on f.openid=dm.openid and f.uniacid=' . $_W['uniacid'] . ' where dm.uniacid = ' . $_W['uniacid'] . ' and dm.repertory>0  ' . $condition . ' ORDER BY dm.agenttime desc';

		if (empty($_GPC['export'])) {
			$sql .= ' limit ' . (($pindex - 1) * $psize) . ',' . $psize;
		}

		$list = pdo_fetchall($sql, $params);
		$total = pdo_fetchcolumn('select count(dm.id) from' . tablename('ewei_shop_member') . ' dm  ' . ' left join ' . tablename('ewei_shop_member') . ' p on p.id = dm.agentid ' . ' left join ' . tablename('mc_mapping_fans') . 'f on f.openid=dm.openid' . ' where dm.uniacid =' . $_W['uniacid'] . ' and dm.isagent =1 ' . $condition, $params);

		unset($row);

		$pager = pagination2($total, $pindex, $psize);
		load()->func('tpl');
		include $this->template();
	}

	public function delete()
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		if (empty($id)) {
			$id = (is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0);
		}

		$members = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_member') . ' WHERE id in( ' . $id . ' ) AND uniacid=' . $_W['uniacid']);

		foreach ($members as $member) {
			pdo_update('ewei_shop_member', array('isagent' => 0, 'status' => 0), array('id' => $member['id']));
			plog('repertory.agent.delete', '清除会员存酒数据 <br/>分销商信息:  ID: ' . $member['id'] . ' /  ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
		}

		show_json(1, array('url' => referer()));
	}
}

?>
