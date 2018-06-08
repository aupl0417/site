<?php
/**
* 买家退款- 已发货未收货
*/
namespace Common\Controller;
use Common\Builder\Activity;
class Refund2Controller extends OrdersController {
    private $action_logs      =array('add','edit','express_add','express_edit','cancel','send_express');   //须要记录日志的方法
    /**
    * 列可退款商品
    * @param int    $param['imgsize']   图片尺寸
    * @param int    $param['orders_goods_id']   订单中的商品ID    
    */
    public function goods($param=null){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已发货且未确认收货的订单才可执行此操作！
        if($res['data']['status']!=3) return $this->apiReturn(1010);

        if($param['orders_goods_id']!='')   $map['id']  =$param['orders_goods_id'];
        $countExpressMoney  =   M('refund')->where(['s_no' => $res['data']['s_no'], 'status' => ['notin', '20']])->field('SUM(refund_express) as countexpressmoney')->find();
        $map['s_no']        =$this->s_no;
        if (($res['data']['express_price_edit'] - $countExpressMoney['countexpressmoney']) == 0) {
            $map['_string']     ='refund_num < num and refund_price<total_price_edit';  
        }
        

        $list['goods']=M('orders_goods')->where($map)->field('id,s_id,s_no,goods_id,attr_list_id,attr_name,price,num,weight,total_price,total_price_edit,goods_name,images,refund_price,refund_num,(num-refund_num) as can_num,(total_price_edit-refund_price) as can_price,concat("/Goods/view/id/",attr_list_id,".html") as detail_url')->select();
        //已申请退款中的商品
        $list2=[];
        foreach($list['goods'] as $i => $val){
            $total=M('refund')
                    ->where([
                        'orders_goods_id'   => $val['id'],
                        'orders_status'     => $res['data']['status'],
                        'status'            => ['not in','100,20']
                        ])
                    ->field('sum(num) as num,sum(money) as money')
                    ->find();
            $list['goods'][$i]['can_num']    -= $total['num'];
            $list['goods'][$i]['can_price']  -= $total['money'];
            //是否可以退运费
            if($res['data']['express_price_edit']>0 && $res['data']['express_price_edit']>$res['data']['refund_express']) {
                if ($res['data']['express_price_edit'] > $countExpressMoney['countexpressmoney']) {
                    $list['goods'][$i]['is_refund_express']  = 1;
                    $list['goods'][$i]['express_money']      = ($res['data']['express_price_edit'] - $countExpressMoney['countexpressmoney']);
                }
            }
            //dump($list['goods'][$i]);
            if($list['goods'][$i]['can_num']>0 || $list['goods'][$i]['can_price']>0 || $list['goods'][$i]['is_refund_express']  == 1) $list2[]=$list['goods'][$i];
        }

        $list['goods']=$list2;

        //是否可以退运费
        /*if($res['data']['express_price_edit']>0 && $res['data']['express_price_edit']>$res['data']['refund_express']){
            //是否申请过运费退款
            $express_count=M('refund')
                    ->where([
                        's_no'              => $this->s_no,
                        'orders_status'     => $res['data']['status'],
                        'type'              => 3,
						'status'            => ['not in','100']
                        ])
                    ->count();



            if($express_count>0) $list['express']['money']=0;
            else {
                $list['express']=[
                    's_no'      =>$this->s_no,
                    'money'     =>$res['data']['express_price_edit']-$res['data']['refund_express']
                ];
            }
        }*/
        /*if (!empty($param['orders_goods_id'])) {//找出参订单所参与的活动
            $activity = Activity::refundActivity($list['goods'][0]['s_no'], $list['goods'][0]['total_price']);
            if ($activity) {
                $list['goods'][0]['activity']   =   $activity;
                //$list['goods'][0]['can_price'] -=   $activity['lessMoney'];
                //$list['goods'][0]['can_price']  =   number_format($list['goods'][0]['can_price'], 2);
            }
        }*/
        if($list['goods'] || $list['express']['money']){
            if($list['goods'] && $param['imgsize']) $list['goods']=imgsize_list($list['goods'],'images',$param['imgsize']);
            
            if($param['orders_goods_id']!=''){
                if($list['goods']) return $this->apiReturn(1,['data' => $list['goods'][0]]);
                else return $this->apiReturn(3);
            }else return $this->apiReturn(1,['data' => $list]);
        }else{
            return $this->apiReturn(3);
        }
    }


