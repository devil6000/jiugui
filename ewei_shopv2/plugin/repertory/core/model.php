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

        }

	}
}


?>