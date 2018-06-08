<?php
namespace Common\Controller;
class SellerRefund3Controller extends SellerOrdersController {
    private $action_logs    =   array('accept','reject','accept2');   //须要记录日志的方法
    private $_param         =   [];
    private $_res           =   [];
    
    function __construct($param = null) {
        $this->s_no     =   $param['s_no'];
        $this->seller_id=   $param['seller_id'];
        //parent::__construct($param);
        if (!is_null($param)) {
            $this->_param   =   array_merge($param, I('post.'));
        } else {
            $this->_param   =   I('post.');
        }
        $this->_res         =   $this->check_s_orders(1);
        if ($this->_res['code'] != 1) return $this->_res;
        if ($this->_res['data']['status'] < 4) return ['code' => 0, 'msg' => '已收货订单才可申请售后！'];
    }
    
    /**
     * 同意 需要获取卖家地址
     * @param unknown $param
     */
    public function accept() {
        $rs     =   $this->getData(1);
        if (false == $rs) return $this->apiReturn(1005);
        //取卖家常用退货地址
        $area   =   $this->cache_table('area');
        $map    =   [
            'uid' => $this->seller_id,
            'id'  => $this->_param['address_id'],
        ];
        $address=   M('send_address')->where($map)->order('is_default desc')->find();
        $address_str= $address['linkname'].'，'.$address['mobile'].($address['tel']?'，'.$address['tel']:'') .'，'.$area[$address['province']].' '.$area[$address['city']].' '.$area[$address['district']].' '.$area[$address['town']].' '.$address['street'].($address['postcode']?'('.$address['postcode'].')':'');
    
        $do=M();
        $do->startTrans();
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 3,'dotime' => date('Y-m-d H:i:s'),'accept_time' => date('Y-m-d H:i:s')])) {
            goto error;
        }
        //$reason = '<p class="strong text_red">卖家同意售后</p>售后地址：'.$address_str;
        $reason = '卖家同意售后申请';

        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$this->seller_id,
            'status'        =>3,
            'type'          =>$rs['type'],
            'address'       =>$address_str,
            'remark'        =>$reason, //卖家同意退货
        ];
        
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'service_agree','r_no'=>$rs['r_no']];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $rs['r_no']]]);
        
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    /**
     * 拒绝
     * @param unknown $param
     */
    public function reject($param) {
        $rs     =   $this->getData(1);
        if (false == $rs) return $this->apiReturn(1005);
        $do=M();
        $do->startTrans();
        
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 2,'dotime' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['refund_express']),'is_problem' => 0])) goto error;
        
        //$reason = '<p class="strong text_red">卖家拒绝售后</p>' . $this->_param['reason'];
        $reason = $param['reason'] ? $param['reason'] : '卖家拒绝售后申请';
        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$this->seller_id,
            'status'        =>2,
            'type'          =>$rs['type'],
            'remark'        =>$reason, //卖家拒绝退货
            'images'        =>$this->_param['images'] ? $this->_param['images'] : '', //拒绝图片
        ];
        
        if(!$this->sw[]=D('Common/RefundLogs')->create($logs)){
            $msg=D('Common/RefundLogs')->getError();
            goto error;
        }
        
        if(!$this->sw[]=D('Common/RefundLogs')->add()) goto error;
        
        $do->commit();
		
		//发送退款消息
		$msg_data = ['tpl_tag'=>'service_refuse','r_no'=>$rs['r_no']];
		tag('send_msg',$msg_data);
		
        return $this->apiReturn(1, ['data' => ['r_no' => $rs['r_no']]]);
        
        error:
        $do->rollback();
        return $this->apiReturn(0,'',C('error_code.0').$msg);
    }
    
    /**
     * 已收到货
     * @param unknown $param
     */
    public function accept1() {
        
        $rs     =   $this->getData(4);
        if (false == $rs) return $this->apiReturn(1005);
        $buyer  =   M('user')->where(['id' => $this->_res['data']['uid']])->field('nick,erp_uid')->find();
        $seller =   M('user')->where(['id' => $this->_res['data']['seller_id']])->field('nick,erp_uid')->find();
        //$reason =   '<p class="strong text_red">卖家已收到售后商品</p>卖家已收到售后商品';//卖家同意售后
        $reason = '卖家已收到需要售后的商品';
        $do=M();
        $do->startTrans();
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 5,'dotime' => date('Y-m-d H:i:s'),'accept_time' => date('Y-m-d H:i:s')])) goto error;
    
        //日志数据
        $logs=[
            'r_id'          =>$rs['id'],
            'r_no'          =>$rs['r_no'],
            'uid'           =>$this->seller_id,
            'status'        =>5,
            'type'          =>$rs['type'],
            'remark'        =>$reason, //卖家同意退款
            'is_sys'        =>isset($this->_param['is_sys']) ? 1 : 0,
        ];
        
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
     * 卖家寄出商品
     * (non-PHPdoc)
     * @see \Common\Controller\SellerOrdersController::send_express()
     */
    public function send_express() {
        $rs     =   $this->getData(5);
        if (false == $rs) return $this->apiReturn(1005);
        $ers    =   M('express_company')->where(['id' => $this->_param['express_company_id']])->field('sub_name')->find();
        $str    =   $ers['sub_name'].'：'.$this->_param['express_code'];
        
        $do     =   M();
        $do->startTrans();
        
        if(!$this->sw[]=M('refund')->where(['id' => $rs['id']])->save(['status' => 6,'dotime' => date('Y-m-d H:i:s'),'express_time' => date('Y-m-d H:i:s'),'next_time' => date('Y-m-d H:i:s',time() + C('cfg.orders')['confirm_orders']),'is_problem' => 0])) goto error;
        
        
        //$reason = '<p class="text_red strong">卖家寄出商品</p>' . $str.$this->_param['reason'];
        $reason = '卖家已完成售后服务并寄回商品给买家';
        //日志数据
        $logs=[
            'r_id'                  =>$rs['id'],
            'r_no'                  =>$rs['r_no'],
            'uid'                   =>$this->seller_id,
            'status'                =>6,
            'type'                  =>$rs['type'],
            'express_company_id'    =>$this->_param['express_company_id'],
            'express_code'          =>$this->_param['express_code'],
            'remark'                =>$reason, //卖家寄出商品
        ];
        
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
     * 获取订单数据
     */
    private function getData($status) {
        $rs=M('refund')
        ->where([
            'r_no'          => $this->_param['r_no'],
            's_id'          => $this->_res['data']['id'],
            'orders_status' => ['in', '4,5'],
            //'orders_status' => $this->_res['data']['status']
        ])
        ->field('id,r_no,status,type,money,score,orders_goods_id,num,s_no,activity_money,refund_express')->find();
        //if(!$rs) return $this->apiReturn(3);    //找不到记录
        if(!$rs) return false;
        //售后订单已失效！
        if($rs['status']!=$status) return false;
        
        return $rs;
    }
}