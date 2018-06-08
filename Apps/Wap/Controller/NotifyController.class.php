<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/1/19
 * Time: 13:54
 */

namespace Wap\Controller;


use Think\Controller;
use Common\Builder\Activity;
class NotifyController extends Controller
{
    /**
     * 单订单同步返回
     */
    public function return_single(){
        $id     = I('get.id');
        $token  = I('get._s');
        if ($this->check_return($id, $token) === true) {
            $res = $this->_single_orders_check($id);
            if($res['code'] == 1) redirect(DM('wap', '/index/index') . '?url=/cart/dt_pay_success');
        }
        redirect(DM('wap', '/index/index') . '?url=/cart/dt_pay_error');
    }

    /**
     * 合并订单同步返回
     */
    public function return_multi(){
        $id     = I('get.id');
        $token  = I('get._s');
        if ($this->check_return($id, $token) === true) {
            $res = $this->_multi_orders_check($id);
            if($res['code'] == 1) redirect(DM('wap', '/index/index') . '?url=/cart/dt_pay_success');
        }
        redirect(DM('wap', '/index/index') . '?url=/cart/dt_pay_error');
    }

    /**
     * 同步认证
     *
     * @param $string
     * @param $token
     * @return bool
     */
    public function check_return($string, $token)
    {
        return md5( $string . '0.13820200 1481726001' ) === $token;
    }

    /**
     * 当异步返回不及时时同步对订单进行修复
     * @param string $s_no 订单号
     */
    public function fix_orders($s_no,$uid,$status){
        //$buyer = M('user')->cache(true)->where(['erp_uid' => $post['payerID']])->field('id,nick')->find();
        $orders=new \Common\Controller\OrdersController(array('s_no'=>$s_no,'uid'=>$uid));
        $ret=$orders->check_s_orders(2);
        if($ret['code']!=1) {
            log_add('erp_pays_notify_error',['atime' => date('Y-m-d H:i:s'),'s_no' => $s_no,'msg' => '订单不存在！']);
            return false;
        }
        if($ret['data']['status']!=1) {
            log_add('erp_pays_notify_error',['atime' => date('Y-m-d H:i:s'),'s_no' => $s_no,'msg' => '订单状态错误！[status='.$ret['data']['status'].']']);
            return false;
        } //未支付状态下方可更改状态

        //dump($ret);

        //检查是否有唐宝支付打折活动
        $seller=M('user')->where(['id' => $ret['data']['seller_id']])->field('nick,erp_uid')->find();
        $pay_price    =   $ret['data']['pay_price'];
        $score        =   $ret['data']['score'];
        $activity     =   [];
        if ($ret['data']['pay_type'] == 2) {
            $activity = Activity::tangPaysActivity($ret['data']);
            if ($activity == false) {
                $activity =   Activity::getActivityByShopOrders($ret['data'], 4);
            }
            if ($activity) {
                $pay_price    =   $activity['pay_price'];
                $score        =   $activity['score'];
            }
        }

        //exit();

        $do=M();
        $do->startTrans();
        //如果有唐宝支付折扣则更新所有商品价格
        if (!empty($activity) && $activity['full_value'] > 0 && $ret['data']['pay_type'] == 2) {
            $sql    =   'update ' . C('DB_PREFIX') . 'orders_goods SET total_price_edit = total_price_edit * '
                . ($activity['full_value']) . ',score = score_ratio * score * '. ($activity['full_value']) .' WHERE s_id = ' . $ret['data']['id'] . ' AND s_no = '.$ret['data']['s_no'];
            if ($do->execute($sql) == false) {
                $msg =   '订单商品更新失败';
                goto error;
            }
            //修改商家订单
            if(M('orders_shop')->where(['id' => $ret['data']['id']])->save(['pay_price' => $pay_price, 'goods_price_edit' => $activity['goods_price_edit'], 'score' => $score, 'pay_time' => date('Y-m-d H:i:s'),'dtpay_no' => $status['o_id'],'status'=>2,'money' => $pay_price]) == false) {
                $msg =   '订单更新失败';
                goto error;
            }
        } else {
            //更新订单状态
            if(!$this->sw[]=M('orders_shop')->where(['id' => $ret['data']['id']])->save(['pay_time' => date('Y-m-d H:i:s'),'dtpay_no' => $status['o_id'],'status'=>2,'money' => $pay_price])) goto error;
        }

        //写入日志
        $logs_data=array(
            'o_id'		=>$ret['data']['o_id'],
            'o_no'		=>$ret['data']['o_no'],
            's_id'		=>$ret['data']['id'],
            's_no'		=>$ret['data']['s_no'],
            'status'	=>2,
            'remark'	=>'买家已付款'
        );

        if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }
        if(!$this->sw[]=D('Common/OrdersLogs')->add()) goto error;


