<?php

define('IN_SYS', true);
require '../framework/bootstrap.inc.php';
load() -> web('common');
load() -> web('template');
load() -> func('file');
require IA_ROOT . '/web/common/bootstrap.sys.inc.php';

require_once IA_ROOT . '/addons/ewei_shopv2/version.php';
require_once IA_ROOT . '/addons/ewei_shopv2/defines.php';
require_once EWEI_SHOPV2_INC . 'functions.php';
global $_W;

$uniacid = intval($_GPC['i']) ? intval($_GPC['i']) : 1;
$_W['uniacid'] = $uniacid;
$cookie = $_GPC['__uniacid'];

if (empty($uniacid) && empty($cookie)) {
	exit('Access Denied.');
}

session_start();
if (!empty($uniacid)) {
	$_SESSION['__merch_uniacid'] = $uniacid;
	isetcookie('__uniacid', $uniacid, 7 * 86400);
}

defined('IN_IA') or exit('Access Denied');
define('IN_GW', true);
if (checksubmit() || $_W['isajax']) {
	if(empty($_GPC['__session'])){
		_login($_GPC['referer']);
	}
}
$setting = $_W['setting'];
//template('user/login');

function logout() {
	isetcookie('__session', '', -10000);
	isetcookie('__switch', '', -10000);
	$forward = $_GPC['forward'];
	if (empty($forward)) {
		$forward = './?refersh';
	}
	return true;
}

if ($_GPC['do'] == 'logout' && empty($_GPC['op'])) {
	logout();
	header('Location:' . $_W['siteurl'] . '&op=1');
	die ;
}
if (!empty($_GPC['do'])) {
	$init = IA_ROOT . "/web/source/{$controller}/__init.php";
	if (is_file($init)) {
		require $init;
	}

	$actions = array();
	$actions_path = file_tree(IA_ROOT . '/web/source/' . $controller);
	foreach ($actions_path as $action_path) {
		$action_name = str_replace('.ctrl.php', '', basename($action_path));

		$section = basename(dirname($action_path));
		if ($section !== $controller) {
			$action_name = $section . '-' . $action_name;
		}
		$actions[] = $action_name;
	}

	if (empty($actions)) {
		header('location: ?refresh');
	}

	if (!in_array($action, $actions)) {
		$action = $action . '-' . $action;
	}
	if (!in_array($action, $actions)) {
		$action = $acl[$controller]['default'] ? $acl[$controller]['default'] : $actions[0];
	}

	if (is_array($acl[$controller]['direct']) && in_array($action, $acl[$controller]['direct'])) {
		require  _forward($controller, $action);
		exit();
	}
	checklogin();
	if ($_W['role'] != ACCOUNT_MANAGE_NAME_FOUNDER && version_compare($_W['setting']['site']['version'], '1.5.5', '>=')) {
		if (empty($_W['uniacid'])) {
			if (defined('FRAME') && FRAME == 'account') {
				itoast('', url('account/display'), 'info');
			}
			if (defined('FRAME') && FRAME == 'wxapp') {
				itoast('', url('wxapp/display'), 'info');
			}
		}
		if (function_exists('permission_build')) {
			$acl = permission_build();
		}
		if (empty($acl[$controller][$_W['role']]) || (!in_array($controller . '*', $acl[$controller][$_W['role']]) && !in_array($action, $acl[$controller][$_W['role']]))) {
			message('不能访问, 需要相应的权限才能访问！');
		}
	}

	require  _forward($controller, $action);

	define('ENDTIME', microtime());
	if (empty($_W['config']['setting']['maxtimeurl'])) {
		$_W['config']['setting']['maxtimeurl'] = 10;
	}
	if ((ENDTIME - STARTTIME) > $_W['config']['setting']['maxtimeurl']) {
		$data = array('type' => '1', 'runtime' => ENDTIME - STARTTIME, 'runurl' => $_W['sitescheme'] . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'createtime' => TIMESTAMP);
		pdo_insert('core_performance', $data);
	}

	//	header('Location:' . wurl('order'));
	die ;
}

function _forward($c, $a) {
	$file = IA_ROOT . '/web/source/' . $c . '/' . $a . '.ctrl.php';
	if (!file_exists($file)) {
		list($section, $a) = explode('-', $a);
		$file = IA_ROOT . '/web/source/' . $c . '/' . $section . '/' . $a . '.ctrl.php';
	}
	return $file;
}

