<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

return array(
	'version' => '1.0',
	'id'      => 'repertory',
	'name'    => '存酒',
	'v3'      => true,
	'menu'    => array(
		'plugincom' => 1,
		'icon'      => 'page',
		'items'     => array(
			array('title' => '存酒管理', 'route' => 'agent'),
			array('title' => '存酒订单', 'route' => 'order'),
			array(
				'title' => '设置',
				'items' => array(
					array('title' => '通知设置', 'route' => 'notice'),
					array('title' => '入口设置', 'route' => 'cover'),
					array('title' => '基础设置', 'route' => 'set')
					)
				)
			)
		)
	);

?>
