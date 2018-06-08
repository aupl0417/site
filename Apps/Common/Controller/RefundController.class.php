<?php
/**
* 买家退款-已付款未发货
*/
namespace Common\Controller;
use Common\Builder\Activity;
class RefundController extends OrdersController {
    private $action_logs      =array('add','express_add','cancel');   //须要记录日志的方法
    /**
    * 列可退款商品
    * @param int    $param['imgsize']   图片尺寸
    * @param int    $param['orders_goods_id']   订单中的商品ID
    */
    public function goods($param=null){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已付款未发货订单才可执行此操作！
        if($res['data']['status']!=2) return $this->apiReturn(1000);

        
        if($param['orders_goods_id']!='')   $map['id']  =$param['orders_goods_id'];
        $countExpressMoney  =   M('refund')->where(['s_no' => $res['data']['s_no'], 'status' => ['notin', '20']])->field('SUM(refund_express) as countexpressmoney')->find();
        $map['s_no']        =$this->s_no;
        if (($res['data']['express_price_edit'] - $countExpressMoney['countexpressmoney']) == 0) {
            $map['_string']     ='refund_num < num or refund_price<total_price_edit';
        }
        $list['goods']=M('orders_goods')->where($map)->field('id,s_id,s_no,goods_id,attr_list_id,attr_name,price,num,weight,total_price,total_price_edit,goods_name,images,refund_price,refund_num,(num-refund_num) as can_num,(total_price_edit-refund_price) as can_price,concat("/Goods/view/id/",attr_list_id,".html") as detail_url')->select();
        //已申请退款中的商品
        $list2=[];
        foreach($list['goods'] as $i => $val){
            $total=M('refund')
                    ->where([
                        'orders_goods_id'   => $val['id'],
                        'orders_status'     => $res['data']['status'],
                        'status'            => ['notin','100,20']
                        ])
                    ->field('sum(num) as num,sum(money) as money')
                    ->find();
            //dump($total);
            $list['goods'][$i]['can_num']    -= $total['num'];
            $list['goods'][$i]['can_price']   = round($val['can_price'] - ($total['money']),2);
            //dump($list['goods'][$i]); 
            //是否可以退运费
            if($res['data']['express_price_edit']>0 && $res['data']['express_price_edit']>$res['data']['refund_express']) {
                if ($res['data']['express_price_edit'] > $countExpressMoney['countexpressmoney']) {
                    $list['goods'][$i]['is_refund_express']  = 1;
                    $list['goods'][$i]['express_money']      = ($res['data']['express_price_edit'] - $countExpressMoney['countexpressmoney']);
                }
            }
            if($list['goods'][$i]['can_num']>0 || $list['goods'][$i]['can_price']>0 || $list['goods'][$i]['is_refund_express'] == 1) $list2[]=$list['goods'][$i];
        }
        $list['goods']=$list2;
        //是否可以退运费
        /*if($res['data']['express_price_edit']>0 && $res['data']['express_price_edit']>$res['data']['refund_express']){
            //是否申请过运费退款
            $express_price=M('refund')
                    ->where([
                        's_no'              => $this->s_no,
                        'orders_status'     => $res['data']['status'],
                        'type'              => 3,
                        'status'            => ['not in','100']
                        ])
                    ->sum('money');
            $list['express']=[
                's_no'      =>$this->s_no,
                'money'     =>$res['data']['express_price_edit']-$res['data']['refund_express']-$express_price,
            ];
        }*/
        /*if (!empty($param['orders_goods_id'])) {//找出参订单所参与的活动
            $activity = Activity::refundActivity($list['goods'][0]['s_no'], $list['goods'][0]['total_price']);
            if ($activity) {
                $list['goods'][0]['activity']   =   $activity;
                //exit;
                //$list['goods'][0]['can_price'] -=   $activity['lessMoney'];
                //$list['goods'][0]['can_price']  =   number_format($list['goods'][0]['can_price'], 2);
            }
        }*/
        if($list['goods'] || $list['express']['money']>0){
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
    * @param string $param['reason']            退款原因
    */
    public function add($param){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已付款未发货订单才可执行此操作！
        if($res['data']['status']!=2) return $this->apiReturn(1000);
        $orderShop  =   M('orders_shop')->where(['s_no' => $param['s_no']])->field('express_price_edit,refund_express,money')->find();
        
        $refundExpress  =   M('refund')->where(['s_no' => $param['s_no'], 'status' => ['notin', '20']])->field('SUM(refund_express) as refund_express')->find();
        if (!empty($param['refund_express']) && $param['refund_express'] > 0) {//如果退运费不为空且大于0
            if ($orderShop['express_price_edit'] < round($param['refund_express'] + $refundExpress['refund_express'], 2)) {
                $msg    =   '运费不能大于' . (round($orderShop['express_price_edit'] - $refundExpress['refund_express'], 2)) . '元';
                return $this->apiReturn(4,'', $msg);
            }
        } 
//         if (round($param['price'] + ($orderShop['express_price_edit'] - $refundExpress['refund_express']), 2) - $orderShop['money'] == 0) {
//             if (empty($param['refund_express']) || round($param['price'] + ($orderShop['refund_express'] + $param['refund_express']), 2) < $orderShop['money']) {
//                 $msg    =   '当前为最后一次退款，邮费必须退'.($orderShop['express_price_edit'] - $orderShop['refund_express']).'元';
//                 return $this->apiReturn(0, '', $msg);
//             }
//         }
//         goto error;
        $goods=M('orders_goods')
                ->where(['id' => $param['orders_goods_id']])
                ->field('id,score_ratio,refund_num,(num-refund_num) as can_num,(total_price_edit-refund_price) as can_price,is_can_refund')
                ->find();
		//if($goods['is_can_refund'] == 0) return $this->apiReturn(0, '', '此商品不允许退款！');
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
        if (($param['num']==0 || !isset($param['num'])) && ($param['price']==0 || !isset($param['price'])) && ($param['refund_express'] == 0 || !isset($param['refund_express']))) {
            return $this->apiReturn(0, '', '退款金额、退货数量、运费必填一项!');
        } 
        
        //可退数量为0，不可申请退款
        //if($goods['can_num']==0)   return $this->apiReturn(1002);

        //可退金额为0，不可申请退款
        //if($goods['can_price']==0)   return $this->apiReturn(1003);

        //超过可退数量
        if($param['num'] > $goods['can_num'])  return $this->apiReturn(4,'',str_replace('{n}', $goods['can_num'], C('error_code.1001')));

        //超过可退金额
        if($param['price'] > round($goods['can_price'], 2))  return $this->apiReturn(4,'',str_replace('{price}', $goods['can_price'], C('error_code.1004')));
        
        
        $score  =   $goods['score_ratio'] * $param['price'] * 100;
        
        

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
            'type'              => 2,
            'reason'            => $param['reason'],
			'dotime'			=> date('Y-m-d H:i:s'),
            'refund_express'    => $param['refund_express'] > 0 ? $param['refund_express'] : 0,
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
        ];

        /*
        $param['reason']        =   $param['reason'].'<p class="strong text_red">买家申请退款</p>申请退款金额为<strong class="text_red">￥ ' . $param['price'] . ' </strong>元退货数量为<strong class="text_red"> ' . ($param['num'] ? $param['num'] : 0) . ' </strong>';
        if ($param['refund_express'] > 0) {  //如果有退运费则将运费写入
            $data['refund_express']  =   $param['refund_express'];
            $param['reason']        .=   '并申请了<strong class="text_red">￥ ' . $param['refund_express'] . ' </strong>元邮费退款'; 
        }
        */

        $reason = $param['reason'] ? $param['reason'] : '买家申请退款';
        
        //日志数据
        $logs=[
            'r_no'          =>$data['r_no'],
            'uid'           =>$this->uid,
            'status'        =>1,
            'type'          =>2,
            'remark'        => $reason,
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
        if($res['data']['status']!=2) return $this->apiReturn(1000);
    
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
        ->field('id,score_ratio,refund_action_num,refund_num,(num-refund_num) as can_num,(total_price_edit-refund_price) as can_price')
        ->find();
    
    
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
            'type'              => 2,
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
        } else {    //如果未填写 则把运费改为0
            $data['refund_express'] = 0;
        }
        */
    
        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$this->uid,
            'status'        =>3,
            'type'          =>2,
            'remark'        =>$reason,
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
        if($res['data']['status']!=2) return $this->apiReturn(1000);

        //是否申请过运费退款
        $express_price=M('refund')
                    ->where([
                        's_no'              => $this->s_no,
                        'orders_status'     => $res['data']['status'],
                        'type'              => 3,
                        'status'            => ['not in','100,20']
                        ])
                    ->sum('money');
        $can_price  = $res['data']['express_price_edit']-$res['data']['refund_express']-$express_price;

        //超过可退金额
        if($param['price'] > $can_price) return $this->apiReturn(4,'',str_replace('{price}', $can_price, C('error_code.1004')));

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
            'reason'            => $param['reason'],
			'dotime'			=> date('Y-m-d H:i:s'),
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['confirm_orders']),
            'is_problem'        => 0,
        ];