if (!empty($_W['user']['uid'])) {//如果已登陆状态就跳转到后台
	header('Location:' . WebUrl('order'));
	die ;
}
$data = m('common') -> getSysset('shop');
print_r("

<!DOCTYPE html>
<html lang=\"zh-cn\">
<head>
	<meta charset=\"utf-8\">
	<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
	<title>" . $data['name'] . "工厂管理</title>
	<link href=\"../web/resource/css/bootstrap.min.css?v=20170915\" rel=\"stylesheet\">
	<link href=\"../web/resource/css/common.css?v=20170915\" rel=\"stylesheet\">
	<script type=\"text/javascript\">
	if(navigator.appName == 'Microsoft Internet Explorer'){
		if(navigator.userAgent.indexOf(\"MSIE 5.0\")>0 || navigator.userAgent.indexOf(\"MSIE 6.0\")>0 || navigator.userAgent.indexOf(\"MSIE 7.0\")>0) {
			alert('您使用的 IE 浏览器版本过低, 推荐使用 Chrome 浏览器或 IE8 及以上版本浏览器.');
		}
	}
	
	</script>
	<script>var require = { urlArgs: 'v=20170915' };</script>
	<script type=\"text/javascript\" src=\"../web/resource/js/lib/jquery-1.11.1.min.js\"></script>
	<script type=\"text/javascript\" src=\"../web/resource/js/lib/bootstrap.min.js\"></script>
	<script type=\"text/javascript\" src=\"../web/resource/js/app/util.js?v=20170915\"></script>
	<script type=\"text/javascript\" src=\"../web/resource/js/app/common.min.js?v=20170915\"></script>
	<script type=\"text/javascript\" src=\"../web/resource/js/require.js?v=20170915\"></script>
</head>
<body>
	<div class=\"loader\" style=\"display:none\">
		<div class=\"la-ball-clip-rotate\">
			<div></div>
		</div>
	</div>
<div class=\"system-login\"  >

	<div class=\"head\">
		<a href=\"/\" class=\"logo-version\">
			<img src=" . $_W['attachurl'] . $data['logo'] . " class=\"logo\">
			<span class=\"version hidden\">" . IMS_VERSION . "</span>
		</a>
	</div>
	<div class=\"login-panel\">
		<div class=\"title\">账号密码登录</div>
		<form action=\"\" method=\"post\" role=\"form\" id=\"form1\" onsubmit=\"return formcheck();\" class=\"we7-form\">
			<div class=\"input-group-vertical\">
				<input name=\"username\" type=\"text\" class=\"form-control \" placeholder=\"请输入用户名登录\">
				<input name=\"password\" type=\"password\" class=\"form-control password\" placeholder=\"请输入登录密码\">
			</div>
			<div class=\"checkbox\">
				<input type=\"checkbox\" value=\"true\" id=\"rember\" name=\"rember\">
				<label for=\"rember\">记住用户名</label>
			</div>
			<div class=\"login-submit text-center\">
				<input type=\"submit\" id=\"submit\" name=\"submit\" value=\"登录\" class=\"btn btn-primary btn-block\" />
				<div class=\"text-right\">
				</div>
				<input name=\"token\" value=\"" . $_W['token'] . "\" type=\"hidden\" />
			</div>
		</form>
	</div>
</div>


<script>
function formcheck() {
	if($('#remember:checked').length == 1) {
		cookie.set('remember-username', $(':text[name=\"username\"]').val());
	} else {
		cookie.del('remember-username');
	}
	return true;
}
var h = document.documentElement.clientHeight;
$(\".login\").css('min-height',h);
$('#toggle').click(function() {
	$('#imgverify').prop('src', '{php echo url('utility/code')}r='+Math.round(new Date().getTime()));
	return false;
});
	$('#form1').submit(function() {
		var verify = $(':text[name=\"verify\"]').val();
		if (verify == '') {
			alert('请填写验证码');
			return false;
		}
	});
</script>
</body>
</html>");

function _login($forward = '') {
	global $_GPC, $_W;
	load() -> model('user');
	$member = array();
	$username = trim($_GPC['username']);
	pdo_query('DELETE FROM' . tablename('users_failed_login') . ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP - 300));
	$failed = pdo_get('users_failed_login', array('username' => $username, 'ip' => CLIENT_IP));
	if ($failed['count'] >= 5) {
		show_itoast('输入密码错误次数超过5次，请在5分钟后再登录', referer(), 'info');
	}
	if (!empty($_W['setting']['copyright']['verifycode'])) {
		$verify = trim($_GPC['verify']);
		if (empty($verify)) {
			show_itoast('请输入验证码', '', '');
		}
		$result = checkcaptcha($verify);
		if (empty($result)) {
			show_itoast('输入验证码错误', '', '');
		}
	}
	if (empty($username)) {
		show_itoast('请输入要登录的用户名', '', '');
	}
	$member['username'] = $username;
	$member['password'] = $_GPC['password'];
	if (empty($member['password'])) {
		show_itoast('请输入密码', '', '');
	}

	$record = user_single($member);
	if (!empty($record)) {
		if ($record['status'] == USER_STATUS_CHECK || $record['status'] == USER_STATUS_BAN) {
			show_itoast('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！', '', '');
		}
		$_W['uid'] = $record['uid'];
		$_W['isfounder'] = user_is_founder($record['uid']);
		$_W['user'] = $record;

		if (empty($_W['isfounder']) || user_is_vice_founder()) {
			if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
				show_itoast('您的账号有效期限已过，请联系网站管理员解决！', '', '');
			}
		}
		if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
			show_itoast('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason'], '', '');
		}
		$cookie = array();

		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = md5($record['password'] . $record['salt']);
		$session = authcode(json_encode($cookie), 'encode');
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
		$status = array();
		$status['uid'] = $record['uid'];
		$status['lastvisit'] = TIMESTAMP;
		$status['lastip'] = CLIENT_IP;
		user_update($status);

		//		if (empty($forward)) {
		//			$forward = user_login_forward($_GPC['forward']);
		//		}

		if ($record['uid'] != $_GPC['__uid']) {
			isetcookie('__uniacid', '', -7 * 86400);
			isetcookie('__uid', '', -7 * 86400);
		}
		pdo_delete('users_failed_login', array('id' => $failed['id']));

		$modulename = $_GPC['m'] ? $_GPC['m'] : 'ewei_shopv2';
		if (!empty($modulename)) {
			$_W['current_module'] = module_fetch($modulename);
		}

		$site = WeUtility::createModule($modulename);
		if (!is_error($site)) {
			$method = 'welcomeDisplay';
			if (method_exists($site, $method)) {
				define('FRAME', 'module_welcome');
				$entries = module_entries($modulename, array('menu', 'home', 'profile', 'shortcut', 'cover', 'mine'));
				$site -> $method($entries);
				exit ;
			}
		}
		define('FRAME', 'account');
		define('IN_MODULE', $modulename);
		$frames = buildframes('account');
		foreach ($frames['section'] as $secion) {
			foreach ($secion['menu'] as $menu) {
				if (!empty($menu['url'])) {
					header('Location: ' . $_W['siteroot'] . 'web/' . $menu['url']);
					exit ;
				}
			}
		}

		//		show_itoast("欢迎回来，{$record['username']}。", WebUrl('order'), 'success');
		die ;
	} else {
		if (empty($failed)) {
			pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
		} else {
			pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
		}
		show_itoast('登录失败，请检查您输入的用户名和密码！', '', '');
	}
}

