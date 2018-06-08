<?php
namespace Common\Controller;
class Refund3Controller extends OrdersController {
    private $action_logs    =   array('add','cancel','send_express');   //须要记录日志的方法
    private $_param         =   [];
    private $_res           =   [];
    private $_resStatus     =   [4,5];    //可执行的状态
    function __construct($param = null) {
        parent::__construct($param);
        if (!is_null($param)) {
            $this->_param   =   array_merge($param, I('post.'));
        } else {
            $this->_param   =   I('post.');
        }
        $this->_res         =   $this->check_s_orders(2);
        if ($this->_res['code'] != 1) {
            return $this->apiReturn($this->_res);
        }
        if ($this->_res['data']['status'] < 4) return ['code' => 0, 'msg' => '已收货订单才可申请售后！'];
    }
    
    public function goods() {
        $map    =   [
            's_no'      =>  $this->_param['s_no'],
            '_string'   =>  'num > (refund_num+service_num)',
        ];
        if($this->_param['id']!='')   $map['id']  =$this->_param['id'];		
        if($this->_param['orders_goods_id']!='')   $map['id']  =$this->_param['orders_goods_id'];
        $list['goods']   =   M('orders_goods')->where($map)->field('id,goods_service_days,s_id,s_no,goods_id,attr_list_id,attr_name,price,num,weight,total_price,total_price_edit,goods_name,images,refund_price,refund_num,(num-(refund_num+service_num)) as can_num,concat("/Goods/view/id/",attr_list_id,".html") as detail_url')->select();
        
        //查询是否还能够申请！
        foreach ($list['goods'] as $k => &$v) {
            $total=M('refund')
                ->where([
                    'orders_goods_id'   => $v['id'],
                    'orders_status'     => ['in', '4,5'],
                    'status'            => ['not in','100,20'],
                ])
                ->field('sum(num) as num')
                ->find();
                $v['can_num']  -=   $total['num'];
                //售后时间判断
                $receipt_time[$k] = strtotime($this->_res['data']['receipt_time']) + (($v['goods_service_days'] > 0 ? $v['goods_service_days'] : getGoodsServiceDays($v['goods_id'])) * 24 * 60 * 3600);
                if ($v['can_num'] < 1 || $receipt_time[$k] < NOW_TIME) unset($list['goods'][$k]);
        }
        unset($v, $receipt_time);
        if ($list['goods']) {
            if($this->_param['orders_goods_id']!='') {
                return ['code' => 1, 'msg' => '操作成功', 'data' => $list['goods'][0]];
            } else {
                return ['code' => 1, 'msg' => '操作成功', 'data' => $list];
            }
        }
        return ['code' => 0, 'msg' => '暂无记录'];
    }
    
