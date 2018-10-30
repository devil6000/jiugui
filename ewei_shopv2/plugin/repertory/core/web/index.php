<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends PluginWebPage
{
	public function main()
	{
		global $_W;

		if (cv('repertory.agent')) {
			header('location: ' . webUrl('repertory/agent'));
			exit();
		}
		else if (cv('repertory.notice')) {
			header('location: ' . webUrl('repertory/notice'));
			exit();
		}
		else if (cv('repertory.cover')) {
			header('location: ' . webUrl('repertory/cover'));
			exit();
		}
		else {
			if (cv('repertory.set')) {
				header('location: ' . webUrl('repertory/set'));
				exit();
			}
		}
	}

	public function notice()
	{
		global $_W;
		global $_GPC;

		if ($_W['ispost']) {
			$data = (is_array($_GPC['data']) ? $_GPC['data'] : array());
			m('common')->updatePluginset(array(
	'repertory' => array('tm' => $data)
	));
			plog('repertory.notice.edit', '修改通知设置');
			show_json(1);
		}

		$data = m('common')->getPluginset('repertory');
		$template_list = pdo_fetchall('SELECT id,title FROM ' . tablename('ewei_shop_member_message_template') . ' WHERE uniacid=:uniacid and typecode=:typecode ', array(':uniacid' => $_W['uniacid'], ':typecode' => 'repertory'));
		include $this->template();
	}

	public function set()
	{
		global $_W;
		global $_GPC;

		if ($_W['ispost']) {
			$data = (is_array($_GPC['data']) ? $_GPC['data'] : array());
			$data['repertory'] = intval($data['repertory']);
			$data['repertory_credit'] = intval($data['repertory_credit']);

			m('common')->updatePluginset(array('repertory' => $data));
			m('cache')->set('template_' . $this->pluginname, $data['style']);

			//plog('repertory.set.edit', '修改基本设置<br>' . '每存 -- ' . $selfbuy . '<br>成为下线条件 -- ' . $become_child . '<br>成为分销商条件 -- ' . $become);
			show_json(1, array('url' => webUrl('repertory/set', array('tab' => str_replace('#tab_', '', $_GPC['tab'])))));
		}

		$styles = array();
		$dir = IA_ROOT . '/addons/ewei_shopv2/plugin/' . $this->pluginname . '/template/mobile/';

		if ($handle = opendir($dir)) {
			while (($file = readdir($handle)) !== false) {
				if (($file != '..') && ($file != '.')) {
					if (is_dir($dir . '/' . $file)) {
						$styles[] = $file;
					}
				}
			}

			closedir($handle);
		}

		$data = m('common')->getPluginset('repertory');

		include $this->template();
	}
}

?>