function show_itoast($msg, $reuri, $type) {
	global $_W;
	if (!empty($_W['show_itoast'])) {
		return false;
		die ;
	}
	$reuri = $reuri == referer() || empty($reuri) ? $_W['siteurl'] : $reuri;
	$type = $type ? $type : 'info';
	$_W['show_itoast'] = '1';
	print_r("
		<style>
			.modal-content{
			    position: fixed;
			    width: 60%;
			    margin-left: 20%;
			    z-index: 1111111;
			    top: 35%;
		    }
		</style>
		<div class=\"modal-content\"><div class=\"modal-header\">	<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\" onclick='javascript:location.href=\"" . $reuri . "\"'>×</button>	<h3>系统提示</h3></div><div class=\"modal-body\">			<div class=\"text-center\">					<i class=\"text-" . $type . " wi wi-" . $type . "-sign\"></i>" . $msg . "<div></div>			</div>			<div class=\"clearfix\"></div></div><div class=\"modal-footer\">		<a href=\"" . $reuri . "\" class=\"btn btn-primary\">确认</a></div>	</div>
	");
}

//$site = WeUtility::createModuleSite('ewei_shopv2');
//if (!is_error($site)) {
//	$method = 'doWeblogin';
//	$site->uniacid = $uniacid;
//	$site->inMobile = false;
//	if (method_exists($site, $method)) {
//		$site->$method();
//		exit();
//	}
//}
?>