    public function add() {
        $goods=M('orders_goods')
        ->where(['id' => $this->_param['orders_goods_id']])
        ->field('id,score_ratio,goods_id,goods_service_days,refund_action_num,refund_num,(num-(refund_num+service_num)) as can_num')
        ->find();
        
        //售后时间判断
        $receipt_time = strtotime($this->_res['data']['receipt_time']) + (($goods['goods_service_days'] > 0 ? $goods['goods_service_days'] : getGoodsServiceDays($goods['goods_id'])) * 24 * 60 * 3600);
        if ($receipt_time > NOW_TIME) $this->apiReturn(0, '', '已超过售后天数，不可申请售后服务');
        
        //申请售后中的商品
        $total=M('refund')
		->where([
            'orders_goods_id'   => $this->_param['orders_goods_id'],
            'orders_status'     => ['in', '4,5'],
            'status'            => ['not in','100,20']
        ])
        ->field('sum(num) as num')
        ->find();
        
        $goods['can_num']      -=$total['num'];    //可售后数量
        //超出可售后数量
		if($this->_param['num']<=0)return $this->apiReturn(0,"","请填写正确的售后数量");
        if($this->_param['num'] > $goods['can_num']) return $this->apiReturn(4,'',str_replace('{n}', $goods['can_num'], C('error_code.1001')));
        $this->_param['type'] = 1; //只能换货
        $data = [
            'r_no'              => $this->create_orderno('SH',$this->_res['data']['uid']),
            'uid'               => $this->_res['data']['uid'],
            'seller_id'         => $this->_res['data']['seller_id'],
            'shop_id'           => $this->_res['data']['shop_id'],
            's_id'              => $this->_res['data']['id'],
            's_no'              => $this->_res['data']['s_no'],
            'orders_status'     => $this->_res['data']['status'],
            'orders_goods_id'   => $this->_param['orders_goods_id'],
            'num'               => $this->_param['num'],
            'reason'            => $this->_param['reason'],
            'images'            => $this->_param['images'],
            'dotime'			=> date('Y-m-d H:i:s'),
            'money'             => 0,
            'score'             => 0,
            'type'              => $this->_param['type'],//1换货，2维修
            'status'            => 1,
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
        ];
        
        //$this->_param['reason'] =   '<p class="strong text_red">买家申请售后</p>' . $this->_param['reason'];
        //$this->_param['reason'] .=  '<br />售后数量为<strong class="text_red"> ' . ($this->_param['num'] ? $this->_param['num'] : 0) . ' </strong>';
        $logs=[
            'r_no'          =>$data['r_no'],
            'uid'           =>$this->uid,
            'status'        =>1,
            'type'          =>$this->_param['type'],
            'remark'        =>$this->_param['reason'],
            'num'           =>$this->_param['num'],
            'images'        =>$this->_param['images'] ? $this->_param['images'] : '',
        ];
        
        $do=M();
        $do->startTrans();
        //创建售后订单
        if(!$this->sw[]=D('Common/Refund')->create($data)){
            $msg=D('Common/Refund')->getError();
            goto error;
        }
        //添加refund
        if(!$this->sw[]=D('Common/Refund')->add()) goto error;

        //创建售后日志
        $logs['r_id']   =D('Common/Refund')->getLastInsID();
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;            
        }
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
		