    /**
    * 提交退款申请
    * @param int    $param['orders_goods_id']   订单中商品ID
    * @param float  $param['price']             退款金额
    * @param int    $param['num']               退掉商品数量
    * @param int    $param['type']              1退货退款，2只退款
    * @param string $param['reason']            退款原因
    * @param string $param['images']            证据图片，多张用逗号隔开
    */
    public function add($param){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;
        
        //已发货且未确认收货的订单才可执行此操作！
        if($res['data']['status']!=3) return $this->apiReturn(1010);
        
        $orderShop  =   M('orders_shop')->where(['s_no' => $param['s_no']])->field('express_price_edit,refund_express,money')->find();
        $refundExpress  =   M('refund')->where(['s_no' => $param['s_no'], 'status' => ['notin', '20']])->field('SUM(refund_express) as refund_express')->find();
        if (!empty($param['refund_express']) && $param['refund_express'] > 0) {//如果退运费不为空且大于0
            if ($orderShop['express_price_edit'] < round($param['refund_express'] + $refundExpress['refund_express'], 2)) {
                $msg    =   '运费不能大于' . (round($orderShop['express_price_edit'] - $refundExpress['refund_express'], 2)) . '元';
                return $this->apiReturn(4,'', $msg);
            }
        } 
        
        $goods=M('orders_goods')
                ->where(['id' => $param['orders_goods_id']])
                ->field('id,score_ratio,refund_action_num,refund_num,(num-refund_num) as can_num,(total_price_edit-refund_price) as can_price,is_can_refund')
                ->find();
        //退款操作不能超过2次（包含取消退款操作）
        //if($goods['refund_action_num']>1) return $this->apiReturn(1012);
		if($goods['is_can_refund'] == 0 && $param['price'] != 0) return $this->apiReturn(0, '', '此商品使用乐兑官方优惠券，只支持运费退款！');

        //申请退款中的商品
        $total=M('refund')
                ->where([
                    'orders_goods_id'   => $param['orders_goods_id'],
                    'orders_status'     => $res['data']['status'],
                    'status'            => ['not in','100,20']
                    ])
                ->field('sum(num) as num,sum(money) as money')
                ->find();
        $goods['can_num']      -=$total['num'];    //可退数量
        $goods['can_price']    -=($total['money']);  //可退金额
        if (($param['num']==0||!isset($param['num'])) && ($param['price']==0 || !isset($param['price'])) && (!isset($param['refund_express']) || $param['refund_express'] == 0)) {
            return $this->apiReturn(0, '', '退款金额、退货数量、运费必填一项!');
        }
        
        //可退数量为0，不可申请退款
        //if($goods['can_num']==0)   return $this->apiReturn(1002);

        //可退金额为0，不可申请退款
        //if($goods['can_price']==0)   return $this->apiReturn(1003);

        //超过可退数量
        if($param['num'] > $goods['can_num'])  return $this->apiReturn(4,'',str_replace('{n}', $goods['can_num'], C('error_code.1001')));

        //超过可退金额    //加一个number_format函数 mercury
        if($param['price'] > round($goods['can_price'], 2))  return $this->apiReturn(4,'',str_replace('{price}', $goods['can_price'], C('error_code.1004')));
        
        $score  =   $goods['score_ratio'] * $param['price'] * 100;


        $reason = $param['reason'] ? $param['reason'] : ($param['type'] == 1 ? '买家申请退款/退货' : '买家申请退款');

        $data = [
            'r_no'              => $this->create_orderno('TK',$res['data']['uid']),
            'uid'               => $res['data']['uid'],
            'seller_id'         => $res['data']['seller_id'],
            'shop_id'           => $res['data']['shop_id'],
            's_id'              => $res['data']['id'],
            's_no'              => $res['data']['s_no'],
            'orders_status'     => $res['data']['status'],
            'status'            => 1,
            'orders_goods_id'   => $param['orders_goods_id'],
            'num'               => $param['num'],
            'money'             => $param['price'],
            'score'             => $score,
            'type'              => $param['type'],
            'reason'            => $reason,
            'images'            => $param['images'],
			'dotime'			=> date('Y-m-d H:i:s'),
            'refund_express'    => $param['refund_express'] > 0 ? $param['refund_express'] : 0,
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
        ];

        /*
        $param['reason']    =   '<p class="strong text_red">买家申请退款</p>' . $param['reason'];
        $param['reason']   .=   '<br />申请退款金额为<strong class="text_red">￥ ' . $param['price'] . ' </strong>元退货数量为<strong class="text_red"> ' . ($param['num'] ? $param['num'] : 0) . ' </strong>';
        if ($param['refund_express'] > 0) {  //如果有退运费则将运费写入
            $data['refund_express'] =   $param['refund_express'];
            $param['reason']       .=   '并申请了<strong class="text_red">￥ ' . $param['refund_express'] . ' </strong>元邮费退款'; 
        }
        */

        //日志数据
        $logs=[
            'r_no'          => $data['r_no'],
            'uid'           => $this->uid,
            'status'        => 1,
            'type'          => $param['type'],
            'remark'        => $reason,
            'images'        => $param['images'] ? $param['images'] : '',
            'num'           => $data['num'],
            'money'         => $data['money'],
            'refund_express'=> $data['refund_express'],
            'score'         => $data['score']
        ];
        $do=M();
        $do->startTrans();
        //创建退款订单
        if(!$this->sw[]=D('Common/Refund')->create($data)){
            $msg=D('Common/Refund')->getError();
            goto error;
        }
        if(!$this->sw[]=D('Common/Refund')->add()) goto error;

        //创建退款日志
        $logs['r_id']   =D('Common/Refund')->getLastInsID();
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;            
        }
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;

