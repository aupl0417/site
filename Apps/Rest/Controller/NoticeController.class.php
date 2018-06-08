<?php
namespace Rest\Controller;
use Common\Controller\OrdersController;
use Common\Builder\Activity;
/**
 * 异步通知地址
 */

class NoticeController extends OtherController {
    protected $_fun = ['pay', 'accept'];  //订单支付，订单取消，确认收货    可执行方法
    protected $_post= [];   //post过来的数据
    protected $_res = [];   //订单信息
    protected $sw   = [];   //事物
    protected $_sno;        //订单号
    protected $_orders;     //订单对象
    protected $_uid;
    
    protected function _initialize() {
        G('begin');
        $this->_post = I('post.');
        $this->_sno  = $this->_post['s_no'];
    }
    
    /**
     * 程序执行入口
     */
    public function run() {
        //如果为可执行方法
        if(in_array($this->_post['type'], $this->_fun)) {
            //call_user_method_array($this->_post['type'], $this, []);
            if ($this->signData() != $this->_post['sign']) {
                $this->apiReturn(['code' => 1002, 'msg' => '签名错误']);
            }
            $this->_uid = M('orders_shop')->where(['s_no' => $this->_sno])->getField('uid');
            if (!$this->_uid) {
                $this->apiReturn(['code' => 1001, 'msg' => '订单不存在', 'data' => ['uid' => $this->_uid]]);
            }
            $this->_orders  = new OrdersController(array('s_no'=>$this->_sno, 'uid' => $this->_uid));
            $this->_res     = $this->_orders->check_s_orders(2);
            if ($this->_res['code'] != 1) {
                $this->apiReturn(['code' => 1002, 'msg' => '订单不存在', 'data' => $this->_res]);
            }
            $this->{$this->_post['type']}();
        }
    }
    