        //日志数据
        $logs=[
            'r_no'          =>$data['r_no'],
            'uid'           =>$this->uid,
            'status'        =>1,
            'type'          =>3,
            'remark'        =>$param['reason']
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
        return $this->apiReturn(1, ['data' => ['r_no' => $data['r_no']]]);

        error:
            $do->rollback();
            return $this->apiReturn(0,'',C('error_code.0').$msg);             
    }


    /**
    * 取消退款
    * @param string     $r_no   退款单号
    */
    public function cancel($r_no){
        //检查订单状态
        $res=$this->check_s_orders(2);
        if($res['code']!=1) return $res;

        //已付款未发货订单才可执行此操作！
        if($res['data']['status']!=2) return $this->apiReturn(1000); 
        
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


        $do=M();
        $do->startTrans();

        if(!$this->sw[]=M('refund')->where(['r_no' => $r_no])->save(['status' => 20,'dotime' => date('Y-m-d H:i:s'),'cancel_time' => date('Y-m-d H:i:s')])) goto error;


        //$reason = '<p class="strong text_red">买家取消退款</a>';
        //$reason.= C('error_code.1006');

        $reason = '买家取消退款';

        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$r_no,
            'uid'           =>$this->uid,
            'status'        =>20,
            'type'          =>$rs['type'],
            'remark'        =>$reason, //买家取消退款！
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


}