        //更新库存，有部分库存属可能已列新，所以不列入事务是否一定要执行成功
        $goods = M('orders_goods')->where(['s_id' => $ret['data']['id']])->field('id,goods_id,num,attr_list_id')->select();
        $num = 0;
        $goods_id = array();
        foreach($goods as $i => $val){
            $goods_id[] = $val['goods_id'];
            $num 	+=	$val['num'];
            $do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
            //更新销量
            $do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
        }
        $goods_id = array_unique($goods_id);

        //更新店铺销量
        if(!$this->sw[]=M('shop')->where(['id' => $ret['data']['shop_id']])->setInc('sale_num',$num)) {
            $msg =  '更新店铺销量失败！';
            goto error;
        }

        //付款加1
        if (Activity::activityInc($ret['data']['s_no'], 'payment_num') == false) {
            //goto error;
        }
        //设置参与者状态
        if (Activity::setStatus($ret['data']['s_no'], $buyer['id'], 1, (!empty($activity) ? null : 4)) === false) {
            //goto error;
        }

        success:
        $do->commit();

        //发短信通知
        $sms_data['mobile']=M('shop')->where(['id' => $ret['data']['shop_id']])->getField('mobile');
        if(!empty($sms_data['mobile'])){
            $sms_data['content']=$this->sms_tpl(14,
                ['{nick}','{orderno}','{money}','{goods_num}'],
                [$buyer['nick'],$ret['data']['s_no'],$pay_price,$ret['data']['goods_num']]
            );

            if(!strstr($sms_data['content'],'test') && !strstr($sms_data['content'],'测试')) sms_send($sms_data);
        }

        shop_pr($ret['data']['shop_id']);	//更新店铺PR
        goods_pr($goods_id);				//更新宝贝PR

        log_add('erp_pays_notify_success',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'group_no' => $ret['data']['o_no'],'s_no' => $s_no,'msg' => '订单更新成功！' ,'sw' => @implode(',',$this->sw)]);

        return true;

        error:
        $do->rollback();
        log_add('erp_pays_notify_error',['atime' => date('Y-m-d H:i:s'),'is_fix' => 1,'group_no' => $ret['data']['o_no'],'s_no' => $s_no,'msg' => $msg ,'sw' => @implode(',',$this->sw)]);
        return false;
    }

    /**
     * 合并付款订单是否支付成功
     */
    public function multi_orders_check(){
        if(empty($_SESSION['user'])) $this->ajaxReturn(['code' => 0,'msg' => '请先登录！']);
        $res = $this->_multi_orders_check(I('post.o_no'));
        $this->ajaxReturn($res);
    }

    public function _multi_orders_check($o_no){
        $list   = M('orders_shop')->where(['o_no' => $o_no])->field('id,s_no,status,uid')->select();
        $result = ['code' => 0,'msg' => '支付失败！','count' => count($list),'o_no' => $o_no,'success' => 0,'error' => 0];

        foreach($list as $val){
            //在此次可考虑再次与ERP中的订单较对，防止异步出问题是可以修正
            //do samething……

            if($val['status'] ==2 ){
                $result['success']++;
            }elseif($val['status'] == 1){
                $res = $this->doApi('/Erp/check_orders_status',['s_no' => $val['s_no']]);
                if($res->data->o_orderState == 1){
                    $tmp = $this->fix_orders($val['s_no'],$val['uid'],objectToArray($res->data));
                    if($tmp == true) $result['success']++;
                }
            }else{
                $result['error']++;
            }
        }

        if($result['success'] == count($list)){
            $result['code'] = 1;
            $result['msg']  = '支付成功！';
        }

        return $result;

    }

    /**
     * 单个订单付款，检查是否支付成功
     */
    public function single_orders_check(){
        if(empty($_SESSION['user'])) $this->ajaxReturn(['code' => 0,'msg' => '请先登录！']);
        $res = $this->_single_orders_check(I('post.s_no'));
        $this->ajaxReturn($res);
    }

    public function _single_orders_check($s_no){
        $result = ['code' => 0,'msg' => '支付失败！','s_no' => $s_no];
        $rs = M('orders_shop')->where(['s_no' => $s_no])->field('id,status,s_no,uid')->find();
        if($rs['status'] == 2){
            $result['code'] = 1;
            $result['msg']  = '支付成功！';
        }elseif($rs['status'] == 1){
            //在此次可考虑再次与ERP中的订单较对，防止异步出问题是可以修正
            //do samething……

            $res = $this->doApi('/Erp/check_orders_status',['s_no' => $s_no]);
            if($res->data->o_orderState == 1){
                $tmp = $this->fix_orders($s_no,$rs['uid'],objectToArray($res->data));
                if($tmp == true){
                    $result = ['code' => 1,'msg' => '支付成功！','s_no' => $s_no];
                }
            }

        }

        return $result;
    }
}