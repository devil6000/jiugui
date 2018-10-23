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

	}

}


?>