    //订单支付
    protected function pay() {
        //判断是否已支付,如果已经支付，则直接返回
        if ($this->_res['data']['status'] != 1) {
            $this->apiReturn(['code' => 1001, 'msg' => '订单已支付', 'data' => $this->_res]);
        }
        $check  = $this->_orders->check_goods_attr();
        //商品属性
        if($check['code']!=1) {
            $this->apiReturn($check);
        }
        
        $pay_price    =   $this->_res['data']['pay_price'];
        $score        =   $this->_res['data']['score'];
        $activity     =   [];
        $paytype      =   $this->_post['payType'];
        $other_paytype=   null;
        if ($paytype == 2) {
            $activity = Activity::tangPaysActivity($this->_res['data']);
            if ($activity == false) {
                $activity =   Activity::getActivityByShopOrders($this->_res['data'], 4);
            }
            if ($activity) {
                $pay_price=   $activity['pay_price'];
                $score    =   $activity['score'];
            }
        }
         
         
        $do=M();
        $do->startTrans();
        //如果有唐宝支付折扣则更新所有商品价格
        if (!empty($activity) && $activity['full_value'] > 0 && $paytype == 2) {
            $sql    =   'update ' . C('DB_PREFIX') . 'orders_goods SET total_price_edit = total_price_edit * '
                . ($activity['full_value']) . ',score = score_ratio * score * '. ($activity['full_value']) .' WHERE s_id = ' . $this->_res['data']['id'] . ' AND s_no = '.$this->_res['data']['s_no'];
            if ($do->execute($sql) == false) {
                $msg =   '订单商品更新失败';
                goto error;
            }
            //修改商家订单
            if(M('orders_shop')->where(['id' => $this->_res['data']['id']])->save(['pay_price' => $pay_price, 'goods_price_edit' => $activity['goods_price_edit'], 'score' => $score, 'pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => ($other_paytype?$other_paytype:$paytype), 'money' => $pay_price, 'notify_type' => 2, 'is_pay' => 1]) == false) {
                $msg =   '订单更新失败';
                goto error;
            }
        } else {
            //更新订单状态
            if(!$this->sw[]=M('orders_shop')->where(['s_no' => $this->_sno])->save(['pay_time' => date('Y-m-d H:i:s'),'status'=>2,'pay_type' => ($other_paytype?$other_paytype:$paytype), 'money' => $pay_price, 'notify_type' => 2, 'is_pay' => 1])) goto error;
        }
         
        //写入日志
        $logs_data=array(
            'o_id'		=>$this->_res['data']['o_id'],
            'o_no'		=>$this->_res['data']['o_no'],
            's_id'		=>$this->_res['data']['id'],
            's_no'		=>$this->_res['data']['s_no'],
            'status'	=>2,
            'remark'	=>'买家已付款'
        );
         
        if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }
        if(!$this->sw[]=D('Common/OrdersLogs')->add()) {
            $msg = '添加订单日志失败';
            goto error;
        }
        
         
         
        //更新库存，有部分库存属可能已列新，所以不列入事务是否一定要执行成功
        $num = 0;
        foreach($check['data'] as $i => $val){
            $goods_id[] = $val['goods_id'];
            $num 	+=	$val['num'];
            $do->execute('update '.C('DB_PREFIX').'goods_attr_list set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['attr_list_id']);
            //更新销量
            $do->execute('update '.C('DB_PREFIX').'goods set num=num-'.$val['num'].',sale_num=sale_num+'.$val['num'].' where id='.$val['goods_id']);
        }
        $goods_id = array_unique($goods_id);
         
        //更新店铺销量
        if(!$this->sw[]=M('shop')->where(['id' => $this->_res['data']['shop_id']])->setInc('sale_num',$num)) {
            $msg = '更新店铺销量失败';
            goto error;
        }
        
        //付款加1
        if (Activity::activityInc($this->_sno, 'payment_num') == false) {
            //goto error;
        }
        //设置参与者状态
        if (Activity::setStatus($this->_sno, $this->uid, 1, (!empty($activity) ? null : 4)) === false) {
            //goto error;
        }
        
        $do->commit();
         
        //发短信通知
        $sms_data['mobile']=M('shop')->where(['id' => $this->_res['data']['shop_id']])->getField('mobile');
        if(!empty($sms_data['mobile'])){
            $sms_data['content']=$this->sms_tpl(14,
                ['{nick}','{orderno}','{money}','{goods_num}'],
                [$this->user['nick'],$this->_res['data']['s_no'],$this->_res['data']['pay_price'],$this->_res['data']['goods_num']]
            );
             
            if(!strstr($sms_data['content'],'test') && !strstr($sms_data['content'],'测试')) sms_send($sms_data);
        }
         
        success:
        shop_pr($this->_res['data']['shop_id']);	//更新店铺PR
        goods_pr($goods_id);				//更新宝贝PR
        
        $this->apiReturn(['code' => 1001, 'msg' => '支付成功', 'data' => $this->_res]);
        
        error:
        $do->rollback();
        $this->apiReturn(['code' => 1002, 'msg' => $msg, 'data' => $this->_res]);
    }
    
    
    
    //订单确认收货
    protected function accept() {
		$rs=$this->_res['data'];
		if ($rs['status'] != 3) $this->apiReturn(['code' => 1001, 'msg' => '当前订单不能收货', 'data' => $this->_res]);
		//如果已经收货，则返回1001
		if ($rs['status'] > 3) $this->apiReturn(['code' => 1001, '当前订单已收货', 'data' => $this->_res]);

        //获取是否有参与累积升级
        $activity = [];
        if ($rs['pay_type'] != 2) {    //不为唐宝支付时才去查看是否有参与满消费升级
            $activity = M('activity_participate')->where(['s_no' => $this->_sno, 'type_id' => 7, 'uid' => $rs['uid'], 'status' => 0])->field('id,status')->find();
        }
        
        $refund=M('refund')->where(['s_id' => $rs['id'],'status' => ['not in','20,100']])->field()->select();
        
        $do=M();
        $do->startTrans();
        if ($activity) { //如果状态为未支付，则改为已支付
            M('activity_participate')->where(['id' => $activity['id']])->save(['status' => 1]);    //修改状态
        }
        	
        $luckdraw = getSiteConfig('luckdraw');   //抽奖
        $luckdrawFlag = false;
        if (round($rs['goods_price_edit'] - $rs['refund_price'], 2) >= $luckdraw['luckdraw_orders_money']) {
            $luckdrawMap = [
                'id' => $rs['shop_id'],
                'type_id' => ['in', $luckdraw['luckdraw_shop_type']],
            ];
            if (M('shop')->where($luckdrawMap)->getField('id')) {
                $luckdrawDo = M('luckdraw_chance');
                $luckdrawId = $luckdrawDo->where(['uid' => $rs['uid']])->getField('id');
                if ($luckdrawId > 0) {
                    if(!$this->sw[]=$luckdrawDo->where(['id' => $luckdrawId])->setInc('free_chance', $luckdraw['luckdraw_orders_num'])) {
                        $msg = '免费抽奖机会加' . $luckdraw['luckdraw_orders_num'] . '失败';
                        goto error;
                    }
                } else {
                    if(!$this->sw[]=$luckdrawDo->add(['free_chance' => $luckdraw['luckdraw_orders_num'], 'uid' => $rs['uid']])) {
                        $msg = '添加免费抽奖机会加' . $luckdraw['luckdraw_orders_num'] . '失败';
                        goto error;
                    }
                }
                 
                //记录免费抽奖机会
                $freeData = [
                    'no'    =>  $this->create_orderno('LA',$this->uid),    //单号
                    'uid'   =>  $this->uid,                 //用户ID
                    'status'=>  1,                          //状态
                    'type'  =>  2,                          //类型
                ];
                if (false == M('luckdraw_chance_free')->add($freeData)) {
                    $msg = '记录免费抽奖机会加' . $luckdraw['luckdraw_orders_num'] . '失败';
                    goto error;
                }
                 
                $luckdrawFlag = true;
            }
        }
        
        //如果存在着退款，即将退款取消
        if($refund){
            if(!$this->sw[]=M('refund')->where(['s_id' => $rs['id'],'status' => ['not in','20,100']])->save(['status' => 20,'cancel_time' => date('Y-m-d H:i:s')])) goto error;
            //日志
            foreach($refund as $val){
                //日志数据
                $logs=[
                    'r_id'          =>$val['id'],
                    'r_no'          =>$val['r_no'],
                    'uid'           =>$rs['uid'],
                    'status'        =>20,
                    'type'          =>$val['type'],
                    'remark'        =>C('error_code.1006'), //买家取消退款！
                ];
        
                if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
                    $msg=D('Common/RefundLogs')->getError();
                    goto error;
                }
        
                if(!$this->sw[]=D('Common/RefundLogs')->add()) {
                    $msg = '取消退款时添加日志失败';
                    goto error;
                }
            }
        }
        
        
        //更新订单
        if(!$this->sw[]=M('orders_shop')->where(array('id'=>$rs['id']))->save(array('status'=>4,'receipt_time'=>date('Y-m-d H:i:s')))){
            //更新订单状态失败！
            $msg = '更新订单状态失败';
            goto error;
        }
        
        //订单日志
        $logs_data=array(
            'o_id'		=>$rs['o_id'],
            'o_no'		=>$rs['o_no'],
            's_id'		=>$rs['id'],
            's_no'		=>$rs['s_no'],
            'status'	=>4,
            'remark'	=>'买家确认收货',
            'is_sys'	=>1
        );
        	
        if(!$this->sw[]=D('Common/OrdersLogs')->create($logs_data)){
            $msg=D('Common/OrdersLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/OrdersLogs')->add()){
            $msg = '添加订单日志失败';
            goto error;
        }
        
        //付款成功，事务提交
        $do->commit();
        $returnData['s_no'] = $rs['s_no'];
        $returnData['luckdraw'] = $luckdrawFlag == true ? 1 : 0;
        $this->apiReturn(['code' => 1001, 'msg' => '操作成功', 'data' => $this->_res]);
        error:
        $do->rollback();
        $this->apiReturn(['code' => 1001, 'msg' => $msg, 'data' => $this->_res]);
    }
}