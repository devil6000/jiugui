<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/4
 * Time: 9:42
 */
defined('IN_IA') or exit('Access Denied');

class Restaurant_EweiShopV2Page extends WebPage{

    public function main($status){
        global $_W;
        global $_GPC;

        $pIndex = max(1,intval($_GET['page']));
        $pSize = 20;

        if(empty($status)){
            $status = 0;
        }

        $set = 'status' . $status;

        $conditions = "r.uniacid=:uniacid and r.status=:status";
        $params[':uniacid'] = $_W['uniacid'];
        $params[':status'] = $status;

        $starttime = empty($_GPC['time']['start']) ? time() : strtotime($_GPC['time']['start']);
        $endtime = empty($_GPC['time']['end']) ? time() : strtotime($_GPC['time']['end']);

        if(!empty($_GPC['searchtime'])){
            if($_GPC['searchtime'] == 'create'){
                $conditions .= " and r.create_time>=:starttime and r.create_time<=:endtime";
                $params[':starttime'] = $starttime;
                $params[':endtime'] = $endtime;
            }elseif($_GPC['searchtime'] == 'apply'){
                $conditions .= " and r.apply_time>=:starttime and r.apply_time<=:endtime";
                $params[':starttime'] = $starttime;
                $params[':endtime'] = $endtime;
            }
        }

        if(!empty($_GPC['keyword'])){
            $conditions .= " and (r.store_name like '%" . $_GPC['keyword'] . "%' or r.contacts like '%" . $_GPC['keyword'] . "%'";
        }

        $count = pdo_fetchcolumn("select count(r.id) from " . tablename('ewei_shop_restaurant_apply') . " r lef tjoin" . tablename('ewei_shop_member') . " m on r.openid=m.openid where " . $conditions, $params);
        $pager = pagination2($count, $pIndex, $pSize);

        $limit = ($pIndex - 1) * $pSize . "," . $pSize;
        $list = pdo_fetchall("select r.*,m.avatar,m.nickname,m.realname from " . tablename('ewei_shop_restaurant_apply') . " r left join " . tablename('ewei_shop_member') . " m on r.openid=m.openid where " . $conditions . " order by r.id desc limit " . $limit, $params);

        include $this->template('member/restaurant');
    }

    public function status0(){
        $this->main(0);
    }

    public function status1(){
        $this->main(1);
    }

    public function status2(){
        $this->main(2);
    }

    public function apply(){
        global $_W;
        global $_GPC;

        $id = intval($_GPC['id']);
        $status = intval($_GPC['types']);
        $remark = $_GPC['remark'];

        $restaurant = pdo_get('ewei_shop_restaurant_apply', array('id' => $id, 'uniacid'=> $_W['uniacid']));
        if($restaurant['status'] != 0){
            $this->message('申请已审核，不能重复审核', webUrl('member/restaurant/status0'),'error');
            exit;
        }

        $update = array(
            'status' => $status,
            'remark' => $remark,
            'apply_time' => time()
        );

        $i = pdo_update('ewei_shop_restaurant_apply', $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
        if($i){
            if($status == 1){
                pdo_update('ewei_shop_member', array('level' => 5), array('openid' => $restaurant['openid'], 'uniacid' => $_W['uniacid']));
            }
        }

        $msg = ($status == 1) ? '审核通过' : '审核不通过';

        $this->message($msg, webUrl('member/restaurant/status0'), 'success');
    }

}