		//发送售后消息
		$msg_data = ['tpl_tag'=>'service_apply','s_no'=>$data['s_no'],'r_no'=>$data['r_no']];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $data['r_no']]]);
        
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    public function edit() {
        $goods=M('orders_goods')
        ->where(['id' => $this->_param['orders_goods_id']])
        ->field('id,score_ratio,refund_action_num,refund_num,(num-(refund_num+service_num)) as can_num')
        ->find();
        
        //申请售后中的商品
      /*  $total=M('refund')
        ->where([
            'orders_goods_id'   => $this->_param['orders_goods_id'],
            'id'                => $this->_param['id'],
            'uid'               => $this->uid,
            'orders_status'     => ['in', '4,5'],
            'r_no'              => $this->_param['r_no'],
            's_no'              => $this->_param['s_no'],
            'status'            => ['not in','100,20']
        ])*/
		$total=M('refund')
		->where([
            'orders_goods_id'   => $this->_param['orders_goods_id'],
            'id'                => ['neq',$this->_param['id']],
            'uid'               => $this->uid,
            'orders_status'     => ['in', '4,5'],
            'r_no'              => $this->_param['r_no'],         
			's_no'              => $this->_param['s_no'],
            'status'            => ['not in','100,20']
        ])
        ->field('sum(num) as num')
        ->find();
        $goods['can_num']      -=$total['num'];    //可售后数量
        
        //超出可售后数量
		if($this->_param['num']<=0)return $this->apiReturn(0,"","请填写正确的售后数量");
		if($this->_param['num'] > $goods['can_num']) return $this->apiReturn(4,'',str_replace('{n}', $goods['can_num'], C('error_code.1001')));
        $this->_param['reason'] = '买家修改售后申请；'.$this->_param['reason'];
        $data = [
            'num'               => $this->_param['num'],
            'reason'            => $this->_param['reason'],
            'dotime'			=> date('Y-m-d H:i:s'),
            'type'              => 1,//1换货，2维修
            'status'            => 1,
            'next_time'         => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),
            'is_problem'        => 0,
        ];
        
        //$this->_param['reason'] =   '<p class="strong text_red">买家修改售后</p>' . $this->_param['reason'];
        //$this->_param['reason'] .=  '<br />售后数量为<strong class="text_red"> ' . ($this->_param['num'] ? $this->_param['num'] : 0) . ' </strong>';
        $logs=[
            'r_id'          =>$this->_param['id'],
            'r_no'          =>$this->_param['r_no'],
            'uid'           =>$this->uid,
            'status'        =>1,
            'type'          =>1,
            'num'           =>$this->_param['num'],
            'remark'        =>$this->_param['reason'],
            'images'        =>$this->_param['images'] ? $this->_param['images'] : '',
        ];
		
        $do=M();
        $do->startTrans();
        //添加refund
        if(!$this->sw[]=D('Common/Refund')->where(['r_no' => $this->_param['r_no'], 'id' => $this->_param['id'], 'uid' => $this->uid, 's_no' => $this->_param['s_no']])->save($data)) goto error;
        //创建售后日志
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
        return $this->apiReturn(1, ['data' => ['r_no' => $this->_param['r_no']]]);
        
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    public function cancel() {
        $rs=M('refund')
        ->where([
            'r_no'          => $this->_param['r_no'],
            's_id'          => $this->_res['data']['id'],
            'orders_status' => ['in', '4,5']
            ])
        ->field('id,status,type')->find();
        
        if(!$rs) return $this->apiReturn(3);    //找不到记录
        
        //退款订单已失效！
        if(in_array($rs['status'],[20,100]))    return $this->apiReturn(1005);
        
        $do=M();
        $do->startTrans();
        
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 20,'dotime'=> date('Y-m-d H:i:s'),'cancel_time' => date('Y-m-d H:i:s')])) goto error;
        //$remark = '<p class="strong text_red">买家取消售后</p>售后已取消';
        //if (!empty($this->_param['remark'])) $remark .= ',' . $this->_param['remark'];
        $remark = $this->_param['remark'] ? $this->_param['remark'] : '买家取消售后申请';
        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$this->_param['r_no'],
            'uid'           =>$this->uid,
            'status'        =>20,
            'type'          =>$rs['type'],
            'remark'        =>$remark, //买家取消退款！
            'is_sys'        =>isset($this->_param['is_sys']) ? 1 : 0,
        ];
        
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
		
		//发送售后消息
		$msg_data = ['tpl_tag'=>'service_cancel','r_no'=>$this->_param['r_no']];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $this->_param['r_no']]]);
        
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    /**
     * 买家寄出商品
     * @return Ambigous <integer, multitype:>
     */
    public function send_express() {
        //$addr   =   M('')
        $area   =   $this->cache_table('area');
        $address=   M('shopping_address')->where(['uid' => $this->uid, 'id' => $this->_param['address_id']])->find();
        if (!$address) return $this->apiReturn(0, '', '收货地址错误！');
        $address_str= $address['linkname'].'，'.$address['mobile'].($address['tel']?'，'.$address['tel']:'') .'，' .$area[$address['province']].' '.$area[$address['city']].' '.$area[$address['district']].' '.$area[$address['town']].' '.$address['street'].($address['postcode']?'('.$address['postcode'].')':'');
        $rs     =   M('refund')->where(['r_no' => $this->_param['r_no'],'uid' => $this->uid])->find();
        if(!$rs) return $this->apiReturn(3);
        
        //退款订单已失效！
        if($rs['status']!=3) return $this->apiReturn(1005);
        
        $ers=M('express_company')->where(['id' => $this->_param['express_company_id']])->field('sub_name')->find();
        $str=$ers['sub_name'].'：'.$this->_param['express_code'];
        
        $do=M();
        $do->startTrans();
        
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 4,'dotime' => date('Y-m-d H:i:s'),'express_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['confirm_orders']),'is_problem' => 0])) goto error;
        //$reason = '<p class="strong text_red">买家寄出售后商品</p>';
        //$reason .= $str . '，收货地址：' . $address_str;
        $reason = '买家寄出需要售后的商品给卖家！';
        //日志数据
        $logs=[
            'r_id'                  =>$rs['id'],
            'r_no'                  =>$rs['r_no'],
            'uid'                   =>$this->uid,
            'status'                =>4,
            'type'                  =>$rs['type'],
            'express_company_id'    =>$this->_param['express_company_id'],
            'express_code'          =>$this->_param['express_code'],
            'address'               =>$address_str,
            'remark'                =>$reason, //买家取消退款！
        ];
        
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
        return $this->apiReturn(1, ['data' => ['r_no' => $this->_param['r_no']]]);
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    public function accept() {
        $rs=M('refund')
        ->where([
            'r_no'          => $this->_param['r_no'],
            's_id'          => $this->_res['data']['id'],
            'orders_status' => ['in', '4,5']
        ])
        ->field('id,status,type,orders_goods_id,s_no,num')->find();
        
        if(!$rs) return $this->apiReturn(3);    //找不到记录
        
        //退款订单已失效！
        if(in_array($rs['status'],[20,100]))    return $this->apiReturn(1005);
        
        $do=M();
        $do->startTrans();
        
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 100,'dotime'=> date('Y-m-d H:i:s'),'accept_time' => date('Y-m-d H:i:s')])) goto error;
        //$reason = '<p class="strong text_red">买家确认收到售后商品</p>售后已完成';
        $reason = '买家已收到商品且无异议，售后完成！';
        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$this->_param['r_no'],
            'uid'           =>$this->uid,
            'status'        =>100,
            'type'          =>$rs['type'],
            'remark'        =>$reason, //买家取消退款！
            'is_sys'        =>isset($this->_param['is_sys']) ? 1 : 0,
        ];
        
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        if (!$this->sw[]=M('orders_goods')->where(['id' => $rs['orders_goods_id']])->setInc('service_num', $rs['num'])) {
            goto error;
        }
        if (!$this->sw[]=M('orders_shop')->where(['s_no' => $rs ['s_no']])->setInc('service_num', $rs['num'])) {
            goto error;
        }
        
        $do->commit();
        return $this->apiReturn(1, ['data' => ['r_no' => $this->_param['r_no']]]);
        
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    /**
     * 买家收到商品
     * @param unknown $param
     */
    public function appcet() {
        $rs=M('refund')->where(['r_no' => $this->_param['r_no'],'uid' => $this->uid])->field()->find();
        if(!$rs) return $this->apiReturn(3);
        
        //退款订单已失效！
        if($rs['status']!=4) return $this->apiReturn(1005);
        $do=M();
        $do->startTrans();
        
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 100,'dotime' => date('Y-m-d H:i:s'),'express_time' => date('Y-m-d H:i:s')])) goto error;
        //$reason = '<p class="strong text_red">买家确认收到售后商品</p>售后已完成';
        $reason = '买家已收到商品且无异议，售后完成！';
        //日志数据
        $logs=[
            'r_id'                  =>$rs['id'],
            'r_no'                  =>$rs['r_no'],
            'uid'                   =>$this->uid,
            'status'                =>100,
            'type'                  =>$rs['type'],
            'remark'                =>$reason, //买家取消退款！
            'is_sys'                =>isset($this->_param['is_sys']) ? 1 : 0,
        ];
        
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
        return $this->apiReturn(1, ['data' => ['r_no' => $this->_param['r_no']]]);
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
}