        if(!$this->sw[]=M('orders_goods')->where(['id' => $param['orders_goods_id']])->setInc('refund_action_num')) goto error;

        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'refund_apply','s_no'=>$res['data']['s_no'],'r_no'=>$data['r_no']];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $data['r_no']]]);

        error:
            $do->rollback();
            return $this->apiReturn(0,'',C('error_code.0').$msg);          
    }

    /**
    * 修改退款申请
    * @param int    $param['r_no']              退款单号   
    * @param float  $param['price']             退款金额
    * @param int    $param['num']               退掉商品数量
    * @param int    $param['type']              1退货退款，2只退款
    * @param string $param['reason']            退款原因
    * @param string $param['images']            证据图片，多张用逗号隔开
    */
    public function edit($param){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已发货且未确认收货的订单才可执行此操作！
        if($res['data']['status']!=3) return $this->apiReturn(1010);

        $rs=M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->uid])->field()->find();
        if(!$rs) return $this->apiReturn(3);
        
        $orderShop  =   M('orders_shop')->where(['s_no' => $param['s_no']])->field('express_price_edit,refund_express,money')->find();
        $refundExpress  =   M('refund')->where(['s_no' => $param['s_no'], 'status' => ['notin', '20']])->field('SUM(refund_express) as refund_express')->find();
        if (!empty($param['refund_express']) && $param['refund_express'] > 0) {//如果退运费不为空且大于0
            if ($orderShop['express_price_edit'] < round(($param['refund_express'] + $refundExpress['refund_express']) - $rs['refund_express'], 2)) {
                $msg    =   '运费不能大于' . (round($orderShop['express_price_edit'] - $refundExpress['refund_express'], 2)) . '元';
                return $this->apiReturn(4,'', $msg);
            }
        }
        //被拒绝后的退款申请才可以编辑！
        if($rs['status']!=2) return $this->apiReturn(1015);

        $goods=M('orders_goods')
                ->where(['id' => $rs['orders_goods_id']])
                ->field('id,score_ratio,refund_action_num,refund_num,(num-refund_num) as can_num,(total_price_edit-refund_price) as can_price,is_can_refund')
                ->find();
		if($goods['is_can_refund'] == 0 && $param['price'] != 0) return $this->apiReturn(0, '', '此商品使用乐兑官方优惠券，只支持运费退款！');

        //申请退款中的商品
        $total=M('refund')
                ->where([
                    'orders_goods_id'   => $param['orders_goods_id'],
                    'orders_status'     => $res['data']['status'],
                    'status'            => ['not in','100'],
                    'id'                => ['neq',$rs['id']]
                    ])
                ->field('sum(num) as num,sum(money) as money')
                ->find();

        $goods['can_num']      -=$total['num'];    //可退数量
        $goods['can_price']    -=($total['money']);  //可退金额

        //可退数量为0，不可申请退款
        # if($goods['can_num']==0)   return $this->apiReturn(1002);

        //可退金额为0，不可申请退款,且不是退运费的情况
        if($goods['can_price']==0 && !(!empty($param['refund_express']) && $param['refund_express'] > 0))   return $this->apiReturn(1003);

        //超过可退数量
        if($param['num'] > $goods['can_num'])  return $this->apiReturn(4,'',str_replace('{n}', $goods['can_num'], C('error_code.1001')));

        //超过可退金额
        if($param['price'] > $goods['can_price'])  return $this->apiReturn(4,'',str_replace('{price}', $goods['can_price'], C('error_code.1004')));

        
        //$param['reason'] = '<p class="strong text_red">买家修改退款</p>' . $param['reason'];
        $reason = $param['reason'] ? $param['reason'] : '买家修改退款';
        $data = [
            'status'            => 3,
            'num'               => $param['num'],
            'money'             => $param['price'],
            'score'             => $goods['score_ratio'] * $param['price'] * 100,
            'type'              => $param['type'],
            'reason'            => $reason,
            //'images'            => $param['images'],
			'dotime'			=> date('Y-m-d H:i:s'),
            'refund_express'    => $param['refund_express'] > 0 ? $param['refund_express'] : 0,
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
            'is_problem'        => 0,
        ];

        /*
        $param['reason']       .=   '</br />申请退款金额为<strong class="text_red">￥ ' . $param['price'] . ' </strong>元退货数量为<strong class="text_red"> ' . ($param['num'] ? $param['num'] : 0) . ' </strong>';
        if ($param['refund_express'] > 0) {  //如果有退运费则将运费写入
            $data['refund_express'] =   $param['refund_express'];
            $param['reason']       .=   '并申请了<strong class="text_red">￥ ' . $param['refund_express'] . ' </strong>元邮费退款'; 
        } else {
            $data['refund_express'] = 0;    //如果未填写运费，则把运费改为0
        }
        */
        
        //日志数据
        $logs=[
            'r_id'          => $rs['id'],
            'r_no'          => $rs['r_no'],
            'uid'           => $this->uid,
            'status'        => 3,
            'type'          => $param['type'],
            'remark'        => $param['reason'],
            'images'        => $param['images']?$param['images']:'',
            'num'           => $data['num'],
            'money'         => $data['money'],
            'refund_express'=> $data['refund_express'],
            'score'         => $data['score']
        ];



        $do=M();
        $do->startTrans();

        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save($data)) goto error;

        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;            
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;

        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'refund_edit','r_no'=>$rs['r_no']];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $rs['r_no']]]);

        error:
            $do->rollback();
            return $this->apiReturn(0,'',C('error_code.0').$msg);          
    }

    /**
    * 退运费
    * @param float  $param['price']             退款金额
    * @param string $param['reason']            退款原因    
    */
    public function express_add($param){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已付款未发货订单才可执行此操作！
        if($res['data']['status']!=3) return $this->apiReturn(1010);

        //是否申请过运费退款
        $express_count=M('refund')
                    ->where([
                        's_no'              => $this->s_no,
                        'orders_status'     => $res['data']['status'],
                        'type'              => 3
                        ])
                    ->count();

        //退运费不能多次申请
        if($express_count>0) return $this->apiReturn(1011);

        $can_price  = $res['data']['express_price']-$res['data']['refund_express'];

        //超过可退金额
        if($param['price'] > $can_price) return $this->apiReturn(4,'',str_replace('{price}', $can_price, C('error_code.1004')));

        $reason = $param['reason'] ? $param['reason'] : '买家申请退运费';

        $data = [
            'r_no'              => $this->create_orderno('TK',$res['data']['uid']),
            'uid'               => $res['data']['uid'],
            'seller_id'         => $res['data']['seller_id'],
            'shop_id'           => $res['data']['shop_id'],
            's_id'              => $res['data']['id'],
            's_no'              => $res['data']['s_no'],
            'orders_status'     => $res['data']['status'],
            'status'            => 1,
            'money'             => $param['price'],
            'score'             => 0,   //运费没有积分
            'type'              => 3,
            'reason'            => $reason,
			'dotime'			=> date('Y-m-d H:i:s'),
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
            'is_problem'        => 0,
        ];

        //日志数据
        $logs=[
            'r_no'          =>$data['r_no'],
            'uid'           =>$this->uid,
            'status'        =>1,
            'type'          =>3,
            'remark'        =>$reason
        ];

        $do=M();
        $do->startTrans();
        //创建退款订单
        if(!$this->sw[]=D('Common/RefundExpress')->create($data)){
            $msg=D('Common/RefundExpress')->getError();
            goto error;
        }
        if(!$this->sw[]=D('Common/RefundExpress')->add()) goto error;

        //创建退款日志
        $logs['r_id']   =D('Common/RefundExpress')->getLastInsID();
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;            
        }

        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;

        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'refund_apply','s_no'=>$res['data']['s_no'],'r_no'=>$data['r_no']];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $data['r_no']]]);

        error:
            $do->rollback();
            return $this->apiReturn(0,'',C('error_code.0').$msg);             
    }

    /**
    * 修改退运费
    * @param float  $param['price']             退款金额
    * @param string $param['reason']            退款原因    
    */
    public function express_edit($param){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已付款未发货订单才可执行此操作！
        if($res['data']['status']!=3) return $this->apiReturn(1010);

        $rs=M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->uid])->field()->find();
        if(!$rs) return $this->apiReturn(3);

        //被拒绝后的退款申请才可以编辑！
        if($rs['status']!=2) return $this->apiReturn(1015);

        $can_price  = $res['data']['express_price']-$res['data']['refund_express'];

        //超过可退金额
        if($param['price'] > $can_price) return $this->apiReturn(4,'',str_replace('{price}', $can_price, C('error_code.1004')));

        $data = [
            'status'            => 3,
            'money'             => $param['price'],
            'reason'            => $param['reason'],
			'dotime'			=> date('Y-m-d H:i:s'),
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
            'is_problem'        => 0,
        ];

        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$this->uid,
            'status'        =>1,
            'type'          =>3,
            'remark'        =>$param['reason'],
            'imaegs'        =>$param['images']
        ];

        $do=M();
        $do->startTrans();

        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save($data)) goto error;

        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;            
        }

        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;

        $do->commit();
        return $this->apiReturn(1, ['data' => ['r_no' => $rs['r_no']]]);

        error:
            $do->rollback();
            return $this->apiReturn(0,'',C('error_code.0').$msg);             
    }

    /**
    * 取消退款
    * @param string     $r_no   退款单号
    * @param int    $is_sys 系统操作
    * @param int    $check_type 订单检验类型，1为卖家，2为买家，0不检验     
    */
    public function cancel($r_no,$check_type=2,$is_sys=0){
        //检查订单状态
        $res=$this->check_s_orders($check_type);
        if($res['code']!=1) return $res;

        
        
        $rs=M('refund')
            ->where([
                'r_no'          => $r_no,
                's_id'          => $res['data']['id'],
                'orders_status' => $res['data']['status']
                ])
            ->field('id,status,type')->find();

        if(!$rs) return $this->apiReturn(3);    //找不到记录

        //退款订单已失效！
        if(in_array($rs['status'],[20,100]))    return $this->apiReturn(1005);
		//已付款未发货订单才可执行此操作！
        if($res['data']['status']!=3) return $this->apiReturn(1000); 

        $do=M();
        $do->startTrans();

        if(!$this->sw[]=M('refund')->where(['r_no' => $r_no])->save(['status' => 20,'dotime'=> date('Y-m-d H:i:s'),'cancel_time' => date('Y-m-d H:i:s')])) goto error;

        //$reason = '<p class="strong text_red">买家取消退款</p>';
        //$reason.= C('error_code.1006');

        $reason = $param['reason'] ? $param['reason'] : '买家取消退款';
        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$r_no,
            'uid'           =>$this->uid,
            'status'        =>20,
            'type'          =>$rs['type'],
            'remark'        =>$reason, //买家取消退款！
            'is_sys'        =>$is_sys
        ];

        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;            
        }        

        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;

        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'refund_cancel','r_no'=>$r_no];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $r_no]]);

        error:
            $do->rollback();
            return $this->apiReturn(0,'',C('error_code.0').$msg);
    }

    /*
    * 寄回商品
    * @param string $param['r_no']      退款单号 
    * @param float  $param['express_company_id']    快递公司ID
    * @param string $param['express_code']          快递单号
    * @param string $param['reason']                备注
    */
    public function send_express($param){      
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已发货且未确认收货的订单才可执行此操作！
        if($res['data']['status']!=3) return $this->apiReturn(1010);

        $rs=M('refund')->where(['r_no' => $param['r_no'],'uid' => $this->uid])->field()->find();
        if(!$rs) return $this->apiReturn(3);

        //退款订单已失效！
        if($rs['status']!=4) return $this->apiReturn(1005);

        $ers=M('express_company')->where(['id' => $param['express_company_id']])->field('sub_name')->find();
        $str=$ers['sub_name'].'：'.$param['express_code'];

        $do=M();
        $do->startTrans();

        if(!$this->sw[]=M('refund')->where(['r_no' => $rs['r_no']])->save(['status' => 5,'dotime' => date('Y-m-d H:i:s'),'express_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['confirm_orders']),'is_problem' => 0])) goto error;

        //$reason = '<p class="strong text_red">买家寄出商品</p>' . $str.$param['reason'];
        $reason = $param['reason'] ? $param['reason'] : '买家已寄回商品';

        //日志数据
        $logs=[
            'r_id'                  =>$rs['id'],
            'r_no'                  =>$rs['r_no'],
            'uid'                   =>$this->uid,
            'status'                =>5,
            'type'                  =>$rs['type'],
            'express_company_id'    =>$param['express_company_id'],
            'express_code'          =>$param['express_code'],
            'remark'                =>$reason, //买家取消退款！
        ];

        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;            
        }        

        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;

        $do->commit();

        //发短信通知
        //{nick}已寄回商品，{express_company}：{express_code}【乐兑】
        /*
        $sms_data['mobile']=M('shop')->where(['id' => $res['data']['shop_id']])->getField('mobile');
        if(!empty($sms_data['mobile'])){
            $sms_data['content']=$this->sms_tpl(15,
                    ['{nick}','{express_company}','{express_code}'],
                    [$this->user['nick'],$ers['sub_name'],$param['express_code']]
                );

            sms_send($sms_data);
        }
        */

        return $this->apiReturn(1, ['data' => ['r_no' => $rs['r_no']]]);

        error:
            $do->rollback();
            return $this->apiReturn(0,'',C('error_code.0').$msg);  
    }
}